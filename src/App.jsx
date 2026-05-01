import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

import Landing from './pages/Landing';
import Register from './pages/Register';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Profile from './pages/Profile'; // 
import LoanRequestModal from './pages/LoanRequestModal'; 
import AdminDashboard from './pages/AdminDashboard';

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
        <Route path="/profile" element={<Profile />} /> {/* <-- 2. Add the route here */}
        <Route path="/loan-request" element={<LoanRequestModal />} />
        <Route path="/admin" element={<AdminDashboard />} />
      </Routes>
    </Router>
  );
}

export default App;