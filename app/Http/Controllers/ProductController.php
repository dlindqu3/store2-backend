<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::all(); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // getting errors from $request->validate([]); 

        // $request->validate([
        //     'name' => 'required',
        //     'slug' => 'required',
        //     'description' => 'required', 
        //     'image' => 'required',
        //     'brand' => 'required',
        //     'category' => 'required',
        //     'price' => 'required' 
        // ]);

        return Product::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Product::find($id); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);
        $product->update($request->all());
        return $product;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // returns 1 if delete successful and 0 if not 
        return Product::destroy($id);
    }

    public function get_products_in_cart(Request $request)
    {
        $req = json_decode($request->getContent(), true);

        $sql = Product::select('*')
        ->whereIn('id', $req["productIds"])
        ->get();

        return $sql;

        // sample $res object: 
        // $res = {
        //     "productIds": [
        //       5,
        //       6
        //     ]
        //   }
    }
}
