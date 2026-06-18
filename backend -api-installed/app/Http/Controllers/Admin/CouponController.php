<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        return response()->json(Coupon::latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'       => 'required|string|unique:coupons,code|max:20',
            'type'       => 'required|in:percent,flat,free_delivery',
            'value'      => 'required_unless:type,free_delivery|numeric|min:0',
            'min_order'  => 'nullable|numeric|min:0',
            'max_uses'   => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
            'is_active'  => 'nullable|in:0,1,true,false',
        ]);

        $coupon = Coupon::create([
            'code'       => strtoupper($request->code),
            'type'       => $request->type,
            'value'      => $request->value ?? 0,
            'min_order'  => $request->min_order ?? 0,
            'max_uses'   => $request->max_uses,
            'times_used' => 0,
            'expires_at' => $request->expires_at,
            'is_active'  => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        return response()->json(['message' => 'Coupon created', 'coupon' => $coupon], 201);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code'       => 'sometimes|string|unique:coupons,code,' . $id . '|max:20',
            'type'       => 'sometimes|in:percent,flat,free_delivery',
            'value'      => 'sometimes|numeric|min:0',
            'min_order'  => 'nullable|numeric|min:0',
            'max_uses'   => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active'  => 'nullable|in:0,1,true,false',
        ]);

        $data = $request->all();

        if ($request->has('is_active')) {
            $data['is_active'] = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        $coupon->update($data);

        return response()->json(['message' => 'Coupon updated', 'coupon' => $coupon]);
    }

    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();
        return response()->json(['message' => 'Coupon deleted']);
    }

    public function generate()
    {
        $code = strtoupper(Str::random(8));
        return response()->json(['code' => $code]);
    }
}
