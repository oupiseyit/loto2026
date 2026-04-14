<?php

// Screen: setting | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/v1/settings
     */
    public function show(): JsonResponse
    {
        $setting = Setting::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'bluetooth_enabled' => false,
                'printer_size'      => '58mm',
                'logo_mode'         => 'logo',
                'commission_mode'   => 'default',
                'commission_value'  => null,
            ]
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'bluetooth'   => $setting->bluetooth_enabled,
                'device'      => $setting->printer_size,
                'logo'        => $setting->logo_mode,
                'commission'  => $setting->commission_mode,
            ],
            'message' => 'OK',
        ]);
    }

    /**
     * PUT /api/v1/settings
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'bluetooth_enabled' => ['boolean'],
            'printer_size'      => ['required', 'in:58mm,80mm'],
            'logo_mode'         => ['required', 'in:logo,text'],
        ]);

        Setting::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Settings updated.',
        ]);
    }

    /**
     * PUT /api/v1/settings/commission — admin only
     */
    public function commission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'commission_mode'  => ['required', 'in:default,custom'],
            'commission_value' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        Setting::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Commission updated.',
        ]);
    }
}
