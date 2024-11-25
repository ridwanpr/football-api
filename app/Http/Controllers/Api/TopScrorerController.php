<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TopScrorerController extends Controller
{
    public function getTopScorer(Request $request, $leagueCode)
    {
        $season = $request->query('season');
        $matchday = $request->query('matchday');

        $queryParams = [];
        if ($season) {
            $queryParams['season'] = $season;
        }
        if ($matchday) {
            $queryParams['matchday'] = $matchday;
        }

        $cacheKey = 'top_scorer_' . $leagueCode .
            ($season ? '_season_' . $season : '') .
            ($matchday ? '_matchday_' . $matchday : '');

        $topScorer = Cache::get($cacheKey);

        if (!$topScorer) {
            $response = Http::withHeaders([
                'X-Auth-Token' => env('API_TOKEN'),
            ])->get(env('BASE_API_URL') . "/competitions/{$leagueCode}/scorers", $queryParams);

            $responseBody = json_decode($response->body());
            Cache::put($cacheKey, $responseBody, 30);
            $topScorer = $responseBody;
        }

        return response()->json([
            'matches' => $topScorer,
            'status' => 'success',
            'code' => 200
        ]);
    }
}
