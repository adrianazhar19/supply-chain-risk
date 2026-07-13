<?php

namespace App\Console\Commands;

use App\Jobs\SyncNewsJob;
use App\Jobs\SyncExchangeRatesJob;
use App\Jobs\SyncWeatherJob;
use App\Jobs\SyncWorldBankJob;
use App\Jobs\RecalculateRisksJob;
use Illuminate\Console\Command;

class SyncAllDataCommand extends Command
{
    protected $signature   = 'scri:sync {--queue : Dispatch as queued jobs instead of running synchronously}';
    protected $description = 'Sync all external API data: News, Exchange Rates, Weather, World Bank, and recalculate risks.';

    public function handle(): int
    {
        $useQueue = $this->option('queue');

        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   SCRI Data Sync — ' . now()->format('Y-m-d H:i:s') . '   ║');
        $this->info('╚══════════════════════════════════════════╝');

        $tasks = [
            ['News Articles',    SyncNewsJob::class],
            ['Exchange Rates',   SyncExchangeRatesJob::class],
            ['Weather Data',     SyncWeatherJob::class],
            ['World Bank Data',  SyncWorldBankJob::class],
            ['Risk Recalculation', RecalculateRisksJob::class],
        ];

        foreach ($tasks as [$label, $job]) {
            $this->line(" ⟶  Syncing: <info>{$label}</info>");

            if ($useQueue) {
                dispatch(new $job());
                $this->line("    ✓ Dispatched to queue.");
            } else {
                try {
                    app($job)->handle(...$this->resolveJobDependencies($job));
                    $this->line("    ✓ Done.");
                } catch (\Exception $e) {
                    $this->error("    ✗ Failed: " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info('✅  Sync complete!');

        return Command::SUCCESS;
    }

    private function resolveJobDependencies(string $jobClass): array
    {
        $deps = [];
        $constructor = (new \ReflectionClass($jobClass))->getConstructor();
        if (!$constructor) return [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin()) {
                $deps[] = app($type->getName());
            }
        }
        return $deps;
    }
}
