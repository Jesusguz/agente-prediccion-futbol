<?php

use App\Services\FootballService;
use App\Services\PredictionAgent;
use Illuminate\Support\Facades\Route;

Route::get('/analisis-agente', function () {
    try {
        $service = new App\Services\FootballService();
        $agent = new App\Services\PredictionAgent();

        // Nota: Si date('Ymd') te sigue dando solo partidos pasados, 
        // revisa si el servicio tiene un método getLiveMatches()
        $rawData = $service->getMatchesByDate(date('Ymd'));
        
        if (!isset($rawData['response'])) return response()->json(['status' => 'error']);

        $matches = collect($rawData['response'])
            ->flatten(1)
            ->filter(function($item) {
                // 1. Verificación de seguridad
                if (!isset($item['status'])) return false;

                // 2. FILTRO DEFINITIVO: Usamos la propiedad 'finished' que vimos en el JSON
                $isFinished = $item['status']['finished'] ?? true;
                $isCancelled = $item['status']['cancelled'] ?? false;
                $started = $item['status']['started'] ?? false;

                // Solo queremos partidos que hayan empezado y NO hayan terminado
                return $started && !$isFinished && !$isCancelled;
            })
            ->map(function($match) {
                // Extraemos el texto del tiempo correctamente del objeto 'reason'
                $match['time'] = $match['status']['reason']['short'] ?? 'Live';
                return $match;
            })
            ->values()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'total_en_vivo' => count($matches),
            'predicciones' => $agent->analyzeMatches($matches)
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'fatal_error',
            'message' => $e->getMessage(),
            'debug_info' => 'Error en el filtro de estados'
        ], 200);
    }
});