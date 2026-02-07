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
                'message' => 'API externa no respondió con la estructura esperada.',
                'raw_preview' => substr(json_encode($rawData), 0, 150)
            ], 200);
        }

        $matches = collect($rawData['response'])
            ->flatten(1)
            ->filter(function($item) {
                // 1. Validación de seguridad básica
                if (!is_array($item) || !isset($item['home']) || !isset($item['status'])) {
                    return false;
                }
                
                // 2. CORRECCIÓN DEL ERROR: Validamos que 'reason' sea string antes de transformar
                $rawReason = $item['status']['reason'] ?? '';
                $reason = is_string($rawReason) ? strtoupper($rawReason) : '';
                $statusType = strtolower($item['status']['type'] ?? '');

                // 3. Filtro estricto de estados terminados
                $terminados = ['FT', 'FINISHED', 'ENDED', 'AET', 'PEN', 'FULL TIME'];
                return !in_array($reason, $terminados) && $statusType !== 'finished';
            })
            ->map(function($match) {
                // Aseguramos que el tiempo sea un string legible para el Frontend
                $match['time'] = is_string($match['status']['reason'] ?? null) 
                    ? $match['status']['reason'] 
                    : 'Live';
                return $match;
            })
            ->values()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'debug_info' => array_slice($matches, 0, 5), // Muestra los primeros 5 para depurar
            'total_activos' => count($matches),
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