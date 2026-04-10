import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

// 1. We import the REAL pages here
import Landing from './pages/Landing';
import Register from './pages/Register';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';

// 2. Only the Verify placeholder remains (notice there is NO Dashboard placeholder here!)
const Verify = () => (
  <div className="min-h-screen flex items-center justify-center bg-slate-50">
    <h2 className="text-2xl font-bold text-ashesi-red">Check your email to verify...</h2>
  </div>
);

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Landing />} />
        <Route path="/register" element={<Register />} />
        <Route path="/login" element={<Login />} />
        <Route path="/verify" element={<Verify />} />
        <Route path="/dashboard" element={<Dashboard />} /> 
      </Routes>
    </Router>
  );
}

export default App;