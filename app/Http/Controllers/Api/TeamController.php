<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TeamController extends Controller
{
    public function getTeam($teamId)
    {
        $cacheKey = 'team_' . $teamId;
        $team = Cache::get($cacheKey);
        if (!$team) {
            $response = Http::withHeaders([
                'X-Auth-Token' => env('API_TOKEN'),
            ])->get(env('BASE_API_URL') . "/teams/{$teamId}");

            if ($response->failed()) {
                return response()->json([
                    'message' => 'Failed to fetch team from API.',
                    'status' => 'error',
                    'code' => 500,
                    'error' => $response->json()
                ]);
            }

            $team = $response->json();
            Cache::put($cacheKey, $team, now()->addMinutes(60));
        }

        return response()->json([
            'team' => $team,
            'status' => 'success',
            'code' => 200
        ]);
    }
}
