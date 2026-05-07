<?php

namespace App\Console\Commands;

use App\Models\SystemSetting;
use Illuminate\Console\Command;

class MaintenanceOn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:on';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Turn on system maintenance mode';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        SystemSetting::updateOrCreate(
            ['setting_key' => 'maintenance_mode'],
            ['setting_value' => '1']
        );

        $this->info('✓ Maintenance mode is now ON');

        return self::SUCCESS;
    }
}
