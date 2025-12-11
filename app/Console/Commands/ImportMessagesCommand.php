<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMessagesCommand extends Command
{
    protected $signature = 'import:messages {--limit= : Limit number of messages}';
    protected $description = 'Import private messages from Drupal';

    private array $userMap = [];
    private array $threadMap = [];

    public function handle(): int
    {
        $this->info('Starting private messages import from Drupal...');

        $this->userMap = User::pluck('id', 'drupal_uid')->toArray();

        // Step 1: Import threads
        $this->info('Step 1: Importing message threads...');
        $this->importThreads();

        // Step 2: Import messages
        $this->info('Step 2: Importing messages...');
        $this->importMessages();

        // Step 3: Import participants
        $this->info('Step 3: Importing thread participants...');
        $this->importParticipants();

        $this->newLine();
        $this->info("Import completed!");
        $this->table(
            ['Entity', 'Count'],
            [
                ['Threads', MessageThread::count()],
                ['Messages', Message::count()],
            ]
        );

        return Command::SUCCESS;
    }

    private function importThreads(): void
    {
        // Get unique thread IDs from pm_index
        $threadIds = DB::connection('drupal')
            ->table('pm_index')
            ->distinct()
            ->pluck('thread_id');

        $bar = $this->output->createProgressBar($threadIds->count());
        $bar->start();

        foreach ($threadIds as $threadId) {
            if (MessageThread::where('drupal_thread_id', $threadId)->exists()) {
                $bar->advance();
                continue;
            }

            // Get first message to use as thread subject
            $firstMessage = DB::connection('drupal')
                ->table('pm_message as m')
                ->join('pm_index as i', 'm.mid', '=', 'i.mid')
                ->where('i.thread_id', $threadId)
                ->orderBy('m.timestamp')
                ->first();

            if ($firstMessage) {
                $thread = MessageThread::create([
                    'drupal_thread_id' => $threadId,
                    'subject' => $firstMessage->subject ?: 'Без темы',
                    'created_at' => \Carbon\Carbon::createFromTimestamp($firstMessage->timestamp),
                ]);
                $this->threadMap[$threadId] = $thread->id;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function importMessages(): void
    {
        // Reload thread map
        $this->threadMap = MessageThread::pluck('id', 'drupal_thread_id')->toArray();

        $query = DB::connection('drupal')
            ->table('pm_message as m')
            ->join('pm_index as i', 'm.mid', '=', 'i.mid')
            ->select('m.*', 'i.thread_id')
            ->distinct()
            ->orderBy('m.mid');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $total = (clone $query)->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $imported = 0;
        $skipped = 0;

        $query->chunk(500, function ($messages) use (&$imported, &$skipped, $bar) {
            foreach ($messages as $msg) {
                if (Message::where('drupal_mid', $msg->mid)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $threadId = $this->threadMap[$msg->thread_id] ?? null;
                $senderId = $this->userMap[$msg->author] ?? null;

                if (!$threadId || !$senderId) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                try {
                    Message::create([
                        'drupal_mid' => $msg->mid,
                        'thread_id' => $threadId,
                        'sender_id' => $senderId,
                        'body' => $msg->body,
                        'created_at' => \Carbon\Carbon::createFromTimestamp($msg->timestamp),
                        'updated_at' => \Carbon\Carbon::createFromTimestamp($msg->timestamp),
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Messages - Imported: {$imported}, Skipped: {$skipped}");
    }

    private function importParticipants(): void
    {
        $this->threadMap = MessageThread::pluck('id', 'drupal_thread_id')->toArray();

        $participants = DB::connection('drupal')
            ->table('pm_index')
            ->select('thread_id', 'recipient', 'is_new', 'deleted')
            ->distinct()
            ->get();

        $bar = $this->output->createProgressBar($participants->count());
        $bar->start();

        $added = 0;

        foreach ($participants as $p) {
            $threadId = $this->threadMap[$p->thread_id] ?? null;
            $userId = $this->userMap[$p->recipient] ?? null;

            if (!$threadId || !$userId) {
                $bar->advance();
                continue;
            }

            $thread = MessageThread::find($threadId);
            if ($thread && !$thread->participants()->where('user_id', $userId)->exists()) {
                $thread->participants()->attach($userId, [
                    'is_read' => !$p->is_new,
                    'is_deleted' => (bool) $p->deleted,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $added++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Participants added: {$added}");
    }
}
