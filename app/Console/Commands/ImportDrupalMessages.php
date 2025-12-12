<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDrupalMessages extends Command
{
    protected $signature = 'import:drupal-messages {--fresh : Clear existing messages before import}';
    protected $description = 'Import private messages from Drupal database';

    private array $userMap = [];

    public function handle(): int
    {
        $this->info('Starting Drupal messages import...');

        // Set MySQL session timezone to UTC to avoid DST issues
        DB::statement("SET time_zone = '+00:00'");

        if ($this->option('fresh')) {
            $this->warn('Clearing existing messages...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Message::truncate();
            DB::table('message_thread_participants')->truncate();
            MessageThread::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Build user mapping (drupal_uid -> laravel_id)
        $this->buildUserMap();

        // Get unique threads from Drupal
        $threads = DB::connection('drupal')
            ->table('pm_index')
            ->select('thread_id')
            ->distinct()
            ->orderBy('thread_id')
            ->get();

        $this->info("Found {$threads->count()} threads to import.");

        $bar = $this->output->createProgressBar($threads->count());
        $bar->start();

        $importedThreads = 0;
        $skippedThreads = 0;

        foreach ($threads as $thread) {
            $result = $this->importThread($thread->thread_id);
            if ($result) {
                $importedThreads++;
            } else {
                $skippedThreads++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Import completed!");
        $this->info("Imported: {$importedThreads} threads");
        $this->info("Skipped: {$skippedThreads} threads (users not found)");

        return Command::SUCCESS;
    }

    private function buildUserMap(): void
    {
        $this->info('Building user mapping...');

        $users = User::whereNotNull('drupal_uid')->get(['id', 'drupal_uid']);
        foreach ($users as $user) {
            $this->userMap[$user->drupal_uid] = $user->id;
        }

        $this->info("Mapped {$users->count()} users.");
    }

    private function importThread(int $drupalThreadId): bool
    {
        // Check if already imported
        $existingThread = MessageThread::where('drupal_thread_id', $drupalThreadId)->first();
        if ($existingThread) {
            return true; // Already imported
        }

        // Get all messages in this thread
        $messages = DB::connection('drupal')
            ->table('pm_message')
            ->join('pm_index', 'pm_message.mid', '=', 'pm_index.mid')
            ->where('pm_index.thread_id', $drupalThreadId)
            ->select('pm_message.*')
            ->distinct()
            ->orderBy('pm_message.timestamp')
            ->get();

        if ($messages->isEmpty()) {
            return false;
        }

        // Get first message for subject
        $firstMessage = $messages->first();

        // Get participants
        $participants = DB::connection('drupal')
            ->table('pm_index')
            ->where('thread_id', $drupalThreadId)
            ->where('type', 'user')
            ->select('recipient', 'is_new', 'deleted')
            ->distinct()
            ->get();

        // Map participants to Laravel users
        $participantData = [];
        foreach ($participants as $participant) {
            $laravelUserId = $this->userMap[$participant->recipient] ?? null;
            if ($laravelUserId) {
                $participantData[$laravelUserId] = [
                    'is_read' => !$participant->is_new,
                    'is_deleted' => $participant->deleted > 0,
                    'last_read_at' => $participant->is_new ? null : now(),
                ];
            }
        }

        // Skip if no valid participants (at least 2 needed for a conversation)
        if (count($participantData) < 2) {
            return false;
        }

        // Create thread
        $thread = MessageThread::create([
            'drupal_thread_id' => $drupalThreadId,
            'subject' => $firstMessage->subject ?: 'Без темы',
        ]);

        // Attach participants
        $thread->participants()->attach($participantData);

        // Import messages
        foreach ($messages as $drupalMessage) {
            $senderLaravelId = $this->userMap[$drupalMessage->author] ?? null;

            if (!$senderLaravelId) {
                continue; // Skip messages from unmapped users
            }

            // Use DB insert directly to avoid Eloquent timestamp manipulation
            $messageDateTime = gmdate('Y-m-d H:i:s', $drupalMessage->timestamp);

            DB::table('messages')->insert([
                'drupal_mid' => $drupalMessage->mid,
                'thread_id' => $thread->id,
                'sender_id' => $senderLaravelId,
                'body' => $this->cleanBody($drupalMessage->body),
                'created_at' => $messageDateTime,
                'updated_at' => $messageDateTime,
            ]);
        }

        // Update thread timestamps using UTC
        $thread->update([
            'created_at' => gmdate('Y-m-d H:i:s', $firstMessage->timestamp),
            'updated_at' => gmdate('Y-m-d H:i:s', $messages->last()->timestamp),
        ]);

        return true;
    }

    private function cleanBody(string $body): string
    {
        // Remove HTML tags but preserve line breaks
        $body = preg_replace('/<br\s*\/?>/i', "\n", $body);
        $body = preg_replace('/<\/p>/i', "\n\n", $body);
        $body = strip_tags($body);

        // Decode HTML entities
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace
        $body = preg_replace('/\n{3,}/', "\n\n", $body);
        $body = trim($body);

        return $body;
    }
}
