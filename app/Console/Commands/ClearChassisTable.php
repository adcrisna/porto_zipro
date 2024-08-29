<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ClearChassisTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:chassis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clearing chassis table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('chassists')->truncate();
    }
}
