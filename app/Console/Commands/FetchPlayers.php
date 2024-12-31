<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FetchPlayers extends Command
{
    protected $signature = 'fetch:players';
    protected $description = 'Fetch and store player data for all teams.';

    public function handle()
    {
        $chunkSize = 20;
        $delayBetweenChunks = 5;
        $delayBetweenRequests = 1;

        DB::table('teams')->select('id')->orderBy('id')->chunk($chunkSize, function ($chunkedTeams) use ($delayBetweenRequests) {
            foreach ($chunkedTeams as $team) {
                $cacheKey = "team_{$team->id}";

                $teamData = Cache::remember($cacheKey, 60, function () use ($team) {
                    $response = Http::withHeaders([
                        'X-Auth-Token' => env('API_TOKEN'),
                    ])->get(env('BASE_API_URL') . "/teams/{$team->id}");

                    if ($response->failed()) {
                        $this->error("Failed to fetch data for team ID: {$team->id}");
                        return null;
                    }

                    return $response->json();
                });

                if ($teamData && isset($teamData['squad'])) {
                    foreach ($teamData['squad'] as $player) {
                        DB::table('players')->updateOrInsert(
                            ['id' => $player['id']],
                            [
                                'team_id' => $team->id,
                                'name' => $player['name'],
                                'position' => $player['position'] ?? null,
                                'date_of_birth' => $player['dateOfBirth'] ?? null,
                                'nationality' => $player['nationality'] ?? null,
                                'updated_at' => now(),
                            ]
                        );
                    }
                    $this->info("Processed team ID: {$team->id}");
                }

                sleep($delayBetweenRequests);
            }
        });

        sleep($delayBetweenChunks);

        $this->info("Player data fetching completed.");
    }
}
