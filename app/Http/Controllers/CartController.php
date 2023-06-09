<?php

namespace App\Http\Controllers;

use App\Models\Cart; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // total_cost returns as a NUMBER here 
        return Cart::all(); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // getting errors from $request->validate([]); 

        return Cart::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user_id) 
    {   
        // can see table names in migration files
        // total_cost returns as a STRING here, with DB::select. WHY 
        // $sql = DB::select('select * from carts where user_id = :id', ['id' => $user_id]);

        // total_cost returns as a number here, as expected
        $sql = Cart::select('*')
        ->where('user_id', '=', $user_id)
        ->get();

        return $sql; 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cart = Cart::find($id);
        $cart->update($request->all());
        return $cart;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // returns 1 if delete successful and 0 if not 
        return Cart::destroy($id);
    }
}
