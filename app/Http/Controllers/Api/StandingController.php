<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class StandingController extends Controller
{
    public function getStandings(Request $request, $leagueCode)
    {
        $season = $request->query('season');
        $matchday = $request->query('matchday');
        $date = $request->query('date');

        $queryParams = [];
        if ($season) {
            $queryParams['season'] = $season;
        }
        if ($matchday) {
            $queryParams['matchday'] = $matchday;
        }
        if ($date) {
            $queryParams['date'] = $date;
        }

        $cacheKey = 'standings_' . $leagueCode .
            ($season ? '_season_' . $season : '') .
            ($matchday ? '_matchday_' . $matchday : '') .
            ($date ? '_date_' . $date : '');

        $standings = Cache::get($cacheKey);

        if (!$standings) {
            $response = Http::withHeaders([
                'X-Auth-Token' => env('API_TOKEN'),
            ])->get(env('BASE_API_URL') . "/competitions/{$leagueCode}/standings", $queryParams);

            $responseBody = json_decode($response->body(), true);
            Cache::put($cacheKey, $responseBody['standings'], 30);
            $standings = $responseBody['standings'];
        }

        return response()->json([
            'standings' => $standings,
            'status' => 'success',
            'code' => 200
        ]);
    }
}
