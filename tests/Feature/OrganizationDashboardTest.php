<?php

namespace Tests\Feature;

use App\Modules\Authorization\Models\Permission;
use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Authorization\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Project\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_log_in_with_a_username(): void
    {
        $user = User::factory()->create(['user_name' => 'devsync-user']);

        $this->postJson('/api/auth/login', [
            'login' => $user->user_name,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('metadata.token_type', 'bearer')
            ->assertJsonStructure(['metadata' => ['access_token', 'expires_in']]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_an_active_member_can_list_organizations_and_view_only_its_project_permissions(): void
    {
        $user = User::factory()->create();
        $organization = Organization::create(['code' => 'acme', 'name' => 'Acme']);
        $otherOrganization = Organization::create(['code' => 'other', 'name' => 'Other']);

        $organization->members()->attach($user->id, ['joined_at' => now()]);

        $project = Project::create([
            'code' => 'devsync',
            'name' => 'DevSync',
            'status' => 'active',
            'organization_id' => $organization->id,
            'lead_id' => $user->id,
        ]);
        $project->members()->attach($user->id, ['team_type' => 'developer', 'joined_at' => now()]);

        $permission = Permission::create([
            'code' => 'task.read',
            'name' => 'Read tasks',
            'resource' => 'task',
            'action' => 'read',
        ]);
        $role = Role::create([
            'organization_id' => $organization->id,
            'scope' => Role::SCOPE_PROJECT,
            'code' => 'developer',
            'name' => 'Developer',
        ]);
        $role->permissions()->attach($permission->id);

        $membership = ProjectMember::query()
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        $membership->roles()->attach($role->id);

        $this->actingAs($user, 'api')
            ->getJson('/api/auth/organizations')
            ->assertOk()
            ->assertJsonPath('metadata.0.id', $organization->id)
            ->assertJsonCount(1, 'metadata');

        $this->actingAs($user, 'api')
            ->getJson("/api/organizations/{$organization->id}/dashboard")
            ->assertOk()
            ->assertJsonPath('metadata.organization.id', $organization->id)
            ->assertJsonPath('metadata.projects.0.project.id', $project->id)
            ->assertJsonPath('metadata.projects.0.roles.0.permissions.0.code', 'task.read');

        $this->actingAs($user, 'api')
            ->getJson("/api/organizations/{$otherOrganization->id}/dashboard")
            ->assertForbidden();
    }
}
