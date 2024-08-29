<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BasePrice;
use App\Jobs\DeleteCarJob;
use DB;

class DeleteCar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:car';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleting Hiace Cars';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $baseprice = BasePrice::where('brand', 'TOYOTA')->where('modelName', 'HIACE')->get();
        // $listrik = BasePrice::where('typeDetail', 'like', '%LISTRIK%')->get();
        foreach ($baseprice as $key => $base) {
            $base->delete();
        }

        // foreach ($listrik as $keylistrik => $baselistrik) {
        //     $baselistrik->delete();
        // }
    }
}
