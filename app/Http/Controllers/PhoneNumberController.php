<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PhoneNumberController extends Controller
{
    public function index()
    {
        $url = config('app.phone_api_url');
        $apiKey = config('app.phone_api_key');

        if (!$url || !$apiKey) {
            abort(500, 'API URL or API Key is not configured.');
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
        ])->get($url);

        if ($response->failed()) {
            abort(500, 'Failed to fetch data from API.');
        }

        $phoneNumbers = $response->json();

        return view('phone-numbers.index', compact('phoneNumbers'));
    }
}
