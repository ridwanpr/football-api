<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MatchController extends Controller
{
    public function getMatches(Request $request, $leagueCode)
    {
        $season = $request->query('season');
        $matchday = $request->query('matchday');
        $status = $request->query('status');
        $dateFrom = $request->query('dateFrom');
        $dateTo = $request->query('dateTo');
        $stage = $request->query('stage');
        $group = $request->query('group');

        $queryParams = [];
        if ($season) {
            $queryParams['season'] = $season;
        }
        if ($matchday) {
            $queryParams['matchday'] = $matchday;
        }
        if ($status) {
            $queryParams['status'] = $status;
        }
        if ($dateFrom) {
            $queryParams['dateFrom'] = $dateFrom;
        }
        if ($dateTo) {
            $queryParams['dateTo'] = $dateTo;
        }
        if ($stage) {
            $queryParams['stage'] = $stage;
        }
        if ($group) {
            $queryParams['group'] = $group;
        }

        $cacheKey = 'matches_' . $leagueCode .
            ($season ? '_season_' . $season : '') .
            ($matchday ? '_matchday_' . $matchday : '') .
            ($status ? '_status_' . $status : '') .
            ($dateFrom ? '_dateFrom_' . $dateFrom : '') .
            ($dateTo ? '_dateTo_' . $dateTo : '') .
            ($stage ? '_stage_' . $stage : '') .
            ($group ? '_group_' . $group : '');

        $matches = Cache::get($cacheKey);

        if (!$matches) {
            $response = Http::withHeaders([
                'X-Auth-Token' => env('API_TOKEN'),
            ])->get(env('BASE_API_URL') . "/competitions/{$leagueCode}/matches", $queryParams);

            $responseBody = json_decode($response->body(), true);

            if (empty($responseBody['matches'])) {
                return response()->json([
                    'message' => 'No matches found.',
                    'status' => 'error',
                    'code' => 404
                ], 404);
            }

            Cache::put($cacheKey, $responseBody['matches'], now()->addMinutes(10));

            $matches = $responseBody['matches'];
        }

        return response()->json([
            'matches' => $matches,
            'status' => 'success',
            'code' => 200
        ]);
    }

    public function getMatchDetails(Request $request, $matchId)
    {
        $date = $request->query('date');
        $dateFrom = $request->query('dateFrom');
        $dateTo = $request->query('dateTo');
        $status = $request->query('status');

        $queryParams = [];
        if ($date) {
            $queryParams['date'] = $date;
        }
        if ($dateFrom) {
            $queryParams['dateFrom'] = $dateFrom;
        }
        if ($dateTo) {
            $queryParams['dateTo'] = $dateTo;
        }
        if ($status) {
            $queryParams['status'] = $status;
        }

        $cacheKey = 'match_' . $matchId .
            ($date ? '_date_' . $date : '') .
            ($dateFrom ? '_dateFrom_' . $dateFrom : '') .
            ($dateTo ? '_dateTo_' . $dateTo : '') .
            ($status ? '_status_' . $status : '');

        $match = Cache::get($cacheKey);

        if (!$match) {
            $response = Http::withHeaders([
                'X-Auth-Token' => env('API_TOKEN'),
            ])->get(env('BASE_API_URL') . "/matches/{$matchId}", $queryParams);

            $responseBody = json_decode($response->body(), true);
            Cache::put($cacheKey, $responseBody, now()->addMinutes(5));
            $match = $responseBody;
        }

        return response()->json([
            'match' => $match,
            'status' => 'success',
            'code' => 200
        ]);
    }
}
