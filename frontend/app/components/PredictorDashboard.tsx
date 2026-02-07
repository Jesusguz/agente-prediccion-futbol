'use client';
import { useState, useEffect } from 'react';

export default function PredictorDashboard() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchPredictions = async () => {
    try {
      //const response = await fetch('https://agente-prediccion-futbol-production.up.railway.app/analisis-agente');
      const response = await fetch('https://agente-prediccion-futbol-production.up.railway.app/analisis-agente');

      const json = await response.json();
      setData(json.predicciones || []);
      setLoading(false);
    } catch (error) {
      console.error("Error:", error);
    }
  };

  useEffect(() => {
    fetchPredictions();
    const interval = setInterval(fetchPredictions, 60000); 
    return () => clearInterval(interval);
  }, []);

  const recomendados = data.filter((p: any) => p.prediction.includes('Alta') || p.is_over_2_5);
  const otros = data.filter((p: any) => !recomendados.includes(p));

  if (loading) return <div className="p-10 text-center text-green-400 animate-pulse">Lía está analizando los mercados...</div>;

  return (
    <div className="space-y-10">
      <section>
        <h2 className="text-2xl font-bold text-yellow-400 mb-4 flex items-center gap-2">
          ⭐ TOP RECOMENDADOS POR LÍA
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {recomendados.map((match: any, i: number) => (
            <div key={i} className="bg-slate-800 border-2 border-yellow-500/50 p-5 rounded-2xl shadow-[0_0_20px_rgba(234,179,8,0.1)]">
              <div className="flex justify-between items-center mb-2">
                <span className="text-red-500 font-black animate-pulse">{match.time || 'LIVE'}</span>
                <span className="text-xs font-bold text-yellow-500 uppercase">Alta Confianza</span>
              </div>
              <h3 className="text-xl font-bold">{match.game}</h3>
              <p className="text-4xl font-black text-white my-2">{match.score}</p>
              <div className="mt-4 p-3 bg-yellow-500/10 rounded-lg">
                <p className="text-yellow-400 font-bold italic">🎯 Lía: {match.prediction}</p>
              </div>
            </div>
          ))}
        </div>
      </section>

      <section>
        <h2 className="text-xl font-bold text-slate-400 mb-4">OTROS PARTIDOS EN VIVO</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {otros.map((match: any, i: number) => (
            <div key={i} className="bg-slate-800 p-4 rounded-xl border border-slate-700 opacity-80">
              <div className="flex justify-between text-[10px] text-slate-500 mb-2">
                <span>{match.time || 'IN PLAY'}</span>
                <span>{match.intensity}</span>
              </div>
              <h4 className="font-bold text-sm">{match.game}</h4>
              <p className="text-xl font-bold text-blue-400">{match.score}</p>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}