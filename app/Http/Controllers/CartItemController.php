<?php

namespace App\Http\Controllers;

use App\Models\CartItem; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartItemController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CartItem::all(); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // getting errors from $request->validate([]); 

        return CartItem::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $cart_id)
    {
        // returns an array of cart_item objects
        $sql = DB::select('select * from cart_items where cart_id = :id', ['id' => $cart_id]);
        return $sql;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = CartItem::find($id);
        $product->update($request->all());
        return $product;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // returns 1 if delete successful and 0 if not 
        return CartItem::destroy($id);
    }
}
