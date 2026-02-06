<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FootballService
{
    protected $key;
    protected $host;
    protected $url;

    public function __construct()
    {
        $this->key = env('FOOTBALL_API_KEY');
        $this->host = env('FOOTBALL_API_HOST');
        $this->url = env('FOOTBALL_API_URL');
    }

    public function searchPlayers($name)
    {
        $response = Http::withHeaders([
            'x-rapidapi-host' => $this->host,
            'x-rapidapi-key' => $this->key,
        ])->get("{$this->url}/football-players-search", [
            'search' => $name
        ]);

        return $response->json();
    }


    public function getMatchesByLeague($leagueId = 47)
    {
        $response = Http::withHeaders([
            'x-rapidapi-host' => $this->host,
            'x-rapidapi-key' => $this->key,
        ])->get("{$this->url}/football-get-matches-by-league", [
            'leagueid' => $leagueId
        ]);

        return $response->json();
    }
    public function getUpcomingMatches($leagueId = 47) // 47 es Premier League en esta API
    {
    $response = Http::withHeaders([
        'x-rapidapi-host' => $this->host,
        'x-rapidapi-key' => $this->key,
    ])->get("{$this->url}/football-get-matches-by-league", [
        'leagueid' => $leagueId
    ]);
    return $response->json()['response']['matches'] ?? [];
    }

    public function getLiveMatches()
    {
    $response = Http::withHeaders([
        'x-rapidapi-host' => $this->host,
        'x-rapidapi-key' => $this->key,
    ])->get("{$this->url}/football-get-all-livescores"); // Probamos con partidos en vivo

    return $response->json();
    }

    public function getMatchesByDate($date = null)
{
    $date = $date ?? date('Ymd'); // Formato AAAAMMDD que suele pedir esta API

    $response = Http::withHeaders([
        'x-rapidapi-host' => $this->host,
        'x-rapidapi-key' => $this->key,
    ])->get("{$this->url}/football-get-matches-by-date", [
        'date' => $date
    ]);

    return $response->json();
}
}