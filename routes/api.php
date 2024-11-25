<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\StandingController;
use App\Http\Controllers\Api\TopScrorerController;
use App\Http\Controllers\Api\CompetitionController;

Route::get('matches/{leagueCode}', [MatchController::class, 'getMatches']);
Route::get('match/{matchId}', [MatchController::class, 'getMatchDetails']);

Route::get('areas', [AreaController::class, 'getAreas']);

Route::get('competitions', [CompetitionController::class, 'getCompetition']);
Route::get('competitions/{id}/teams', [CompetitionController::class, 'getCompetitionTeams']);

Route::get('topscorer/{leagueCode}', [TopScrorerController::class, 'getTopScorer']);

Route::get('standings/{leagueCode}', [StandingController::class, 'getStandings']);
