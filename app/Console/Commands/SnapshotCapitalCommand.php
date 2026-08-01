<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SnapshotCapitalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'capital:snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take a daily snapshot of the total net capital';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Calculating current capital...');
        $capitalData = \App\Services\CapitalService::calculateCurrentCapital();

        \App\Models\CapitalSnapshot::updateOrCreate(
            ['snapshot_date' => today()],
            [
                'net_capital' => $capitalData['net_capital'],
                'details' => $capitalData['details'],
            ]
        );

        $this->info('Capital snapshot saved successfully for ' . today()->toDateString());
    }
}
