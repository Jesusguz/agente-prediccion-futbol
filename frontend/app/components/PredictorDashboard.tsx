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

interface DebugInfo {
  status: string;
  message: string;
  file?: string;
  line?: number;
  debug_info?: any;
}

export default function PredictorDashboard() {
  const [data, setData] = useState<Prediccion[]>([]);
  const [loading, setLoading] = useState(true);
  const [debugInfo, setDebugInfo] = useState<DebugInfo | null>(null);

  const fetchPredictions = async () => {
    try {
      const response = await fetch('https://agente-prediccion-futbol-production.up.railway.app/analisis-agente');
      
      if (!response.ok) {
        throw new Error(`Error de red: ${response.status}`);
      }

      const json = await response.json();

      if (json.status === 'fatal_error' || json.status === 'debug' || json.status === 'debug_mode_active') {
        setDebugInfo(json);
        setData([]);
      } else {
        setDebugInfo(null);
        setData(json.predicciones || []);
      }
    } catch (error: any) {
      setDebugInfo({
        status: 'connection_error',
        message: 'No se pudo conectar con el servidor de Lía o hay un problema de CORS.',
      });
      console.error("Lía error:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPredictions();
    const interval = setInterval(fetchPredictions, 60000);
    return () => clearInterval(interval);
  }, []);

  const recomendados = data.filter((p) => p.prediction.includes('Alta') || p.is_over_2_5);
  const otros = data.filter((p) => !recomendados.some(r => r.game === p.game));

  if (loading && data.length === 0) {
    return (
      <div className="p-10 text-center">
        <div className="animate-spin inline-block w-8 h-8 border-4 border-green-500 border-t-transparent rounded-full mb-4"></div>
        <p className="text-green-400 font-mono">Lía está analizando mercados en vivo...</p>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto p-4 space-y-6 text-white bg-slate-900 min-h-screen">
      <header className="flex justify-between items-center border-b border-slate-800 pb-4">
        <h1 className="text-2xl font-black italic">
          LÍA <span className="text-green-500">PREDICTOR</span>
        </h1>
        <button 
          onClick={() => { setLoading(true); fetchPredictions(); }}
          className="text-[10px] bg-slate-800 hover:bg-slate-700 px-3 py-1 rounded text-slate-400 transition"
        >
          RECARGAR AHORA
        </button>
      </header>

      {debugInfo && (
        <div className="p-4 bg-red-950/30 border border-red-500/50 rounded-xl font-mono text-xs text-red-200 shadow-2xl">
          <div className="flex items-center gap-2 mb-2">
            <span className="w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
            <p className="font-bold text-red-500 uppercase tracking-tighter">Monitor de Depuración Activo</p>
          </div>
          <p className="mb-2"><span className="text-red-400 font-bold">Respuesta:</span> {debugInfo.message}</p>
          
          {debugInfo.debug_info && (
            <div className="mt-4 border-t border-red-500/30 pt-4">
              <p className="font-bold text-yellow-500 mb-2 underline">ESTRUCTURA REAL DEL PARTIDO (JSON):</p>
              <pre className="bg-black/60 p-4 rounded-lg overflow-auto max-h-[400px] text-green-400 text-[9px] border border-white/10">
                {JSON.stringify(debugInfo.debug_info.full_structure, null, 2)}
              </pre>
              <div className="mt-2 text-blue-300">
                <p className="font-bold text-[10px]">LLAVES DISPONIBLES EN STATUS:</p>
                <p>{JSON.stringify(debugInfo.debug_info.status_keys)}</p>
              </div>
            </div>
          )}
        </div>
      )}

      {recomendados.length > 0 && (
        <section>
          <h2 className="text-xl font-bold text-yellow-500 uppercase tracking-widest mb-4 flex items-center gap-2">
            ⭐ Top Recomendados
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {recomendados.map((m, i) => (
              <div key={`rec-${m.game}-${i}`} className="p-5 bg-slate-800 border-l-4 border-yellow-500 rounded-r-xl shadow-lg">
                <div className="flex justify-between text-[10px] text-slate-400 mb-2">
                  <span className="bg-red-900/40 text-red-400 px-2 py-0.5 rounded font-bold">
                    {m.time && m.time !== "" ? m.time : 'FINALIZADO?'}
                  </span>
                  <span className="uppercase">{m.intensity}</span>
                </div>
                <p className="font-bold text-lg">{m.game}</p>
                <p className="text-3xl font-black text-blue-400 font-mono my-2">{m.score}</p>
                <div className="bg-black/20 p-2 rounded">
                  <p className="text-sm italic text-green-400">🎯 {m.prediction}</p>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      {otros.length > 0 && (
        <section>
          <h2 className="text-lg font-bold text-slate-500 uppercase tracking-widest mb-4">Otros Mercados Activos</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {otros.map((m, i) => (
              <div key={`other-${m.game}-${i}`} className="p-4 bg-slate-900/50 border border-slate-800 rounded-xl opacity-80">
                <div className="flex justify-between text-[10px] text-slate-500 mb-1">
                  <span>{m.time || 'IN PLAY'}</span>
                </div>
                <p className="font-bold text-sm text-slate-300">{m.game}</p>
                <p className="text-xl font-black text-slate-500">{m.score}</p>
              </div>
            ))}
          </div>
        </section>
      )}

      {data.length === 0 && !loading && !debugInfo && (
        <div className="p-20 text-center border-2 border-dashed border-slate-800 rounded-2xl">
          <p className="text-slate-500">No hay partidos activos en este momento que cumplan los filtros.</p>
        </div>
      )}
    </div>
  );
}