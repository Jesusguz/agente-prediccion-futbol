<?php

use Illuminate\Support\Facades\Route;
use App\Services\FootballService;

use App\Services\PredictionAgent;

Route::get('/analisis-agente', function () {
    try {
        $service = new App\Services\FootballService();
        $agent = new App\Services\PredictionAgent();

        $rawData = $service->getMatchesByDate(date('Ymd'));
        
        if (!isset($rawData['response'])) {
            return response()->json([
                'status' => 'debug',
                'message' => 'API externa no respondió con datos',
                'raw' => substr(json_encode($rawData), 0, 200)
            ], 200); 
        }

        $matches = collect($rawData['response'])
            ->flatten(1)
            ->filter(function($item) {
                if (!isset($item['home']) || !isset($item['status'])) return false;
                
                $reason = strtoupper($item['status']['reason'] ?? '');
                return !in_array($reason, ['FT', 'FINISHED', 'ENDED', 'AET', 'PEN']);
            })
            ->values()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'predicciones' => $agent->analyzeMatches($matches)
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'fatal_error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 200); 
    }
});