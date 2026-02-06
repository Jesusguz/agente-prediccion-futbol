<?php

use Illuminate\Support\Facades\Route;
use App\Services\FootballService;

use App\Services\PredictionAgent;

Route::get('/analisis-agente', function () {
    $service = new FootballService();
    $agent = new PredictionAgent();

    $rawData = $service->getMatchesByDate('20260206');
    
    // Navegamos por la estructura que vimos en tu Tinker:
    // response -> [0] (la fecha) -> [0] (la liga o bloque)
    $matches = data_get($rawData, 'response.0.0', []);

    // Si sigue saliendo 1 o vacío, intentamos aplanarlo para encontrar los partidos
    if (count($matches) < 2) {
        $matches = collect($rawData)->flatten(3)->whereNotNull('home')->toArray();
    }

    return response()->json([
        'agente_name' => 'Lía Predictor V1',
        'status' => 'Conectado',
        'total_partidos' => count($matches),
        'analisis' => $agent->analyzeMatches($matches)
    ]);
});