<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** All Paypal Details class **/
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

// For Accepting live payments use
// use PayPalCheckoutSdk\Core\ProductionEnvironment;

/*Steps:
1) Change Paypal CLIENT_ID AND SECRET  
2) Use The ProductionEnvironment, instead of using SandboxEnvironment
*/

class PayPalApiController extends Controller
{
     public function createOrder(Request $request) 
     {
          $amount = $request->input('amount');

          // For Sandbox
          $environment = new SandboxEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'));

          // For Live
          // $environment = new ProductionEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'));

          $client = new PayPalHttpClient($environment);

          $request = new OrdersCreateRequest();
          $request->prefer('return=representation');

          $request->body = [
               "intent" => "CAPTURE",
               "purchase_units" => [
                    [
                         "amount" => [
                              "value" => $amount ?? '100',
                              "currency_code" => "USD"
                              //put other details here as well related to your order
                         ]
                    ]
               ],
               "application_context" => [
                    "cancel_url" => "https://staging.advisecubeconsulting.com",
                    "return_url" => "https://staging.advisecubeconsulting.com"
               ]
           ];

           try {
               // Call API with your client and get a response for your call
               $response = $client->execute($request);
               
               // If call returns body in response, you can get the deserialized version from the result attribute of the response
               return response()->json($response);
           }catch (HttpException $ex) {
               return response()->json(['error' => $ex->getMessage()]);
           }
     }

     /**
     * After successful paypal authentication from paypal window, paypal will call this function to capture the order
     */

     public function captureOrder(Request $request) 
     {
          $orderId = $request->input('orderId');

          // For Sandbox
          $environment = new SandboxEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'));

          // For Live
          // $environment = new ProductionEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'));

          $client = new PayPalHttpClient($environment);

          $request = new OrdersCaptureRequest($orderId);
          $request->prefer('return=representation');

          try {
               // Call API with your client and get a response for your call
               $response = $client->execute($request);
               
               // If call returns body in response, you can get the deserialized version from the result attribute of the response
               return response()->json($response);
           }catch (HttpException $ex) {
               return response()->json(['error' => $ex->getMessage()]);
           }
     }
}
