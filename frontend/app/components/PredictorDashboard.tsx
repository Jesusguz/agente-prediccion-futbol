'use client';
import React, { useState, useEffect } from 'react';

interface Prediccion {
  game: string;
  score: string;
  prediction: string;
  intensity: string;
  is_over_2_5: boolean;
  time?: string;
}

export default function PredictorDashboard() {
  const [data, setData] = useState<Prediccion[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchPredictions = async () => {
      try {
        const response = await fetch('https://agente-prediccion-futbol-production.up.railway.app/analisis-agente');
        const json = await response.json();
        setData(json.predicciones || []);
        setLoading(false);
      } catch (error) {
        console.error("Lía error:", error);
      }
    };

    fetchPredictions();
    const interval = setInterval(fetchPredictions, 60000);
    return () => clearInterval(interval);
  }, []);

  const recomendados = data.filter((p) => p.prediction.includes('Alta') || p.is_over_2_5);
  const otros = data.filter((p) => !recomendados.some(r => r.game === p.game));

  if (loading) return <div className="p-10 text-center text-green-400">Analizando mercados...</div>;

  return (
    <div className="space-y-6">
      <h2 className="text-xl font-bold text-yellow-500 uppercase tracking-widest">Top Recomendados</h2>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {recomendados.map((m, i) => (
          <div key={`${m.game}-${i}`} className="p-4 bg-slate-800 border-l-4 border-yellow-500 rounded-r-lg">
            <div className="flex justify-between text-[10px] text-slate-400">
              <span>{m.time || 'LIVE'}</span>
              <span>{m.intensity}</span>
            </div>
            <p className="font-bold text-lg">{m.game}</p>
            <p className="text-2xl font-black text-blue-400">{m.score}</p>
            <p className="text-sm italic text-green-400 mt-2">{m.prediction}</p>
          </div>
        ))}
      </div>
    </div>
  );
}