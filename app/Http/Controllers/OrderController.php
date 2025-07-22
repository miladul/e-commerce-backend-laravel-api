<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return Order::with('items.product')->where('user_id', $request->user()->id)->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $total += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'user_id'     => $user->id,
                'total_price' => $total,
                'status'      => 'pending',
            ]);

            foreach ($request->items as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->price,
                ]);
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();
            return response()->json($order->load('items.product'));
        } catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Order $order)
    {
        return $order->load('items.product');
    }

    public function update(Request $request, Order $order)
    {
        $request->validate(['status' => 'required']);
        $order->update(['status' => $request->status]);
        return response()->json($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Order cancelled']);
    }
}

