<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PlayerController extends Controller
{
    public function getPlayers(Request $request)
    {
        $playerName = $request->playerName;
        $player = DB::table('players')
            ->where('name', 'like', "%{$playerName}%")
            ->limit(20)->get();

        return response()->json([
            'player' => $player,
            'status' => 'success',
            'code' => 200
        ]);
    }

    public function getPlayerDetail($playerId)
    {
        $cacheKey = "player_{$playerId}";
        $player = Cache::remember($cacheKey, now()->addDays(3), function () use ($playerId) {
            $response = Http::withHeaders([
                'X-Auth-Token' => env('API_TOKEN'),
            ])->get(env('BASE_API_URL') . "/persons/{$playerId}");

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        });

        return response()->json([
            'player' => $player,
            'status' => 'success',
            'code' => 200
        ]);
    }
}
