<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PhoneNumberController extends Controller
{
    public function index(Request $request)
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

        $allPhoneNumbers = new Collection($response->json());
        $search = $request->input('search');

        $filteredPhoneNumbers = $allPhoneNumbers->when($search, function ($collection, $search) {
            return $collection->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['nama']), strtolower($search)) ||
                       str_contains(strtolower($item['ext']), strtolower($search));
            });
        });

        $page = $request->input('page', 1);
        $perPage = 15;
        $paginatedPhoneNumbers = new LengthAwarePaginator(
            $filteredPhoneNumbers->forPage($page, $perPage),
            $filteredPhoneNumbers->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('phone-numbers.index', [
            'phoneNumbers' => $paginatedPhoneNumbers,
            'search' => $search,
        ]);
    }
}
