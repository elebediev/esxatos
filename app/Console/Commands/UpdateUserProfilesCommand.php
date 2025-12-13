<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateUserProfilesCommand extends Command
{
    protected $signature = 'users:update-profiles {--dry-run : Show what would be updated without making changes}';
    protected $description = 'Update existing users with profile data (first_name, country, city) from Drupal';

    public function handle(): int
    {
        $this->info('Loading profile data from Drupal...');

        $profileData = $this->loadProfileData();
        $this->info("Loaded profile data for " . count($profileData) . " users");

        // Get all users with drupal_uid
        $users = User::whereNotNull('drupal_uid')->get();
        $this->info("Found {$users->count()} users to check");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;
        $skipped = 0;
        $dryRun = $this->option('dry-run');

        foreach ($users as $user) {
            $profile = $profileData[$user->drupal_uid] ?? null;

            if (!$profile) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Check if any field needs updating
            $needsUpdate = false;
            $updates = [];

            if (empty($user->first_name) && !empty($profile['real_name'])) {
                $updates['first_name'] = $profile['real_name'];
                $needsUpdate = true;
            }

            if (empty($user->country) && !empty($profile['country'])) {
                $updates['country'] = $profile['country'];
                $needsUpdate = true;
            }

            if (empty($user->city) && !empty($profile['city'])) {
                $updates['city'] = $profile['city'];
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                if ($dryRun) {
                    $this->newLine();
                    $this->line("Would update user {$user->name} (ID: {$user->id}):");
                    foreach ($updates as $field => $value) {
                        $this->line("  {$field}: {$value}");
                    }
                } else {
                    $user->update($updates);
                }
                $updated++;
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("DRY RUN - No changes made");
        }
        $this->info("Updated: {$updated}");
        $this->info("Skipped (no profile or already has data): {$skipped}");

        return Command::SUCCESS;
    }

    private function loadProfileData(): array
    {
        $profiles = DB::connection('drupal')
            ->table('profile as p')
            ->leftJoin('field_data_field_username as fn', function ($join) {
                $join->on('fn.entity_id', '=', 'p.pid')
                    ->where('fn.entity_type', '=', 'profile2');
            })
            ->leftJoin('field_data_field_usercountry as fc', function ($join) {
                $join->on('fc.entity_id', '=', 'p.pid')
                    ->where('fc.entity_type', '=', 'profile2');
            })
            ->leftJoin('field_data_field_usrtown as ft', function ($join) {
                $join->on('ft.entity_id', '=', 'p.pid')
                    ->where('ft.entity_type', '=', 'profile2');
            })
            ->select([
                'p.uid',
                'fn.field_username_value as real_name',
                'fc.field_usercountry_value as country',
                'ft.field_usrtown_value as city',
            ])
            ->get();

        $data = [];
        foreach ($profiles as $profile) {
            $data[$profile->uid] = [
                'real_name' => $profile->real_name,
                'country' => $profile->country,
                'city' => $profile->city,
            ];
        }

        return $data;
    }
}
