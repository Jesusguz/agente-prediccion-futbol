import PredictorDashboard from './components/PredictorDashboard';

export default function Home() {
  return (
    <main className="min-h-screen bg-slate-900 text-slate-100">
      <div className="max-w-7xl mx-auto py-10 px-4">
        <h1 className="text-4xl font-extrabold mb-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-blue-500">
          LÍA PREDICTOR LIVE
        </h1>
        <p className="text-slate-400 mb-10 italic">Escaneo reactivo de +400 partidos en tiempo real</p>
        
        <PredictorDashboard />
      </div>
    </main>
  );
}