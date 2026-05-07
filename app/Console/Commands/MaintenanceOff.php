<?php

namespace App\Console\Commands;

use App\Models\SystemSetting;
use Illuminate\Console\Command;

class MaintenanceOff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:off';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Turn off system maintenance mode';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        SystemSetting::updateOrCreate(
            ['setting_key' => 'maintenance_mode'],
            ['setting_value' => '0']
        );

        $this->info('✓ Maintenance mode is now OFF');

        return self::SUCCESS;
    }
}
