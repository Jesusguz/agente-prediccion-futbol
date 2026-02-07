<?php

use Illuminate\Support\Facades\Route;
use App\Services\FootballService;

use App\Services\PredictionAgent;

Route::get('/analisis-agente', function () {
    $service = new App\Services\FootballService();
    $agent = new App\Services\PredictionAgent();

    
    
    $rawData = $service->getMatchesByDate(date('Ymd'));
    
    
    $matches = [];
    if (isset($rawData['response'])) {
    
        $matches = collect($rawData['response'])->flatten(1)->filter(function($item) {
            return isset($item['home']);
        })->toArray();
    }

    return response()->json([
        'agente_name' => 'Lía Predictor V1',
        'status' => 'Analizando ligas internacionales',
        'fecha' => date('d-m-Y'),
        'total_partidos' => count($matches),
        'predicciones' => $agent->analyzeMatches($matches)
    ]);
});