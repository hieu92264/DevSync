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

        $this->assignOrganizationRole($devSync, $users['demo.admin'], $devSyncRoles['OWNER']);
        $this->assignOrganizationRole($devSync, $users['demo.minh.manager'], $devSyncRoles['ADMIN']);
        $this->assignOrganizationRole($acme, $users['demo.admin'], $acmeRoles['OWNER']);

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

        $this->syncProjectMember($portal, $users['demo.minh.manager'], 'BA', [$devSyncRoles['PROJECT_MANAGER']]);
        $this->syncProjectMember($portal, $users['demo.lan.backend'], 'BACKEND', [$devSyncRoles['DEVELOPER']]);
        $this->syncProjectMember($portal, $users['demo.an.frontend'], 'FRONTEND', [$devSyncRoles['DEVELOPER']]);
        $this->syncProjectMember($portal, $users['demo.hoa.qa'], 'QA', [$devSyncRoles['TESTER']]);
        $this->syncProjectMember($mobile, $users['demo.minh.manager'], 'BA', [$devSyncRoles['PROJECT_MANAGER']]);
        $this->syncProjectMember($mobile, $users['demo.an.frontend'], 'FRONTEND', [$devSyncRoles['DEVELOPER']]);
        $this->syncProjectMember($acmePlatform, $users['demo.admin'], 'BA', [$acmeRoles['PROJECT_MANAGER']]);
        $this->syncProjectMember($acmePlatform, $users['demo.lan.backend'], 'BACKEND', [$acmeRoles['DEVELOPER']]);
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
            'organization.view', 'organization.update', 'organization.member.view', 'organization.member.invite', 'organization.member.update', 'organization.member.remove',
            'project.create', 'project.view', 'project.update', 'project.member.view', 'project.member.manage',
            'task.view_all', 'task.create', 'task.update_all', 'task.update_assigned', 'task.assign', 'task.delete',
            'api.view', 'api.create', 'api.update', 'api.execute', 'log.view', 'incident.view', 'incident.update', 'ai.analysis.view', 'ai.analysis.create',
        ])->mapWithKeys(function (string $code): array {
            [$resource, $action] = array_pad(explode('.', $code, 2), 2, null);
            $attributes = ['code' => $code, 'name' => str_replace('.', ' ', $code), 'resource' => $resource, 'action' => $action, 'is_active' => true];
            $permission = Permission::updateOrCreate(['code' => $attributes['code']], $attributes);

            return [$permission->code => $permission];
        });
    }

    private function seedRoles(Organization $organization, Collection $permissions): Collection
    {
        $all = $permissions->keys()->all();
        $definitions = [
            'OWNER' => [
                'scope' => Role::SCOPE_ORGANIZATION,
                'name' => 'Owner',
                'priority' => 1000,
                'permissions' => $all,
            ],
            'ADMIN' => [
                'scope' => Role::SCOPE_ORGANIZATION,
                'name' => 'Administrator',
                'priority' => 900,
                'permissions' => ['organization.view', 'organization.update', 'organization.member.view', 'organization.member.invite', 'organization.member.update', 'organization.member.remove', 'project.create', 'project.view', 'project.update', 'project.member.view', 'project.member.manage'],
            ],
            'PROJECT_MANAGER' => [
                'scope' => Role::SCOPE_PROJECT,
                'name' => 'Project Manager',
                'priority' => 100,
                'permissions' => ['project.view', 'project.update', 'project.member.view', 'project.member.manage', 'task.view_all', 'task.create', 'task.update_all', 'task.assign', 'task.delete', 'api.view', 'api.create', 'api.update', 'api.execute', 'log.view', 'incident.view', 'incident.update', 'ai.analysis.view', 'ai.analysis.create'],
            ],
            'TECH_LEAD' => [
                'scope' => Role::SCOPE_PROJECT, 'name' => 'Tech Lead', 'priority' => 80,
                'permissions' => ['project.view', 'project.member.view', 'task.view_all', 'task.create', 'task.update_all', 'task.assign', 'api.view', 'api.create', 'api.update', 'api.execute', 'log.view', 'incident.view', 'incident.update', 'ai.analysis.view', 'ai.analysis.create'],
            ],
            'DEVELOPER' => [
                'scope' => Role::SCOPE_PROJECT,
                'name' => 'Developer',
                'priority' => 50,
                'permissions' => ['project.view', 'task.view_all', 'task.create', 'task.update_assigned', 'api.view', 'api.execute', 'log.view', 'incident.view', 'ai.analysis.view', 'ai.analysis.create'],
            ],
            'TESTER' => [
                'scope' => Role::SCOPE_PROJECT,
                'name' => 'Tester',
                'priority' => 40,
                'permissions' => ['project.view', 'task.view_all', 'task.update_assigned', 'api.view', 'api.execute', 'incident.view'],
            ],
            'VIEWER' => [
                'scope' => Role::SCOPE_PROJECT, 'name' => 'Viewer', 'priority' => 10,
                'permissions' => ['project.view', 'task.view_all', 'api.view', 'incident.view'],
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
