<?php

use Illuminate\Support\Facades\Route;
use App\Services\FootballService;

use App\Services\PredictionAgent;

Route::get('/analisis-agente', function () {
    try {
        $service = new App\Services\FootballService();
        $agent = new App\Services\PredictionAgent();
        $rawData = $service->getMatchesByDate(date('Ymd'));
        
        if (!isset($rawData['response'])) return response()->json(['status' => 'error', 'message' => 'No response']);

        $matches = collect($rawData['response'])->flatten(1);

        // --- BLOQUE DE DEPURACIÓN PARA TI ---
        // Esto nos dirá qué estados existen en los partidos actuales
        $debugEstados = $matches->map(function($m) {
            return [
                'name' => ($m['home']['name'] ?? 'Unknown') . ' vs ' . ($m['away']['name'] ?? 'Unknown'),
                'type' => $m['status']['type'] ?? 'N/A',
                'reason' => $m['status']['reason'] ?? 'N/A'
            ];
        })->take(10)->toArray(); 

        $filtered = $matches->filter(function($item) {
            if (!isset($item['status'])) return false;
            
            // Filtro más agresivo:
            $type = strtolower($item['status']['type'] ?? '');
            $reason = strtoupper($item['status']['reason'] ?? '');

            // Si el tipo es 'finished' o la razón es 'FT', lo quitamos
            $esTerminado = in_array($reason, ['FT', 'FINISHED', 'ENDED', 'AET', 'PEN', 'FULL TIME']) 
                           || $type === 'finished' 
                           || $type === 'closed';

            return !$esTerminado;
        })->values();

        return response()->json([
            'status' => 'success',
            'debug_info' => $debugEstados, // Veremos esto en el monitor rojo
            'total_partidos_api' => $matches->count(),
            'total_tras_filtro' => $filtered->count(),
            'predicciones' => $agent->analyzeMatches($filtered->toArray())
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'fatal_error',
            'message' => $e->getMessage(),
            'debug_raw' => "Revisa si 'status' es un string o array"
        ], 200);
    }
});