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
}
