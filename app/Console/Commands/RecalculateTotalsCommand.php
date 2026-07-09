<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectBand;
use Illuminate\Console\Command;

class RecalculateTotalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sy2:recalculate-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate all cached financial totals for projects and bands';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating band totals...');
        $bands = ProjectBand::all();
        $bar = $this->output->createProgressBar(count($bands));
        foreach ($bands as $band) {
            $band->recalculateCachedTotals();
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $this->info('Recalculating project totals...');
        $projects = Project::all();
        $bar2 = $this->output->createProgressBar(count($projects));
        foreach ($projects as $project) {
            $project->recalculateCachedTotals();
            $bar2->advance();
        }
        $bar2->finish();
        $this->newLine();

        $this->info('Done!');
    }
}
