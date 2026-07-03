<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = Settings::current();
        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'default_supervision_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'company_name'            => ['required', 'string', 'max:255'],
            'company_tagline'         => ['nullable', 'string', 'max:255'],
            'company_phone'           => ['nullable', 'string', 'max:100'],
            'company_registration'    => ['nullable', 'string', 'max:100'],
            'whatsapp_country_code'   => ['required', 'string', 'max:5'],
        ]);

        Settings::current()->update($data);

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
