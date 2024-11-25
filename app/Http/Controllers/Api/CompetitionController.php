<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CompetitionController extends Controller
{
    const CACHE_TTL = 60 * 60 * 24 * 7; // 7 days
    const CACHE_KEY = 'competitions';

    public function getCompetition(Request $request)
    {
        $areas = $request->query('areas');
        $cacheKey = 'competitions' . ($areas ? '_areas_' . $areas : '');
        $competitions = Cache::get($cacheKey);

        if (!$competitions) {
            try {
                $queryParams = $areas ? ['areas' => $areas] : [];
                $response = Http::withHeaders([
                    'X-Auth-Token' => env('API_TOKEN'),
                ])->get(env('BASE_API_URL') . '/competitions/', $queryParams);

                $competitions = json_decode($response->body());
                if (isset($competitions->competitions) && is_array($competitions->competitions)) {
                    foreach ($competitions->competitions as $competition) {
                        DB::table('competitions')->updateOrInsert(
                            ['id' => $competition->id],
                            [
                                'name' => $competition->name ?? null,
                                'code' => $competition->code ?? null,
                                'type' => $competition->type ?? null,
                                'emblem' => $competition->emblem ?? null,
                                'plan' => $competition->plan ?? null,
                                'area_name' => $competition->area->name ?? null,
                                'area_code' => $competition->area->code ?? null,
                                'current_season' => json_encode($competition->currentSeason),
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );
                    }

                    Cache::put($cacheKey, $competitions->competitions, self::CACHE_TTL);
                } else {
                    $competitions = DB::table('competitions')->get();
                    Cache::put($cacheKey, $competitions, self::CACHE_TTL);
                }
            } catch (\Exception $e) {
                $competitions = DB::table('competitions')->get();
                Cache::put($cacheKey, $competitions, self::CACHE_TTL);
            }
        }

        return response()->json([
            'competitions' => $competitions,
            'status' => 'success',
            'code' => 200
        ]);
    }

    public function getCompetitionTeams(Request $request, $id)
    {
        $season = $request->query('season');
        $cacheKey = 'competition_teams_' . $id . ($season ? '_season_' . $season : '');

        $teams = Cache::get($cacheKey);

        if (!$teams) {
            try {
                $queryParams = $season ? ['season' => $season] : [];
                $response = Http::withHeaders([
                    'X-Auth-Token' => env('API_TOKEN'),
                ])->get(env('BASE_API_URL') . "/competitions/{$id}/teams", $queryParams);

                if ($response->failed()) {
                    return response()->json([
                        'message' => 'Failed to fetch teams from API.',
                        'status' => 'error',
                        'code' => 500
                    ], 500);
                }

                $responseBody = $response->json();

                if (empty($responseBody['teams'])) {
                    return response()->json([
                        'message' => 'No teams found for this competition.',
                        'status' => 'error',
                        'code' => 404
                    ], 404);
                }

                foreach ($responseBody['teams'] as $team) {
                    DB::table('teams')->updateOrInsert(
                        ['id' => $team['id']],
                        [
                            'name' => $team['name'],
                            'code' => $team['code'] ?? null,
                            'crest' => $team['crest'] ?? null,
                            'address' => $team['address'] ?? null,
                            'website' => $team['website'] ?? null,
                            'founded' => $team['founded'] ?? null,
                            'venue' => $team['venue'] ?? null,
                            'competition_id' => $id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }

                Cache::put($cacheKey, $responseBody['teams'], self::CACHE_TTL);

                return response()->json([
                    'teams' => $responseBody['teams'],
                    'status' => 'success',
                    'code' => 200
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'An error occurred while processing your request.',
                    'status' => 'error',
                    'code' => 500,
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'teams' => $teams,
            'status' => 'success',
            'code' => 200
        ], 200);
    }
}
