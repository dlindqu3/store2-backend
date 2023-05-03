<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
## for bcrypt: 
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function index()
    {
        return User::all(); 
    }

    public function register(Request $request){

        // FROM LARAVEL DOCUMENTATION: 
        // XHR Requests & Validation
        // In this example, we used a traditional form to send data to the application. However, many applications receive XHR requests from a JavaScript powered frontend. When using the validate method during an XHR request, Laravel will not generate a redirect response. Instead, Laravel generates a JSON response containing all of the validation errors. This JSON response will be sent with a 422 HTTP status code.

        // so, un-comment this once I start the xhr requests from react 

        $fields = $request->validate([
            'username' => 'required|string', 
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => bcrypt($request['password'])
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201); 
    }

    // this method only works if the "/logout" route is a protected sanctum route in api.php
    // takes the current user's active token as a  bearer token 
    public function logout(Request $request){
        $request->user()->tokens()->delete();

        return [
            "message" => "logout successful"
        ];
    }

    public function login(Request $request){
        
        $currentUser = User::where('email', $request["email"])->first();

        if(!$currentUser || !Hash::check($request['password'], $currentUser->password)){
            return response([
                "message" => "Incorrect username or password."
            ], 401); 
        }

        $token = $currentUser->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $currentUser,
            'token' => $token
        ];

        return response($response, 201); 
    }


}
