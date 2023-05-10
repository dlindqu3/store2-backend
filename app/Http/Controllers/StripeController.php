<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Stripe;


class StripeController extends Controller
{
    public function store_order_with_arg($order_data)
    {
        return Order::create($order_data);
    }

    public function handle_checkout(Request $request)
    {
        $stripe = new \Stripe\StripeClient(env("STRIPE_PRIVATE_KEY"));

        $req = json_decode($request->getContent(), true);

        $line_items = []; 

        $email = "";

        foreach($req as $key => $val) {
            $new_line_item = [
                'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $val["product_item"]["name"],
                ],
                'unit_amount' => $val["product_item"]["price"] * 100,
                ],
                'quantity' => $val["cart_item"]["quantity"]
            ];
            
            $email = $val["user_email"];

            array_push($line_items, $new_line_item);
        }

        $checkout_session = $stripe->checkout->sessions->create([
            'shipping_address_collection' => ['allowed_countries' => ['US']],
            'payment_method_types' => ['card'],
            "customer_email" => $email,
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => 'http://localhost:3000/checkout-success',
            'cancel_url' => 'http://localhost:3000/checkout-cancelled',
          ]);
          


        return $checkout_session->url; 
    }

    public function stripe_webhook(Request $request)
    {
        $stripe_webhook_secret = env("STRIPE_WEBHOOK_SECRET");
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($request, true), 
                $sig_header,
                $stripe_webhook_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }
        // Handle the event
       
        $resObj = [];

        if ($event->type === "payment_intent.succeeded"){
        
            $resObj["event"] = $event; 
            
            
            $current_email = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["email"];
            $current_address = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"];
            
            // get user.id associated with the $current_email
            $current_user = User::where('email', $current_email)->first();
            $current_user_id = $current_user["id"];

            $new_order_data = [
                "user_id" => $current_user_id,
                "total_cost"=> $event["data"]["object"]["charges"]["data"][0]["amount"] / 100,
                "address_line_one"=> $current_address["line1"],
                "address_line_two"=> $current_address["line2"],
                "city" => $current_address["city"],
                "state" => $current_address["state"],
                "postal_code" => $current_address["postal_code"],
                "country" => $current_address["country"]
            ];

            // create new ORDER object 
            $this->store_order_with_arg($new_order_data);

            // delete user's current CART 

            // create new CART for user 

            // MUST RETURN SOMETHING FOR STRIPE WEBHOOK TO WORK 
            return $resObj;
        } 
        http_response_code(200);
    }
}
