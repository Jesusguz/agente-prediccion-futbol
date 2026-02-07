<?php

use App\Services\FootballService;
use App\Services\PredictionAgent;
use Illuminate\Support\Facades\Route;

Route::get('/analisis-agente', function () {
    $service = new App\Services\FootballService();
    $agent = new App\Services\PredictionAgent();

    $rawData = $service->getMatchesByDate(date('Ymd'));
    
    $matches = [];
    if (isset($rawData['response'])) {
        $matches = collect($rawData['response'])
            ->flatten(1)
            ->filter(function($item) {
                $status = $item['status']['reason'] ?? '';
                return !in_array($status, ['FT', 'Finished', 'Ended', 'AET', 'Pen']);
            })
            ->map(function($match) {
                $match['time'] = $match['status']['reason'] ?? 'Live';
                return $match;
            })
            ->toArray();
    }

    return response()->json([
        'agente_name' => 'Lía Predictor V1',
        'total_partidos' => count($matches),
        'predicciones' => $agent->analyzeMatches($matches)
    ]);
});