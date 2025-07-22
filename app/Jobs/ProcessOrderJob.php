<?php
namespace App\Jobs;

use App\Models\Order;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        DB::beginTransaction();

        try {
            sleep(2); 
            if (rand(0, 1)) {
                throw new Exception("Random processing failure!");
            }
            $this->order->update(['status' => 'completed']);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $this->order->update(['status' => 'failed']);

            throw $e;
        }
    }
}
