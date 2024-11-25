<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AreaController extends Controller
{
    const CACHE_TTL = 60 * 60 * 24 * 7;

    public function getAreas()
    {
        $areas = Cache::get('areas');

        if (!$areas) {
            try {
                $response = Http::withHeaders([
                    'X-Auth-Token' => env('API_TOKEN'),
                ])->get(env('BASE_API_URL') . '/areas');

                $responseBody = json_decode($response->body(), true);

                if (isset($responseBody['areas']) && is_array($responseBody['areas'])) {
                    $areas = $responseBody['areas'];

                    foreach ($areas as $area) {
                        DB::table('areas')->updateOrInsert(
                            ['countryCode' => $area['countryCode']],
                            [
                                'name' => $area['name'],
                                'flag' => $area['flag'],
                                'parentAreaId' => $area['parentAreaId'] ?? null,
                                'parentArea' => $area['parentArea'] ?? null,
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );
                    }

                    Cache::put('areas', $areas, self::CACHE_TTL);
                } else {
                    $areas = DB::table('areas')->get()->toArray();
                    Cache::put('areas', $areas, self::CACHE_TTL);
                }
            } catch (\Exception $e) {
                $areas = DB::table('areas')->get()->toArray();
                Cache::put('areas', $areas, self::CACHE_TTL);
            }
        }

        return response()->json([
            'areas' => $areas,
            'status' => 'success',
            'code' => 200
        ]);
    }
}
