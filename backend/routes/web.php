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
                
                if (!isset($item['home']) || !isset($item['status'])) return false;


                $statusType = strtolower($item['status']['type'] ?? '');
                $reason = strtoupper($item['status']['reason'] ?? '');

                
                $esTerminado = in_array($reason, ['FT', 'FINISHED', 'ENDED', 'AET', 'PEN', 'POSTP']) 
                               || $statusType === 'finished';

                return !$esTerminado;
            })
            ->map(function($match) {
                
                $match['time'] = $match['status']['reason'] ?? 'Live';
                return $match;
            })
            ->values() 
            ->toArray();
    }

    return response()->json([
        'total_activos' => count($matches),
        'predicciones' => $agent->analyzeMatches($matches)
    ]);
});