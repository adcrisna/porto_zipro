<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Jobs\MomenticXendit;
class LoopXenditMomentic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'momentic:xendit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $q = Transaction::where("contract_id","!=",null)->whereDate('updated_at', Carbon::today())->get();
        foreach($q as $d){
            dispatch(new MomenticXendit($d->contract_id));
            //MomenticXendit::dispatch();
            error_log("dispatched");
        }
        error_log($q->count());
        return Command::SUCCESS;
    }
}
