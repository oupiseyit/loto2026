<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SettingController extends Controller
{
    #[OA\Get(
        path: '/settings',
        summary: 'Get printer settings for current user — all roles',
        security: [['bearerAuth' => []]],
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Settings',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    ref: '#/components/schemas/Setting'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        return $this->show();
    }

    public function show(): JsonResponse
    {
        $setting = Setting::firstOrCreate(
            ['user_id' => auth()->id()],
            ['bluetooth_enabled' => false, 'printer_size' => '58mm', 'logo_mode' => 'logo', 'commission_mode' => 'default', 'commission_value' => null]
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'bluetooth'  => $setting->bluetooth_enabled,
                'device'     => $setting->printer_size,
                'logo'       => $setting->logo_mode,
                'commission' => $setting->commission_mode,
            ],
            'message' => 'OK',
        ]);
    }

    #[OA\Post(
        path: '/settings',
        summary: 'Update printer settings — all roles',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['printer_size', 'logo_mode'],
                properties: [
                    new OA\Property(property: 'bluetooth_enabled', type: 'boolean', example: true),
                    new OA\Property(property: 'printer_size',      type: 'string',  enum: ['58mm', '80mm']),
                    new OA\Property(property: 'logo_mode',         type: 'string',  enum: ['logo', 'text']),
                ]
            )
        ),
        security: [['bearerAuth' => []]],
        tags: ['Settings'],
        responses: [
            new OA\Response(response: 200, description: 'Settings updated',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(response: 422, description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bluetooth_enabled' => ['boolean'],
            'printer_size'      => ['required', 'in:58mm,80mm'],
            'logo_mode'         => ['required', 'in:logo,text'],
        ]);

        Setting::updateOrCreate(['user_id' => auth()->id()], $validated);

        return response()->json(['success' => true, 'message' => 'Settings updated.']);
    }

    #[OA\Put(
        path: '/settings/commission',
        summary: 'Update commission settings — admin only',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['commission_mode'],
                properties: [
                    new OA\Property(property: 'commission_mode',  type: 'string', enum: ['default', 'custom']),
                    new OA\Property(property: 'commission_value', type: 'number', minimum: 0, maximum: 100, example: 5),
                ]
            )
        ),
        security: [['bearerAuth' => []]],
        tags: ['Settings'],
        responses: [
            new OA\Response(response: 200, description: 'Commission updated',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(response: 403, description: 'Admin only',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 422, description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function commission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'commission_mode'  => ['required', 'in:default,custom'],
            'commission_value' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        Setting::updateOrCreate(['user_id' => auth()->id()], $validated);

        return response()->json(['success' => true, 'message' => 'Commission updated.']);
    }
}
