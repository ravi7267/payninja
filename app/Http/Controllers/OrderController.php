<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOrderJob;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'order_number' => Str::uuid(),
                'amount' => $validated['amount'],
            ]);

            DB::commit();

            // Dispatch job
            ProcessOrderJob::dispatch($order);

            return response()->json(['message' => 'Order created and processing started.', 'order' => $order], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create order.'], 500);
        }
    }
}
