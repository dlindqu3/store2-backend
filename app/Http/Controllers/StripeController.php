<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe;


class StripeController extends Controller
{
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
        $event = null;

        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($request, true)
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }
        // Handle the event
       
        if ($event->type === "payment_intent.succeeded"){
            // $currentEmail = $event.data.object.charges.data[0].billing_details.email;


        } else if ($event->type === "payment_intent.payment_failed"){

            
        }
        http_response_code(200);
    }
}
