<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->mapWithKeys(fn($s) => [$s->key => $s->value]);
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name'              => 'sometimes|string|max:100',
            'store_email'             => 'sometimes|email',
            'store_phone'             => 'sometimes|string|max:20',
            'store_address'           => 'sometimes|string',
            'currency'                => 'sometimes|string|max:5',
            'tax_rate'                => 'sometimes|numeric|min:0|max:100',
            'free_delivery_threshold' => 'sometimes|numeric|min:0',
            'base_delivery_fee'       => 'sometimes|numeric|min:0',
            'low_stock_threshold'     => 'sometimes|integer|min:1',
            'maintenance_mode'        => 'sometimes|boolean',
            'stripe_enabled'          => 'sometimes|boolean',
            'paypal_enabled'          => 'sometimes|boolean',
            'cod_enabled'             => 'sometimes|boolean',
            'meta_title'              => 'sometimes|string|max:200',
            'meta_description'        => 'sometimes|string|max:500',
        ]);

        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return response()->json(['message' => 'Settings saved successfully']);
    }
}
