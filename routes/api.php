<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\TopScrorerController;
use App\Http\Controllers\Api\CompetitionController;

Route::get('match', [MatchController::class, 'getMatches']);

Route::get('areas', [AreaController::class, 'getAreas']);

Route::get('competitions', [CompetitionController::class, 'getCompetition']);
Route::get('competitions/{id}/teams', [CompetitionController::class, 'getCompetitionTeams']);

Route::get('topscorer/{leagueCode}', [TopScrorerController::class, 'getTopScorer']);
