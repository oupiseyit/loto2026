<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
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

        $view = auth()->user()->isAdmin() ? 'admin.setting' : 'setting';

        return view($view, [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'bluetooth_enabled' => ['boolean'],
            'printer_size'      => ['required', 'in:58mm,80mm'],
            'logo_mode'         => ['required', 'in:logo,text'],
        ];

        // Commission — admin only
        if ($user->role === 'admin') {
            $rules['commission_mode']  = ['required', 'in:default,custom'];
            $rules['commission_value'] = ['nullable', 'numeric', 'min:0', 'max:100'];
        }

        $validated = $request->validate($rules);

        Setting::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return back()->with('success', 'Settings saved.');
    }
}
