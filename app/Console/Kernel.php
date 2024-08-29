<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected $commands = [
        // '\App\Console\Commands\ExpiryCheckCommand',
        Commands\CheckStatusCommand::class,
        Commands\ClearChassisTable::class,
        Commands\DeleteCar::class,
        Commands\OrderRef::class,
        Commands\DeletePickUp::class,
        Commands\LoopXenditMomentic::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('check:status')->everyThirtyMinutes()->withoutOverlapping();
        $schedule->command('clear:chassis')->dailyAt('00:00');
        $schedule->command('delete:car')->dailyAt('07:00');
        $schedule->command('order:ref')->everyMinute()->withoutOverlapping();
        $schedule->command('delete:pickup')->everyMinute()->withoutOverlapping();
        $schedule->command('momentic:xendit')->hourly()->withoutOverlapping();
        // $schedule->command('order:ref')->everyThreeHours($minutes = 0);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
