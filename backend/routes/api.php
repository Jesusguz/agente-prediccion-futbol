<?php

use App\Services\FootballService;
use App\Services\PredictionAgent;
use Illuminate\Support\Facades\Route;

Route::get('/analisis-agente', function () {
    $service = new FootballService();
    $agent = new PredictionAgent();


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