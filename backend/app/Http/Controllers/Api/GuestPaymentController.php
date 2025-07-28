<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCart;
use App\Models\Payment;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuestPaymentController extends Controller
{
    /**
     * Process guest payment (creates user if needed)
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_items' => 'required|array|min:1',
            'cart_items.*.course_id' => 'required|exists:courses,id',
            'cart_items.*.price' => 'required|numeric',
            'payment_method' => 'required|in:credit_card,pix,boleto',
            'gateway' => 'required|in:getnet,asaas',
            'coupon_code' => 'nullable|string',
            'customer' => 'required|array',
            'customer.name' => 'required|string|max:255',
            'customer.email' => 'required|email|max:255',
            'customer.phone' => 'nullable|string|max:20',
            'customer.cpf' => 'nullable|string|max:14',
            'customer.birth_date' => 'nullable|date',
            'customer.create_account' => 'boolean',
            'customer.password' => 'required_if:customer.create_account,true|min:8',
            // Payment details based on method
            'card' => 'required_if:payment_method,credit_card|array',
            'card.number' => 'required_if:payment_method,credit_card|string',
            'card.holder_name' => 'required_if:payment_method,credit_card|string',
            'card.expiry_month' => 'required_if:payment_method,credit_card|string|size:2',
            'card.expiry_year' => 'required_if:payment_method,credit_card|string|size:4',
            'card.cvv' => 'required_if:payment_method,credit_card|string|size:3',
            'installments' => 'required_if:payment_method,credit_card|integer|min:1|max:12',
            'due_date' => 'required_if:payment_method,boleto|date|after:today'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = collect($request->cart_items)->sum('price');
            $discount = 0;
            $coupon = null;

            // Apply coupon if provided
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', strtoupper($request->coupon_code))
                               ->valid()
                               ->first();
                
                if ($coupon && $coupon->canBeUsed()) {
                    $discount = $coupon->calculateDiscount($subtotal);
                }
            }

            $total = max(0, $subtotal - $discount);

            // Create or find user
            $userData = $request->customer;
            $user = User::where('email', $userData['email'])->first();

            if (!$user) {
                if ($userData['create_account'] ?? false) {
                    // Create new user account
                    $user = User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'phone' => $userData['phone'] ?? null,
                        'cpf' => $userData['cpf'] ?? null,
                        'birth_date' => $userData['birth_date'] ?? null,
                        'password' => Hash::make($userData['password']),
                        'user_type' => 'student'
                    ]);
                } else {
                    // Create guest user (no password)
                    $user = User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'phone' => $userData['phone'] ?? null,
                        'cpf' => $userData['cpf'] ?? null,
                        'birth_date' => $userData['birth_date'] ?? null,
                        'password' => Hash::make(Str::random(32)), // Random password for guest
                        'user_type' => 'student'
                    ]);
                }
            } else {
                // Update existing user info if needed
                $user->update([
                    'name' => $userData['name'],
                    'phone' => $userData['phone'] ?? $user->phone,
                    'cpf' => $userData['cpf'] ?? $user->cpf,
                    'birth_date' => $userData['birth_date'] ?? $user->birth_date
                ]);
            }

            $paymentResults = [];

            // Process payment for each course
            foreach ($request->cart_items as $item) {
                $course = \App\Models\Course::find($item['course_id']);
                
                // Check if user is already enrolled
                if ($user->enrollments()->where('course_id', $course->id)->exists()) {
                    continue; // Skip if already enrolled
                }

                // Create payment record
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'amount' => $item['price'],
                    'payment_method' => $request->payment_method,
                    'gateway' => $request->gateway,
                    'status' => 'pending'
                ]);

                // Process payment based on method and gateway
                $paymentService = $this->getPaymentService($request->gateway);
                $result = $paymentService->processPayment($payment, $request->all());

                $paymentResults[] = [
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'payment_id' => $payment->id,
                    'result' => $result
                ];

                // If payment is successful, create enrollment
                if ($result['success'] && ($result['status'] === 'paid' || $result['status'] === 'pending')) {
                    Enrollment::create([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'payment_id' => $payment->id,
                        'status' => $payment->status === 'paid' ? 'active' : 'pending',
                        'enrolled_at' => now()
                    ]);
                }
            }

            // Apply coupon usage if successful payments
            if ($coupon && $discount > 0) {
                $successfulPayments = collect($paymentResults)->where('result.success', true)->count();
                if ($successfulPayments > 0) {
                    $coupon->incrementUsage();
                }
            }

            // Clear cart
            $sessionId = $request->header('X-Session-ID') ?: session()->getId();
            ShoppingCart::clearCart($sessionId, $user->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pagamento processado com sucesso',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email']),
                    'total' => $total,
                    'discount' => $discount,
                    'subtotal' => $subtotal,
                    'coupon' => $coupon ? $coupon->only(['code', 'name', 'formatted_value']) : null,
                    'payments' => $paymentResults,
                    'account_created' => !$user->wasRecentlyCreated ? false : ($userData['create_account'] ?? false)
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cart summary for checkout
     */
    public function getCartSummary(Request $request)
    {
        $sessionId = $request->header('X-Session-ID') ?: session()->getId();
        $items = ShoppingCart::getCartItems($sessionId, null);

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Carrinho vazio'
            ], 422);
        }

        $subtotal = $items->sum('total');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'subtotal' => $subtotal,
                'count' => $items->count()
            ]
        ]);
    }

    private function getPaymentService($gateway)
    {
        switch ($gateway) {
            case 'asaas':
                return app(\App\Services\AsaasPaymentService::class);
            case 'getnet':
            default:
                return app(\App\Services\GetnetService::class);
        }
    }
}
