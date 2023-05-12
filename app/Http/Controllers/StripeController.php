<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            'success_url' => 'https://store2-frontend.vercel.app/checkout-success',
            'cancel_url' => 'https://store2-frontend.vercel.app/checkout-cancelled',
          ]);
          


        return $checkout_session->url; 
    }

    public function stripe_webhook()
    {
        // \Stripe\Stripe::setApiKey(env("STRIPE_PRIVATE_KEY"));

        $endpoint_secret = env("STRIPE_WEBHOOK_SECRET");
        $m1 = "endpoint secret " . $endpoint_secret;
        info("endpoint secret from stripe webhook: " . $m1);
        ## works 

        $payload = @file_get_contents('php://input');
        $m2 = "payload " . $payload;
        info("payload from stripe webhook: " . $m2);
        ## works 

        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $m3 = "sig header " . $sig_header;
        info("sig header from stripe webhook: " . $m3);
        ## works 

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );

            // info("event from stripe webhook: " . $event);
            ## works 

        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            info('⚠️ Webhook has invalid payload.');
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            info("⚠️ Webhook has invalid signature"); 
            http_response_code(400);
            exit();
        }

        if ($event->type === "payment_intent.succeeded"){
            info("PAYMENT INTENT SUCCEEDED: " . $event);
        }

        http_response_code(200);
    }
}
