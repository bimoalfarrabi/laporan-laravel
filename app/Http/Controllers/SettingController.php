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
     * Show the form for editing attendance time settings.
     */
    public function attendanceSettings()
    {
        $types = ['in', 'out'];
        $times = ['start', 'end'];
        $settingKeys = [];

        foreach ($types as $type) {
            foreach ($times as $time) {
                $settingKeys[] = "attendance_{$type}_{$time}";
            }
        }

        $settings = Setting::whereIn('key', $settingKeys)
            ->pluck('value', 'key');

        return view('settings.attendance', compact('settings', 'types', 'times'));
    }

    /**
     * Update the attendance time settings in storage.
     */
    public function updateAttendanceSettings(Request $request)
    {
        $types = ['in', 'out'];
        $times = ['start', 'end'];
        $rules = [];

        foreach ($types as $type) {
            foreach ($times as $time) {
                $key = "attendance_{$type}_{$time}";
                $rules[$key] = 'required|date_format:H:i';
            }
        }

        $request->validate($rules);

        foreach ($rules as $key => $rule) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key)]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan waktu absensi berhasil diperbarui.');
    }
}
