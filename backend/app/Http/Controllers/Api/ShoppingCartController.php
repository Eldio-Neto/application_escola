<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCart;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ShoppingCartController extends Controller
{
    /**
     * Display the cart contents
     */
    public function index(Request $request)
    {
        $sessionId = $request->header('X-Session-ID') ?: session()->getId();
        $userId = auth()->id();

        $items = ShoppingCart::getCartItems($sessionId, $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => $items->sum('total'),
                'count' => $items->count()
            ]
        ]);
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'quantity' => 'integer|min:1|max:1' // For courses, quantity is always 1
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $course = Course::find($request->course_id);
        if (!$course->active) {
            return response()->json([
                'success' => false,
                'message' => 'Este curso não está disponível para compra'
            ], 422);
        }

        $sessionId = $request->header('X-Session-ID') ?: session()->getId();
        $userId = auth()->id();

        // Check if user is already enrolled in this course
        if ($userId && $course->enrollments()->where('user_id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Você já está matriculado neste curso'
            ], 422);
        }

        // Check if course is already in cart
        $existingItem = ShoppingCart::where('course_id', $course->id)
            ->where(function($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();

        if ($existingItem) {
            return response()->json([
                'success' => false,
                'message' => 'Este curso já está no seu carrinho'
            ], 422);
        }

        // Add to cart
        $cartItem = ShoppingCart::create([
            'session_id' => $userId ? null : $sessionId,
            'user_id' => $userId,
            'course_id' => $course->id,
            'price' => $course->price,
            'quantity' => 1,
            'expires_at' => now()->addHours(24) // Cart expires in 24 hours
        ]);

        $cartItem->load('course');

        return response()->json([
            'success' => true,
            'message' => 'Curso adicionado ao carrinho',
            'data' => $cartItem
        ], 201);
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, $id)
    {
        $sessionId = $request->header('X-Session-ID') ?: session()->getId();
        $userId = auth()->id();

        $cartItem = ShoppingCart::where('id', $id)
            ->where(function($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item não encontrado no carrinho'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removido do carrinho'
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request)
    {
        $sessionId = $request->header('X-Session-ID') ?: session()->getId();
        $userId = auth()->id();

        ShoppingCart::clearCart($sessionId, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Carrinho limpo com sucesso'
        ]);
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Código do cupom é obrigatório',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get cart items
        $sessionId = $request->header('X-Session-ID') ?: session()->getId();
        $userId = auth()->id();
        $items = ShoppingCart::getCartItems($sessionId, $userId);

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Carrinho vazio'
            ], 422);
        }

        // Find and validate coupon
        $coupon = \App\Models\Coupon::where('code', $request->coupon_code)
            ->valid()
            ->first();

        if (!$coupon || !$coupon->canBeUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'Cupom inválido ou expirado'
            ], 422);
        }

        $cartTotal = $items->sum('total');
        $discount = $coupon->calculateDiscount($cartTotal);

        return response()->json([
            'success' => true,
            'data' => [
                'coupon' => $coupon,
                'discount' => $discount,
                'original_total' => $cartTotal,
                'final_total' => max(0, $cartTotal - $discount)
            ]
        ]);
    }

    /**
     * Transfer guest cart to user account
     */
    public function transferCart(Request $request)
    {
        $sessionId = $request->header('X-Session-ID') ?: session()->getId();
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        // Transfer items from session to user
        ShoppingCart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update([
                'user_id' => $userId,
                'session_id' => null
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Carrinho transferido com sucesso'
        ]);
    }
}
