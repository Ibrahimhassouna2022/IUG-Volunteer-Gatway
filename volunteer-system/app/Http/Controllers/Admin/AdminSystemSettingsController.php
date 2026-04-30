<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class AdminSystemSettingsController extends Controller
{
    /**
     */
    public function index()
    {
        $settings = SystemSetting::all()->groupBy('group');
        return response()->json($settings);
    }

    /**
     */
    public function updateBulk(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:system_settings,key',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($request->settings as $item) {
            SystemSetting::where('key', $item['key'])->update(['value' => $item['value']]);
        }

        return response()->json(['message' => 'تم تحديث الإعدادات بنجاح']);
    }

    /**
     */
    public function show(string $key)
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();
        return response()->json($setting);
    }

    /**
     */
    public function update(Request $request, SystemSetting $systemSetting)
    {
        $request->validate([
            'value' => 'nullable',
            'description' => 'sometimes|string|nullable',
        ]);

        $systemSetting->update($request->only(['value', 'description']));

        return response()->json([
            'message' => 'تم تحديث الإعداد بنجاح',
            'setting' => $systemSetting
        ]);
    }
}
