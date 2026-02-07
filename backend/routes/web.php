<?php

use Illuminate\Support\Facades\Route;
use App\Services\FootballService;

use App\Services\PredictionAgent;

Route::get('/analisis-agente', function () {
    try {
        $service = new App\Services\FootballService();
        $rawData = $service->getMatchesByDate(date('Ymd'));
        
        if (!isset($rawData['response'])) {
            return response()->json(['status' => 'debug', 'message' => 'Sin respuesta de API']);
        }

        // Aplanamos y tomamos el primer partido para inspeccionarlo
        $allMatches = collect($rawData['response'])->flatten(1);
        $sampleMatch = $allMatches->first();

        return response()->json([
            'status' => 'debug_mode_active',
            'message' => 'Inspección de datos crudos',
            // Enviamos el partido completo para ver sus llaves reales
            'debug_info' => [
                'full_structure' => $sampleMatch,
                'status_keys' => isset($sampleMatch['status']) ? array_keys((array)$sampleMatch['status']) : 'Sin status'
            ]
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'fatal_error',
            'message' => $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine()
        ], 200);
    }
});