'use client';
import { useState, useEffect } from 'react';

interface Prediccion {
  game: string;
  score: string;
  prediction: string;
  intensity: string;
  is_over_2_5: boolean;
}

export default function PredictorDashboard() {
  const [data, setData] = useState<Prediccion[]>([]);
  const [loading, setLoading] = useState(true);

 const fetchPredictions = async () => {
    try {
    
        const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
        //const response = await fetch(`${baseUrl}/api/analisis-agente`); 
        const response = await fetch('https://agente-prediccion-futbol-production.up.railway.app/analisis-agente');
        
        if (!response.ok) throw new Error('Error en la red');
        
        const json = await response.json();
        setData(json.predicciones);
        setLoading(false);
    } catch (error) {
        console.error("Lía no responde en esta ruta:", error);
    }
};

  useEffect(() => {
    fetchPredictions();
    const interval = setInterval(fetchPredictions, 30000); // Recarga cada 30 seg
    return () => clearInterval(interval);
  }, []);

  if (loading) return <div className="text-center p-10">Cargando datos de Lía...</div>;

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
      {data.map((match, i) => (
        <div key={i} className="bg-slate-800 border border-slate-700 p-4 rounded-xl shadow-xl hover:border-green-500 transition-all">
          <div className="flex justify-between text-[10px] mb-2">
            <span className="text-slate-400 uppercase font-mono">{match.intensity}</span>
            <span className={match.is_over_2_5 ? "text-green-400" : "text-sky-400"}>
              {match.is_over_2_5 ? '🔥 POTENCIAL OVER' : '❄️ POTENCIAL UNDER'}
            </span>
          </div>
          <h3 className="font-bold text-lg leading-tight">{match.game}</h3>
          <p className="text-3xl font-black text-white my-3 font-mono">{match.score}</p>
          <div className="bg-black/20 p-2 rounded border border-white/5">
            <p className="text-xs italic text-green-300">Lía dice: {match.prediction}</p>
          </div>
        </div>
      ))}
    </div>
  );
}