<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Show the form for editing location settings.
     */
    public function locationSettings()
    {
        $settingKeys = ['center_latitude', 'center_longitude', 'allowed_radius_meters'];
        $settings = Setting::whereIn('key', $settingKeys)
            ->pluck('value', 'key');

        return view('settings.location', compact('settings'));
    }

    /**
     * Update the location settings in storage.
     */
    public function updateLocationSettings(Request $request)
    {
        $request->validate([
            'center_latitude' => 'required|numeric|between:-90,90',
            'center_longitude' => 'required|numeric|between:-180,180',
            'allowed_radius_meters' => 'required|integer|min:1',
        ]);

        $settings = [
            'center_latitude' => $request->center_latitude,
            'center_longitude' => $request->center_longitude,
            'allowed_radius_meters' => $request->allowed_radius_meters,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan lokasi berhasil diperbarui.');
    }

    /**
     * Show the form for editing media settings.
     */
    public function mediaSettings()
    {
        $settingKeys = ['attendance_retention_days'];
        $settings = Setting::whereIn('key', $settingKeys)
            ->pluck('value', 'key');

        return view('settings.media', compact('settings'));
    }

    /**
     * Update the media settings in storage.
     */
    public function updateMediaSettings(Request $request)
    {
        $request->validate([
            'attendance_retention_days' => 'nullable|integer|min:1',
        ]);

        Setting::updateOrCreate(
            ['key' => 'attendance_retention_days'],
            ['value' => $request->attendance_retention_days]
        );

        return redirect()->back()->with('success', 'Pengaturan media berhasil diperbarui.');
    }
}
