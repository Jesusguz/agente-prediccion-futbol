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
        $matches = collect($rawData['response'])
            ->flatten(1)
            ->filter(function($item) {
               
                if (!isset($item['home'])) return false;
                
               
                $status = $item['status']['reason'] ?? '';
                return !in_array($status, ['FT', 'Finished', 'Ended']);
            })
            ->map(function($match) {
                
                $match['current_time'] = $match['status']['reason'] ?? 'Live';
                return $match;
            })
            ->toArray();
    }

    return response()->json([
        'agente_name' => 'Lía Predictor V1',
        'status' => 'Escaneo en Tiempo Real',
        'fecha' => date('d-m-Y'),
        'total_partidos' => count($matches),
        'predicciones' => $agent->analyzeMatches($matches)
    ]);
});