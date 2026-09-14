<?php

namespace Tests\Feature;

use App\Modules\Authorization\Models\Permission;
use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Authorization\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Equipment;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationMember;
use App\Modules\Project\Models\Project;
use Database\Seeders\DevSyncDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_factories_create_valid_related_records(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();

        Department::factory()->create(['organization_id' => $organization->id, 'manager_id' => $user->id]);
        Equipment::factory()->create(['organization_id' => $organization->id]);
        OrganizationMember::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id]);

        $project = Project::factory()->create(['organization_id' => $organization->id, 'lead_id' => $user->id]);
        $membership = ProjectMember::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();

        $role->permissions()->attach($permission->id);
        $membership->roles()->attach($role->id);

        $this->assertDatabaseCount('departments', 1);
        $this->assertDatabaseCount('equipment', 1);
        $this->assertDatabaseCount('organization_members', 1);
        $this->assertDatabaseCount('project_members', 1);
        $this->assertDatabaseCount('role_permissions', 1);
        $this->assertDatabaseCount('project_member_roles', 1);
    }

    public function test_demo_seeder_is_idempotent_and_builds_frontend_fixture_data(): void
    {
        $this->seed(DevSyncDemoSeeder::class);
        $this->seed(DevSyncDemoSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('organizations', 2);
        $this->assertDatabaseCount('projects', 3);
        $this->assertDatabaseCount('project_members', 8);
        $this->assertDatabaseCount('project_member_roles', 8);
    }
}
