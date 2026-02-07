<?php

use Illuminate\Support\Facades\Route;
use App\Services\FootballService;

use App\Services\PredictionAgent;

Route::get('/analisis-agente', function () {
    try {
        $service = new App\Services\FootballService();
        $agent = new App\Services\PredictionAgent();

        // Obtenemos los datos del día
        $rawData = $service->getMatchesByDate(date('Ymd'));
        
        if (!isset($rawData['response'])) return response()->json(['status' => 'debug', 'message' => 'No hay response']);

        $allMatches = collect($rawData['response'])->flatten(1);

        // --- DEPURACIÓN AGRESIVA ---
        // Vamos a capturar TODO el objeto del primer partido para ver por qué 'status' sale vacío
        $primerPartido = $allMatches->first();

        $matches = $allMatches->filter(function($item) {
            if (!is_array($item)) return false;

            // Buscamos cualquier indicio de que el partido NO ha terminado
            // Algunas APIs usan 'status_id', 'period', o 'time.status'
            $status = $item['status']['type'] ?? $item['status'] ?? 'unknown';
            $reason = $item['status']['reason'] ?? '';

            // Si el marcador ya tiene goles y el status está vacío, 
            // es muy probable que sea un partido finalizado.
            $hasScore = isset($item['home']['score']) && $item['home']['score'] > 0;
            
            // Filtro temporal: Si no trae status claro, sospechamos de él
            return !in_array(strtoupper((string)$reason), ['FT', 'FINISHED', 'END']);
        })->values();

        return response()->json([
            'status' => 'success',
            'debug_raw' => $primerPartido, // ESTO NOS DIRÁ LA VERDAD
            'predicciones' => $agent->analyzeMatches($matches->toArray())
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'fatal_error',
            'message' => $e->getMessage()
        ], 200);
    }
});