<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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

        $req_content = json_decode($request->getContent(), true);

        info("req_content: "); 
        info($req_content); 
        info(gettype($req_content)); 

        $line_items = []; 

        $email = "";

        foreach($req_content as $key => $val) {
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

        ## NEW 
        // info("req from checkout: " . $req); 

        $checkout_session = $stripe->checkout->sessions->create([
            'shipping_address_collection' => ['allowed_countries' => ['US']],
            'payment_method_types' => ['card'],
            "customer_email" => $email,
            'line_items' => $line_items,
            
            ## works
            'payment_intent_data' => [ 
                   'metadata' => [ 
                            "itemsProductsData" => json_encode($req_content)
                   ]
            ], 
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

            // WORKS 
            $ip_arr = json_decode($event["data"]["object"]["charges"]["data"][0]["metadata"]["itemsProductsData"], true);
            info(gettype($ip_arr));
            info("IP ARR: "); 
            info($ip_arr);

            $total = $event["data"]["object"]["amount"] / 100; 
            $email = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["email"];
            $city = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["city"];
            $country = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["country"];
            $line1 = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["line1"];
            $line2 = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["line2"];
            $postal_code = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["postal_code"];
            $state = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["state"];

            ## NEW 
            ## get user_id for given email 
            // returns an array of cart_item objects
            $sql = DB::table("users")
                ->where('email', $email)
                ->first();
            info("sql user data: "); 
            $user_data = json_decode(json_encode($sql), true);
            info($user_data);


            $new_order_obj = [];
            $new_order_obj["user_id"] = $user_data["id"];
            $new_order_obj["total_cost"] = $total;
            $new_order_obj["address_line_one"] = $line1;
            $new_order_obj["address_line_two"] = $line2;
            $new_order_obj["city"] = $city;
            $new_order_obj["state"] = $state;
            $new_order_obj["postal_code"] = $postal_code;
            $new_order_obj["country"] = $country;

            ## create order, WORKS
            $new_order = Order::create($new_order_obj);
            info("new_order: "); 
            info($new_order);
            $order_data = json_decode($new_order, true); 
            info("new order id: ");
            info($order_data["id"]);

            ## create orderItems, all with same order_id

            ## delete cart 

            ## create new empty cart 


            return $event; 
        }

        http_response_code(200);
    }
}
