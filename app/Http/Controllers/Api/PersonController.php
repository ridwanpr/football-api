<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PersonController extends Controller
{
    public function getPerson($personId)
    {
        $cacheKey = 'person_' . $personId;
        $person = Cache::get($cacheKey);

        if (!$person) {
            $response = Http::withHeaders([
                'X-Auth-Token' => env('API_TOKEN'),
            ])->get(env('BASE_API_URL') . "/persons/{$personId}");

            if ($response->failed()) {
                return response()->json([
                    'message' => 'Failed to fetch person from API.',
                    'status' => 'error',
                    'code' => 500,
                    'error' => $response->json()
                ]);
            }

            $person = $response->json();
            Cache::put($cacheKey, $person, 60);
        }

        return response()->json([
            'person' => $person,
            'status' => 'success',
            'code' => 200
        ]);
    }
}
