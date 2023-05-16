<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Order::all(); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return Order::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Order::find($id); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::find($id);
        $order->update($request->all());
        return $order;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return Order::destroy($id);
    }

    ## ADD METHOD TO GET ALL ORDERS FOR GIVEN USER
    public function get_orders_with_user_id(Request $request)
    {
        $user_id = $request['user_id'];
        // returns an array of order objects
        $sql = Order::select('*')
        ->where('user_id', '=', $user_id)
        ->get();

        return $sql;
    }

}
