<?php

namespace App\Http\Controllers;

// require_once '../vendor/autoload.php';
// use Illuminate\Support\Facades\Log;
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
        \Stripe\Stripe::setApiKey(env("STRIPE_PRIVATE_KEY"));
        
        $endpoint_secret = env("STRIPE_WEBHOOK_SECRET");
        $m1 = "endpoint secret " . $endpoint_secret;

        $payload = @file_get_contents('php://input');
        $m2 = "payload " . $payload;

        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $m3 = "sig header " . $sig_header;

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        
            $m4 = "event " . $event;


        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            file_put_contents("php://stderr", "⚠️ Webhook has invalid payload.\n");
            echo '⚠️ Webhook has invalid payload.';
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            file_put_contents("php://stderr", "⚠️  Webhook error while validating signature.\n");
            echo '⚠️  Webhook error while validating signature.';
            http_response_code(400);
            exit();
        }
        // Handle the event
      http_response_code(200);

    }
}
