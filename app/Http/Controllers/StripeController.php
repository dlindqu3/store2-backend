<?php

namespace App\Http\Controllers;

require_once '../vendor/autoload.php';
use Illuminate\Support\Facades\Log;
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
        
        info("stripe webhook called");
        echo "stripe webhook called echo"; 

        $endpoint_secret = env("STRIPE_WEBHOOK_SECRET");
        // file_put_contents("php://stderr", "endpoint secret: $endpoint_secret\n");
        $m1 = "endpoint secret " . $endpoint_secret;
        info($m1);
        echo $m1 . " echo"; 
        // Log::info($m1);

        $payload = @file_get_contents('php://input');
        // file_put_contents("php://stderr", "payload: $payload\n");
        $m2 = "payload " . $payload;
        info($m2);
        echo $m2 . " echo"; 
        // Log::info($m2);

        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        // file_put_contents("php://stderr", "sig header: $sig_header\n");
        $m3 = "sig header " . $sig_header;
        info($m3);
        echo $m3 . " echo"; 
        // Log::info($m3);

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
            // file_put_contents("php://stderr", "event: $event\n"); 
            $m4 = "event " . $event;
            info($m4);
            echo $m4 . " echo"; 
            // Log::info($m4);

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
       
        // $resObj = [];

        // if ($event->type === "payment_intent.succeeded"){
        
        //     // THIS STILL DIDN'T FIX THE PROBLEM 
        //     $intent_success_obj = [];
        //     $intent_success_obj["event"] = $event;
        //     return response()->json($intent_success_obj, 200);

        //     // $resObj["event"] = $event; 
            
            
        //     // $current_email = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["email"];
        //     // $current_address = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"];
            
        //     // // get user.id associated with the $current_email
        //     // $current_user = User::where('email', $current_email)->first();
        //     // $current_user_id = $current_user["id"];

        //     // $new_order_data = [
        //     //     "user_id" => $current_user_id,
        //     //     "total_cost"=> $event["data"]["object"]["charges"]["data"][0]["amount"] / 100,
        //     //     "address_line_one"=> $current_address["line1"],
        //     //     "address_line_two"=> $current_address["line2"],
        //     //     "city" => $current_address["city"],
        //     //     "state" => $current_address["state"],
        //     //     "postal_code" => $current_address["postal_code"],
        //     //     "country" => $current_address["country"]
        //     // ];

        //     // // create new ORDER object 
        //     // $this->store_order_with_arg($new_order_data);

        //     // // delete user's current CART 

        //     // // create new CART for user 

        //     // // MUST RETURN SOMETHING FOR STRIPE WEBHOOK TO WORK 
        //     // return $resObj;
        // } else {
        //     echo "Recieved other event type: " . $event->type;
        //     return response()->json($event, 200);
        // }

        return Response::json([ 
            'event' => $event
        ], 201);
    }
}
