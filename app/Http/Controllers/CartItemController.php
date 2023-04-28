<?php

namespace App\Http\Controllers;

use App\Models\CartItem; 
use Illuminate\Http\Request;

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
    public function show(string $id)
    {
        return CartItem::find($id); 
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
