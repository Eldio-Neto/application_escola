<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->has('active')) {
            $query->active();
        }

        if ($request->has('valid')) {
            $query->valid();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $coupons = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $coupons
        ]);
    }

    /**
     * Store a newly created coupon
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0|max:' . ($request->type === 'percentage' ? '100' : '99999.99'),
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'active' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'code', 'name', 'description', 'type', 'value', 'minimum_amount',
            'usage_limit', 'usage_limit_per_user', 'valid_from', 'valid_until',
            'active', 'category_ids', 'course_ids'
        ]);

        $data['code'] = strtoupper($data['code']);

        $coupon = Coupon::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Cupom criado com sucesso',
            'data' => $coupon
        ], 201);
    }

    /**
     * Display the specified coupon
     */
    public function show(Coupon $coupon)
    {
        return response()->json([
            'success' => true,
            'data' => $coupon
        ]);
    }

    /**
     * Update the specified coupon
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0|max:' . ($request->type === 'percentage' ? '100' : '99999.99'),
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'active' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'code', 'name', 'description', 'type', 'value', 'minimum_amount',
            'usage_limit', 'usage_limit_per_user', 'valid_from', 'valid_until',
            'active', 'category_ids', 'course_ids'
        ]);

        $data['code'] = strtoupper($data['code']);

        $coupon->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cupom atualizado com sucesso',
            'data' => $coupon->fresh()
        ]);
    }

    /**
     * Remove the specified coupon
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cupom excluído com sucesso'
        ]);
    }

    /**
     * Validate a coupon code
     */
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::where('code', strtoupper($request->code))
                       ->valid()
                       ->first();

        if (!$coupon || !$coupon->canBeUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'Cupom inválido ou expirado'
            ], 422);
        }

        $discount = $coupon->calculateDiscount($request->amount);

        return response()->json([
            'success' => true,
            'data' => [
                'coupon' => $coupon,
                'discount' => $discount,
                'original_amount' => $request->amount,
                'final_amount' => max(0, $request->amount - $discount),
                'can_use' => $discount > 0
            ]
        ]);
    }

    /**
     * Apply coupon and increment usage
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::where('code', strtoupper($request->code))
                       ->valid()
                       ->first();

        if (!$coupon || !$coupon->canBeUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'Cupom inválido ou expirado'
            ], 422);
        }

        $discount = $coupon->calculateDiscount($request->amount);

        if ($discount > 0) {
            $coupon->incrementUsage();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cupom aplicado com sucesso',
            'data' => [
                'coupon' => $coupon->fresh(),
                'discount' => $discount,
                'original_amount' => $request->amount,
                'final_amount' => max(0, $request->amount - $discount)
            ]
        ]);
    }
}
