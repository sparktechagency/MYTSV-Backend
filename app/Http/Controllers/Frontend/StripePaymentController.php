<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripePaymentController extends Controller
{
    public function paymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason'         => 'required|string|in:Uploading video,Promoting YouTube Link,Onsite account creation',
            'amount'         => 'required',
            'payment_method' => 'required',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $paymentIntent = PaymentIntent::create([
                'amount'         => $request->amount * 100,
                'currency'       => 'usd',
                'payment_method' => $request->payment_method,
                'metadata'       => [
                    'service_name' => $request->reason,
                ],
            ]);
            return response()->json([
                'data' => $paymentIntent,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
    }
}
