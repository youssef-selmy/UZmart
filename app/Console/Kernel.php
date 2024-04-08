<?php
declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
         $schedule->command('email:send:by:time')->hourly();
         $schedule->command('remove:expired:closed:dates')->daily();
         $schedule->command('remove:expired:delivery:point:closed:dates')->daily();
         $schedule->command('remove:expired:stories')->daily();
         $schedule->command('remove:expired:models')->hourly();
         $schedule->command('remove:expired:warehouse:closed:dates')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
