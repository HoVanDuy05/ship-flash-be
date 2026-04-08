<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function processPayment(Request $request, $rideId)
    {
        $request->validate([
            'method' => 'required|in:cash,momo,zalopay,vnpay,card',
            'amount' => 'required|numeric|min:0',
        ]);

        $ride = Ride::findOrFail($rideId);

        if ($ride->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($ride->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already processed'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'ride_id' => $ride->id,
                'method' => $request->method,
                'status' => 'pending',
                'amount' => $ride->final_price,
                'transaction_id' => 'TXN_' . Str::random(12) . '_' . time(),
            ]);

            // Process payment based on method
            $paymentResult = $this->processPaymentMethod($payment, $request->method);

            if ($paymentResult['success']) {
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'gateway_response' => $paymentResult['response'],
                ]);

                $ride->update([
                    'payment_status' => 'paid',
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'data' => $payment->load('ride')
                ]);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'notes' => $paymentResult['message'],
                ]);

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed: ' . $paymentResult['message']
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Payment processing error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPaymentHistory(Request $request)
    {
        $payments = $request->user()->rides()
            ->with('payment')
            ->whereHas('payment')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    public function getPaymentDetails(Request $request, $paymentId)
    {
        $payment = Payment::with(['ride.user', 'ride.driver'])
            ->whereHas('ride', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->findOrFail($paymentId);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    public function refundPayment(Request $request, $paymentId)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $payment = Payment::with('ride')
            ->whereHas('ride', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->findOrFail($paymentId);

        if ($payment->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment cannot be refunded'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Process refund based on payment method
            $refundResult = $this->processRefund($payment, $request->reason);

            if ($refundResult['success']) {
                $payment->update([
                    'status' => 'refunded',
                    'notes' => 'Refunded: ' . $request->reason,
                    'gateway_response' => $refundResult['response'],
                ]);

                $payment->ride->update([
                    'payment_status' => 'refunded',
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment refunded successfully',
                    'data' => $payment
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Refund failed: ' . $refundResult['message']
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Refund processing error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function processPaymentMethod($payment, $method)
    {
        // Simulate payment processing
        switch ($method) {
            case 'cash':
                return [
                    'success' => true,
                    'response' => ['method' => 'cash', 'collected' => true]
                ];

            case 'momo':
                return $this->processMomoPayment($payment);

            case 'zalopay':
                return $this->processZaloPayPayment($payment);

            case 'vnpay':
                return $this->processVnPayPayment($payment);

            case 'card':
                return $this->processCardPayment($payment);

            default:
                return ['success' => false, 'message' => 'Invalid payment method'];
        }
    }

    private function processMomoPayment($payment)
    {
        // Simulate MoMo payment processing
        sleep(1);
        return [
            'success' => true,
            'response' => [
                'provider' => 'momo',
                'transaction_id' => $payment->transaction_id,
                'status' => 'success'
            ]
        ];
    }

    private function processZaloPayPayment($payment)
    {
        // Simulate ZaloPay payment processing
        sleep(1);
        return [
            'success' => true,
            'response' => [
                'provider' => 'zalopay',
                'transaction_id' => $payment->transaction_id,
                'status' => 'success'
            ]
        ];
    }

    private function processVnPayPayment($payment)
    {
        // Simulate VNPay payment processing
        sleep(1);
        return [
            'success' => true,
            'response' => [
                'provider' => 'vnpay',
                'transaction_id' => $payment->transaction_id,
                'status' => 'success'
            ]
        ];
    }

    private function processCardPayment($payment)
    {
        // Simulate card payment processing
        sleep(1);
        return [
            'success' => true,
            'response' => [
                'provider' => 'card',
                'transaction_id' => $payment->transaction_id,
                'status' => 'success'
            ]
        ];
    }

    private function processRefund($payment, $reason)
    {
        // Simulate refund processing
        sleep(1);
        return [
            'success' => true,
            'response' => [
                'refund_id' => 'REF_' . Str::random(8) . '_' . time(),
                'original_transaction' => $payment->transaction_id,
                'amount' => $payment->amount,
                'status' => 'success'
            ]
        ];
    }
}
