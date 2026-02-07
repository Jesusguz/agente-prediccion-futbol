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
                'message' => 'La API externa no respondió con la estructura esperada.',
                'raw_preview' => substr(json_encode($rawData), 0, 150)
            ], 200);
        }

        $matches = collect($rawData['response'])
            ->flatten(1)
            ->filter(function($item) {
                // Validación de seguridad inicial
                if (!is_array($item) || !isset($item['home']) || !isset($item['status'])) {
                    return false;
                }
                
                // CORRECCIÓN: Validamos que sea string antes de usar strtoupper
                $rawReason = $item['status']['reason'] ?? '';
                $reason = is_string($rawReason) ? strtoupper($rawReason) : '';

                // Filtro de estados terminados
                $terminados = ['FT', 'FINISHED', 'ENDED', 'AET', 'PEN', 'POSTP'];
                return !in_array($reason, $terminados);
            })
            ->values()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'total_procesados' => count($matches),
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