<?php

namespace Tests\Feature;

use App\Modules\Authorization\Models\Permission;
use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Authorization\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationMember;
use App\Modules\Project\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_wrong_password_and_inactive_user(): void
    {
        $user = User::factory()->create(['email' => 'inactive@example.test', 'is_active' => false]);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'wrong'])->assertUnauthorized();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])->assertUnauthorized();
    }

    public function test_me_requires_authentication_and_returns_authenticated_user(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
        $user = User::factory()->create();
        $this->actingAs($user, 'api')->getJson('/api/auth/me')->assertOk()
            ->assertJsonPath('metadata.id', $user->id);
    }

    public function test_organization_and_project_context_are_isolated(): void
    {
        [$user, $organization, $project] = $this->context();
        $other = Organization::factory()->create(['code' => 'other-org']);
        $otherProject = Project::factory()->create(['organization_id' => $other->id, 'lead_id' => $user->id, 'code' => 'shared-code']);
        $project->update(['code' => 'shared-code']);

        $headers = ['X-Organization-Code' => $organization->code, 'X-Project-Code' => $otherProject->code];
        $this->actingAs($user, 'api')->getJson('/api/me/context', $headers)->assertOk()
            ->assertJsonPath('metadata.project.id', $project->id);

        $this->actingAs($user, 'api')->getJson('/api/me/projects', ['X-Organization-Code' => $other->code])->assertForbidden();
        $this->actingAs($user, 'api')->getJson('/api/me/context', ['X-Organization-Code' => $organization->code, 'X-Project-Code' => 'fake'])->assertNotFound();
    }

    public function test_inactive_memberships_are_not_listed(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        OrganizationMember::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'is_active' => false]);

        $this->actingAs($user, 'api')->getJson('/api/me/organizations')->assertOk()->assertJsonCount(0, 'metadata');
    }

    public function test_project_permission_is_required_and_permissions_are_unique(): void
    {
        [$user, $organization, $project, $membership] = $this->context();
        $permission = Permission::factory()->create(['code' => 'project.member.view', 'name' => 'Project member view']);
        foreach (['ONE', 'TWO'] as $code) {
            $role = Role::factory()->create(['organization_id' => $organization->id, 'scope' => Role::SCOPE_PROJECT, 'code' => $code]);
            $role->permissions()->attach($permission->id, ['is_active' => true]);
            $membership->roles()->attach($role->id, ['is_active' => true]);
        }
        $headers = ['X-Organization-Code' => $organization->code, 'X-Project-Code' => $project->code];
        $this->actingAs($user, 'api')->getJson('/api/project/members', $headers)->assertOk();
        $this->actingAs($user, 'api')->getJson('/api/me/context', $headers)->assertJsonPath('metadata.permissions.0', 'project.member.view')
            ->assertJsonCount(1, 'metadata.permissions');

        $membership->roles()->detach();
        $this->actingAs($user, 'api')->getJson('/api/project/members', $headers)->assertForbidden();
    }

    public function test_role_assignments_reject_wrong_scope_or_organization(): void
    {
        [$user, $organization, $project, $projectMember, $organizationMember] = $this->context();
        $this->grant($projectMember, $organization, 'project.member.manage', Role::SCOPE_PROJECT);
        $this->grant($organizationMember, $organization, 'organization.member.update', Role::SCOPE_ORGANIZATION);
        $organizationRole = Role::factory()->create(['organization_id' => $organization->id, 'scope' => Role::SCOPE_ORGANIZATION]);
        $projectRole = Role::factory()->create(['organization_id' => $organization->id, 'scope' => Role::SCOPE_PROJECT]);
        $otherRole = Role::factory()->create(['scope' => Role::SCOPE_PROJECT]);
        $projectHeaders = ['X-Organization-Code' => $organization->code, 'X-Project-Code' => $project->code];

        $this->actingAs($user, 'api')->putJson("/api/project/members/{$projectMember->id}/roles", ['role_ids' => [$organizationRole->id]], $projectHeaders)->assertNotFound();
        $this->actingAs($user, 'api')->putJson("/api/project/members/{$projectMember->id}/roles", ['role_ids' => [$otherRole->id]], $projectHeaders)->assertNotFound();
        $this->actingAs($user, 'api')->putJson("/api/organization/members/{$organizationMember->id}/roles", ['role_ids' => [$projectRole->id]], ['X-Organization-Code' => $organization->code])->assertNotFound();
    }

    private function context(): array
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['code' => 'org-'.fake()->unique()->slug(2)]);
        $organizationMember = OrganizationMember::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id]);
        $project = Project::factory()->create(['organization_id' => $organization->id, 'lead_id' => $user->id, 'code' => 'project-'.fake()->unique()->slug(2)]);
        $projectMember = ProjectMember::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);

        return [$user, $organization, $project, $projectMember, $organizationMember];
    }

    private function grant($membership, Organization $organization, string $permissionCode, string $scope): void
    {
        $permission = Permission::factory()->create(['code' => $permissionCode, 'name' => $permissionCode]);
        $role = Role::factory()->create(['organization_id' => $organization->id, 'scope' => $scope]);
        $role->permissions()->attach($permission->id, ['is_active' => true]);
        $membership->roles()->attach($role->id, ['is_active' => true]);
    }
}
