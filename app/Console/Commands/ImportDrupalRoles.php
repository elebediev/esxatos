<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ImportDrupalRoles extends Command
{
    protected $signature = 'import:drupal-roles {--dry-run : Show what would be imported without making changes}';
    protected $description = 'Import user roles from Drupal database';

    // Mapping Drupal role IDs to Laravel role names
    private array $roleMapping = [
        3 => 'admin',
        5 => 'club',
        6 => 'aide',
    ];

    // Permissions for each role
    private array $rolePermissions = [
        'admin' => [
            'manage users',
            'manage books',
            'manage comments',
            'manage content',
            'manage settings',
            'view admin panel',
            'upload books',
            'edit any book',
            'delete any book',
            'approve books',
            'moderate comments',
        ],
        'club' => [
            'upload books',
            'edit own book',
            'delete own book',
            'view premium content',
            'download books',
        ],
        'aide' => [
            'upload books',
            'edit own book',
            'delete own book',
            'view premium content',
            'download books',
            'moderate comments',
            'approve books',
        ],
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('Starting Drupal roles import...');
        $this->newLine();

        // Step 1: Create permissions
        $this->createPermissions($dryRun);

        // Step 2: Create roles
        $this->createRoles($dryRun);

        // Step 3: Assign roles to users
        $this->assignRolesToUsers($dryRun);

        $this->newLine();
        $this->info('✅ Drupal roles import completed!');

        return Command::SUCCESS;
    }

    private function createPermissions(bool $dryRun): void
    {
        $this->info('Creating permissions...');

        $allPermissions = collect($this->rolePermissions)->flatten()->unique();

        foreach ($allPermissions as $permission) {
            if ($dryRun) {
                $this->line("  Would create permission: {$permission}");
            } else {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
                $this->line("  ✓ Permission: {$permission}");
            }
        }

        $this->info("  Total: {$allPermissions->count()} permissions");
    }

    private function createRoles(bool $dryRun): void
    {
        $this->newLine();
        $this->info('Creating roles with permissions...');

        foreach ($this->rolePermissions as $roleName => $permissions) {
            if ($dryRun) {
                $this->line("  Would create role: {$roleName} with " . count($permissions) . " permissions");
            } else {
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $role->syncPermissions($permissions);
                $this->line("  ✓ Role: {$roleName} with " . count($permissions) . " permissions");
            }
        }
    }

    private function assignRolesToUsers(bool $dryRun): void
    {
        $this->newLine();
        $this->info('Assigning roles to users...');

        // Get user roles from Drupal
        $drupalUserRoles = DB::connection('drupal')
            ->table('users_roles')
            ->select('uid', 'rid')
            ->whereIn('rid', array_keys($this->roleMapping))
            ->get();

        $stats = [
            'admin' => 0,
            'club' => 0,
            'aide' => 0,
            'not_found' => 0,
        ];

        $bar = $this->output->createProgressBar($drupalUserRoles->count());
        $bar->start();

        foreach ($drupalUserRoles as $userRole) {
            $laravelRole = $this->roleMapping[$userRole->rid] ?? null;

            if (!$laravelRole) {
                continue;
            }

            $user = User::where('drupal_uid', $userRole->uid)->first();

            if (!$user) {
                $stats['not_found']++;
                $bar->advance();
                continue;
            }

            if (!$dryRun) {
                if (!$user->hasRole($laravelRole)) {
                    $user->assignRole($laravelRole);
                }
            }

            $stats[$laravelRole]++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Role assignment summary:');
        $this->table(
            ['Role', 'Users Assigned'],
            [
                ['admin', $stats['admin']],
                ['club', $stats['club']],
                ['aide', $stats['aide']],
                ['Not found in Laravel', $stats['not_found']],
            ]
        );
    }
}
