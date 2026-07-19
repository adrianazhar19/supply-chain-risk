<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Services\CentroidUpdateService;
use Illuminate\Console\Command;

class UpdateCountryCentroidsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'countries:update-centroids {--force : Force update all countries, ignoring existing coordinates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update latitude and longitude coordinates to country centroids for records that are NULL.';

    protected CentroidUpdateService $centroidService;

    public function __construct(CentroidUpdateService $centroidService)
    {
        parent::__construct();
        $this->centroidService = $centroidService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║   SCRI Country Centroid Coordinates Update Utility  ║');
        $this->info('╚══════════════════════════════════════════════════════╝');

        $query = Country::query();

        if (!$force) {
            $query->whereNull('latitude')->orWhereNull('longitude');
        }

        $countries = $query->get();
        $total = $countries->count();

        if ($total === 0) {
            $this->info('All countries already have coordinate settings. No updates required.');
            $this->info('Summary:');
            $this->line('Updated: <info>0</info>');
            $this->line('Skipped: <info>0</info>');
            $this->line('Errors:  <info>0</info>');
            return Command::SUCCESS;
        }

        $this->info("Found {$total} countries to check / update.");
        $this->newLine();

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($countries as $country) {
            try {
                $coords = $this->centroidService->resolveCentroid($country);

                if ($coords) {
                    $country->latitude = $coords[0];
                    $country->longitude = $coords[1];
                    $country->save();
                    $updated++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nError updating {$country->name}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Update Complete!');
        $this->line("Updated: <info>{$updated}</info>");
        $this->line("Skipped: <comment>{$skipped}</comment>");
        $this->line("Errors:  <error>{$errors}</error>");

        return Command::SUCCESS;
    }
}
