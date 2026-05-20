<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Joke;
use Throwable;

class FetchJokeCommand extends Command
{
    protected $signature = 'fetch:joke {--once : run only once and exit}';
    protected $description = 'Fetch jokes every 5 minutes. Stop only by typing stop in the same terminal.';

    protected bool $shouldStop = false;
    protected int $interval = 300;

    public function handle(): int
    {
        $runOnce = (bool) $this->option('once');

        $stdinAvailable = (defined('STDIN') && is_resource(STDIN));
        if (! $stdinAvailable) {
            $this->warn('STDIN/TTY not available. Interactive "stop" will not be read in this session.');
            $this->line('If you want to stop by typing "stop", run with a TTY, e.g.: docker run -it --rm my-image php artisan fetch:joke');
        } else {
            @stream_set_blocking(STDIN, false);
            $this->info('Interactive mode. Type "stop" (or "exit"/"q") and press Enter to stop.');
        }

        do {
            $start = microtime(true);

            $this->fetchAndSave();

            if ($runOnce || $this->shouldStop) {
                break;
            }

            $elapsed = microtime(true) - $start;
            $sleepSeconds = (int) max(0, $this->interval - $elapsed);

            $this->interruptibleSleep($sleepSeconds);

        } while (! $this->shouldStop);

        $this->info('Stopped.');
        return 0;
    }

    protected function fetchAndSave(): void
    {
        try {
            $res = Http::timeout(10)->get(config('services.jokes.url') . '/jokes/random');
            if ($res->successful()) {
                $data = $res->json();

                if (empty($data['id']) || empty($data['value'])) {
                    $this->error('API response missing id or value. Response: ' . $this->shortJson($data));
                    return;
                }
                $externalId = (string) $data['id'];
                $text = (string) $data['value'];

                try {
                    $joke = Joke::firstOrCreate(
                        ['external_id' => $externalId],
                        ['joke_text' => $text]
                    );

                    if ($joke->wasRecentlyCreated) {
                        $this->info('Saved new joke: ' . mb_substr($text, 0, 120, 'UTF-8') . (mb_strlen($text, 'UTF-8') > 120 ? '...' : ''));
                    } else {
                        $this->info('Joke already exists (external_id=' . $externalId . ').');
                    }
                } catch (Throwable $e) {
                    $this->error('DB save error: ' . $e->getMessage());
                }
            } else {
                $this->error('API error: ' . $res->status());
            }
        } catch (Throwable $e) {
            $this->error('Request failed: ' . $e->getMessage());
        }
    }

    protected function interruptibleSleep(int $seconds): void
    {
        $elapsed = 0;
        while ($elapsed < $seconds) {
            if ($this->shouldStop) return;

            $read = [STDIN];
            $write = null;
            $except = null;
            $readCopy = $read;

            $num = @stream_select($readCopy, $write, $except, 1, 0);
            if ($num > 0) {
                $line = @fgets(STDIN);
                if ($line !== false) {
                    $cmd = strtolower(trim($line));
                    if (in_array($cmd, ['stop', 'exit', 'q'], true)) {
                        $this->info('Stop requested: ' . $cmd);
                        $this->shouldStop = true;
                        return;
                    }
                }
            }
            $elapsed += 1;
        }
    }
}
