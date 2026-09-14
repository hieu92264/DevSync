<?php

namespace Database\Seeders;

use App\Modules\Authorization\Models\Permission;
use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Authorization\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Equipment;
use App\Modules\Organization\Models\Organization;
use App\Modules\Project\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DevSyncDemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect([
            ['user_name' => 'demo.admin', 'email' => 'demo.admin@devsync.test'],
            ['user_name' => 'demo.minh.manager', 'email' => 'demo.minh.manager@devsync.test'],
            ['user_name' => 'demo.lan.backend', 'email' => 'demo.lan.backend@devsync.test'],
            ['user_name' => 'demo.an.frontend', 'email' => 'demo.an.frontend@devsync.test'],
            ['user_name' => 'demo.hoa.qa', 'email' => 'demo.hoa.qa@devsync.test'],
        ])->mapWithKeys(function (array $attributes): array {
            $user = User::updateOrCreate(
                ['email' => $attributes['email']],
                [...$attributes, 'password_hash' => Hash::make('password'), 'email_verified_at' => now(), 'is_active' => true],
            );

            return [$attributes['user_name'] => $user];
        });

        $devSync = Organization::updateOrCreate(
            ['code' => 'devsync-labs'],
            ['name' => 'DevSync Labs'],
        );
        $acme = Organization::updateOrCreate(
            ['code' => 'acme-digital'],
            ['name' => 'Acme Digital'],
        );

        $this->syncOrganizationMembers($devSync, [
            $users['demo.admin'],
            $users['demo.minh.manager'],
            $users['demo.lan.backend'],
            $users['demo.an.frontend'],
            $users['demo.hoa.qa'],
        ]);
        $this->syncOrganizationMembers($acme, [$users['demo.admin'], $users['demo.lan.backend']]);

        Department::updateOrCreate(
            ['code' => 'ENG-DEVSYNC'],
            ['name' => 'Engineering', 'organization_id' => $devSync->id, 'manager_id' => $users['demo.minh.manager']->id],
        );
        Department::updateOrCreate(
            ['code' => 'QA-DEVSYNC'],
            ['name' => 'Quality Assurance', 'organization_id' => $devSync->id, 'manager_id' => $users['demo.hoa.qa']->id],
        );

        Equipment::updateOrCreate(
            ['code' => 'EQ-DEVSYNC-001'],
            [
                'name' => 'MacBook Pro 14',
                'type' => 'laptop',
                'serial_number' => 'C02DEVSYNC01',
                'status' => 'assigned',
                'specification' => 'Apple M3 Pro / 18GB RAM / 512GB SSD',
                'purchase_at' => now()->subMonths(8),
                'organization_id' => $devSync->id,
            ],
        );

        $permissions = $this->seedPermissions();
        $devSyncRoles = $this->seedRoles($devSync, $permissions);
        $acmeRoles = $this->seedRoles($acme, $permissions);

        $this->assignOrganizationRole($devSync, $users['demo.admin'], $devSyncRoles['owner']);
        $this->assignOrganizationRole($devSync, $users['demo.minh.manager'], $devSyncRoles['member']);
        $this->assignOrganizationRole($acme, $users['demo.admin'], $acmeRoles['owner']);

        $portal = Project::updateOrCreate(
            ['code' => 'devsync-portal'],
            [
                'name' => 'DevSync Portal',
                'description' => 'Web portal for project collaboration and incident tracking.',
                'status' => 'active',
                'repository_url' => 'https://github.com/devsync/devsync-portal',
                'organization_id' => $devSync->id,
                'lead_id' => $users['demo.minh.manager']->id,
            ],
        );
        $mobile = Project::updateOrCreate(
            ['code' => 'devsync-mobile'],
            [
                'name' => 'DevSync Mobile',
                'description' => 'Mobile companion app for engineers and quality teams.',
                'status' => 'planning',
                'repository_url' => 'https://github.com/devsync/devsync-mobile',
                'organization_id' => $devSync->id,
                'lead_id' => $users['demo.minh.manager']->id,
            ],
        );
        $acmePlatform = Project::updateOrCreate(
            ['code' => 'acme-platform'],
            [
                'name' => 'Acme Platform',
                'description' => 'Customer platform modernization project.',
                'status' => 'active',
                'repository_url' => 'https://github.com/acme/acme-platform',
                'organization_id' => $acme->id,
                'lead_id' => $users['demo.admin']->id,
            ],
        );

        $this->syncProjectMember($portal, $users['demo.minh.manager'], 'BA', [$devSyncRoles['project-manager']]);
        $this->syncProjectMember($portal, $users['demo.lan.backend'], 'BACKEND', [$devSyncRoles['developer']]);
        $this->syncProjectMember($portal, $users['demo.an.frontend'], 'FRONTEND', [$devSyncRoles['developer']]);
        $this->syncProjectMember($portal, $users['demo.hoa.qa'], 'QA', [$devSyncRoles['quality-engineer']]);
        $this->syncProjectMember($mobile, $users['demo.minh.manager'], 'BA', [$devSyncRoles['project-manager']]);
        $this->syncProjectMember($mobile, $users['demo.an.frontend'], 'FRONTEND', [$devSyncRoles['developer']]);
        $this->syncProjectMember($acmePlatform, $users['demo.admin'], 'BA', [$acmeRoles['project-manager']]);
        $this->syncProjectMember($acmePlatform, $users['demo.lan.backend'], 'BACKEND', [$acmeRoles['developer']]);
    }

    private function syncOrganizationMembers(Organization $organization, array $users): void
    {
        $organization->members()->syncWithoutDetaching(
            collect($users)->mapWithKeys(fn (User $user) => [
                $user->id => ['joined_at' => now()->subMonths(6), 'left_at' => null, 'is_active' => true],
            ])->all(),
        );
    }

    private function seedPermissions(): Collection
    {
        return collect([
            ['code' => 'project.read', 'name' => 'View projects', 'resource' => 'project', 'action' => 'read'],
            ['code' => 'project.manage', 'name' => 'Manage projects', 'resource' => 'project', 'action' => 'manage'],
            ['code' => 'task.read', 'name' => 'View tasks', 'resource' => 'task', 'action' => 'read'],
            ['code' => 'task.create', 'name' => 'Create tasks', 'resource' => 'task', 'action' => 'create'],
            ['code' => 'task.update', 'name' => 'Update tasks', 'resource' => 'task', 'action' => 'update'],
            ['code' => 'task.manage', 'name' => 'Manage tasks', 'resource' => 'task', 'action' => 'manage'],
            ['code' => 'incident.read', 'name' => 'View incidents', 'resource' => 'incident', 'action' => 'read'],
            ['code' => 'incident.create', 'name' => 'Create incidents', 'resource' => 'incident', 'action' => 'create'],
            ['code' => 'incident.manage', 'name' => 'Manage incidents', 'resource' => 'incident', 'action' => 'manage'],
        ])->mapWithKeys(function (array $attributes): array {
            $permission = Permission::updateOrCreate(['code' => $attributes['code']], $attributes);

            return [$permission->code => $permission];
        });
    }

    private function seedRoles(Organization $organization, Collection $permissions): Collection
    {
        $definitions = [
            'owner' => [
                'scope' => Role::SCOPE_ORGANIZATION,
                'name' => 'Owner',
                'priority' => 1000,
                'permissions' => ['project.read', 'project.manage', 'task.read', 'task.create', 'task.update', 'task.manage', 'incident.read', 'incident.create', 'incident.manage'],
            ],
            'member' => [
                'scope' => Role::SCOPE_ORGANIZATION,
                'name' => 'Member',
                'priority' => 10,
                'permissions' => ['project.read', 'task.read', 'incident.read'],
            ],
            'project-manager' => [
                'scope' => Role::SCOPE_PROJECT,
                'name' => 'Project Manager',
                'priority' => 100,
                'permissions' => ['project.read', 'project.manage', 'task.read', 'task.create', 'task.update', 'task.manage', 'incident.read', 'incident.create', 'incident.manage'],
            ],
            'developer' => [
                'scope' => Role::SCOPE_PROJECT,
                'name' => 'Developer',
                'priority' => 50,
                'permissions' => ['project.read', 'task.read', 'task.create', 'task.update', 'incident.read', 'incident.create'],
            ],
            'quality-engineer' => [
                'scope' => Role::SCOPE_PROJECT,
                'name' => 'Quality Engineer',
                'priority' => 40,
                'permissions' => ['project.read', 'task.read', 'task.update', 'incident.read', 'incident.create', 'incident.manage'],
            ],
        ];

        return collect($definitions)->mapWithKeys(function (array $definition, string $code) use ($organization, $permissions): array {
            $role = Role::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'scope' => $definition['scope'],
                    'code' => $code,
                ],
                ['name' => $definition['name'], 'priority' => $definition['priority'], 'is_active' => true],
            );
            $role->permissions()->sync(
                collect($definition['permissions'])->mapWithKeys(fn (string $permissionCode) => [
                    $permissions[$permissionCode]->id => ['is_active' => true],
                ])->all(),
            );

            return [$code => $role];
        });
    }

    private function assignOrganizationRole(Organization $organization, User $user, Role $role): void
    {
        $membership = $organization->organizationMembers()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $membership->roles()->syncWithoutDetaching([$role->id => ['is_active' => true]]);
    }

    private function syncProjectMember(Project $project, User $user, string $teamType, array $roles): void
    {
        $project->members()->syncWithoutDetaching([
            $user->id => ['team_type' => $teamType, 'joined_at' => now()->subMonths(4), 'left_at' => null, 'is_active' => true],
        ]);

        $membership = ProjectMember::query()
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $membership->roles()->sync(
            collect($roles)->mapWithKeys(fn (Role $role) => [$role->id => ['is_active' => true]])->all(),
        );
    }
}
