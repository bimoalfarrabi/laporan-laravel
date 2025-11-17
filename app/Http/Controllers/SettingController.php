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
        $shifts = ['reguler', 'normal_pagi', 'normal_malam'];
        $types = ['in', 'out'];
        $times = ['start', 'end'];
        $settingKeys = [];

        foreach ($shifts as $shift) {
            foreach ($types as $type) {
                foreach ($times as $time) {
                    $settingKeys[] = "attendance_{$shift}_{$type}_{$time}";
                }
            }
        }

        $settings = Setting::whereIn('key', $settingKeys)
            ->pluck('value', 'key');

        return view('settings.attendance', compact('settings', 'shifts', 'types', 'times'));
    }

    /**
     * Update the attendance time settings in storage.
     */
    public function updateAttendanceSettings(Request $request)
    {
        $shifts = ['reguler', 'normal_pagi', 'normal_malam'];
        $types = ['in', 'out'];
        $times = ['start', 'end'];
        $rules = [];

        foreach ($shifts as $shift) {
            foreach ($types as $type) {
                foreach ($times as $time) {
                    $key = "attendance_{$shift}_{$type}_{$time}";
                    $rules[$key] = 'required|date_format:H:i';
                }
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
