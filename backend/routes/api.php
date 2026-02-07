<?php

use App\Services\FootballService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Services\PredictionAgent;

Route::get('/analisis-agente', function () {
    $service = new FootballService();
    $agent = new PredictionAgent();

    $rawData = $service->getMatchesByDate('20260206');
    
    $matches = $rawData['response']['matches'] ?? []; 

    return response()->json([
        'agente_name' => 'Lía Predictor V1',
        'status' => 'Conectado',
        'analisis' => $agent->analyzeMatches($matches)
    ]);
});