<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Order;
use App\Models\Cart;
use App\Models\User;
use Stripe;


class StripeController extends Controller
{
    public function store_order_with_arg($order_data)
    {
        return Order::create($order_data);
    }
    

    public function create_order_item_with_args(array $order_item_data)
    {
        return OrderItem::create($order_item_data);
    }


    public function delete_cart_with_args(string $user_id)
    {
        $delete = Cart::where('user_id', $user_id)
                    ->delete();
        // return Cart::destroy($user_id);
        return $delete;
        
    }

    public function create_cart_with_args(array $user_id_array)
    {
        return Cart::create($user_id_array);
    }


    public function handle_checkout(Request $request)
    {
        $stripe = new \Stripe\StripeClient(env("STRIPE_PRIVATE_KEY"));

        $req_content = json_decode($request->getContent(), true);

        info("req_content from handle_checkout, hard-coded num causes errors: "); 
        info($req_content); 
        info(gettype($req_content)); 

        $line_items = []; 

        $emails = []; 

        foreach($req_content as $key => $val){
            $email = $req_content[$key]["user_email"];
            array_push($emails, $email);
        }
        
        $user_email = $emails[0];

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
            
            array_push($line_items, $new_line_item);
        }

        $checkout_session = $stripe->checkout->sessions->create([
            'shipping_address_collection' => ['allowed_countries' => ['US']],
            'payment_method_types' => ['card'],
            "customer_email" => $user_email,
            'line_items' => $line_items,
            // 'payment_intent_data' => [ 
            //        'metadata' => [ 
            //                 // stripe metadata can only be strings up to 500 characters
            //                 "itemsProductsData" => json_encode($new_metadata),
            //        ]
            // ], 
            'mode' => 'payment',
            'success_url' => 'https://store2-frontend.vercel.app/checkout-success',
            'cancel_url' => 'https://store2-frontend.vercel.app/checkout-cancelled',
          ]);
        
        info("checkout url: "); 
        info($checkout_session->url);

        return $checkout_session->url; 
    }


    public function stripe_webhook()
    {
        \Stripe\Stripe::setApiKey(env("STRIPE_PRIVATE_KEY"));

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

        info("event from stripe webhook: " . $event);
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

            $total = $event["data"]["object"]["amount"] / 100; 
            $email = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["email"];
            $city = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["city"];
            $country = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["country"];
            $line1 = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["line1"];
            $line2 = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["line2"];
            $postal_code = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["postal_code"];
            $state = $event["data"]["object"]["charges"]["data"][0]["billing_details"]["address"]["state"];

            $sql = DB::table("users")
                ->where('email', $email)
                ->first();
            $user_data = json_decode(json_encode($sql), true);
            info("sql user data: "); 
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

            ## get cart_id based on $user_data
            $user_id = $user_data['id']; 
            $sql1 = DB::table("carts")
            ->where("user_id", $user_id)
            ->first();

            $cart_data = json_decode(json_encode($sql1), true);
            info("current cart: "); 
            info($cart_data); 


            ## get all cart_items for associated cart_id
            $cart_id = $cart_data["id"];            
            $sql2 = DB::select('select * from cart_items where cart_id = :id', ['id' => $cart_id]);

            info("sql2 cart items data: "); 
            $cart_items_data = json_decode(json_encode($sql2), true);
            info($cart_items_data);

            ## create order items based on cart_items
            foreach($cart_items_data as $key => $val) {
                $current_item_obj = $cart_items_data[$key];

                $order_id = $order_data["id"]; 
                $product_id = $current_item_obj["product_id"];
                $quantity = $current_item_obj["quantity"]; 

                $new_order_item_data = [];
                $new_order_item_data['order_id'] = $order_id;
                $new_order_item_data['product_id'] = $product_id;
                $new_order_item_data['quantity'] = $quantity;

                // works 
                $new_order_item = self::create_order_item_with_args($new_order_item_data); 
                info("new order item created: "); 
                $new_item = json_decode(json_encode($new_order_item), true);
                info($new_item);

            }
            ## delete cart, WORKS 
            $deleted_cart = self::delete_cart_with_args($user_id); 
            info("cart deleted: "); 
            $deleted_cart_res = json_decode(json_encode($deleted_cart), true);
            info($deleted_cart_res);

            ## create new empty cart 
            info("calling create_cart_with_args: "); 
            $user_id_arr = [
                "user_id" => $user_id
            ]; 

            $new_cart = self::create_cart_with_args($user_id_arr);
            info("cart created: "); 
            $created_cart_res = json_decode(json_encode($new_cart), true);
            info($created_cart_res);

            return $event; 
        }

        http_response_code(200);
    }
}
