<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderItem; 
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(array $order_data)
    {
        return OrderItem::create($order_data);
    }

    public function get_order_item_with_order_id_and_product_id(string $order_id, string $product_id)
    {
        // returns an array of cart_item objects
        $sql = OrderItem::select('*')
        ->where('order_id', '=', $order_id)
        ->where('producct_id', '=', $product_id)
        ->get();

        return $sql;
    }

    public function index()
    {
        return OrderItem::all(); 
    }

    public function get_order_items_with_order_id(Request $request)
    {
        $order_id = $request['order_id'];
        // returns an array of order item objects
        $sql = OrderItem::select('*')
        ->where('order_id', '=', $order_id)
        ->get();
    
        return $sql;
    }
}
