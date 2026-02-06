<?php

namespace App\Services;

class PredictionAgent
{
    public function analyzeMatches(array $matches)
{
    // Limpiamos el array por si vienen nulos o estructuras raras
    return collect($matches)->map(function ($match) {
        // Accedemos de forma segura usando el operador ?? o data_get
        $homeName = $match['home']['name'] ?? 'Equipo Local';
        $awayName = $match['away']['name'] ?? 'Equipo Visitante';
        $homeScore = $match['home']['score'] ?? 0;
        $awayScore = $match['away']['score'] ?? 0;
        
        $totalGoals = $homeScore + $awayScore;
        $hasRedCards = ($match['home']['redCards'] ?? 0) > 0 || ($match['away']['redCards'] ?? 0) > 0;

        return [
            'game' => "{$homeName} vs {$awayName}",
            'score' => "{$homeScore} - {$awayScore}",
            'goals_count' => $totalGoals,
            'is_over_2_5' => $totalGoals > 2.5,
            'intensity' => $hasRedCards ? 'Alta (Rojas)' : 'Normal',
            'prediction' => $this->calculateConfidence($totalGoals, $hasRedCards)
        ];
    });
}

    private function calculateConfidence($goals, $redCards)
    {
    
        if ($goals > 3) return 'Alta Probabilidad de Goles';
        if ($redCards) return 'Inestable (Favorable a Goles)';
        return 'Moderado';
    }
}