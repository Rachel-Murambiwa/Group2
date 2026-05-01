import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

import Landing from './pages/Landing';
import Register from './pages/Register';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Profile from './pages/Profile'; 
import LoanRequestModal from './pages/LoanRequestModal'; 
import AdminDashboard from './pages/AdminDashboard';
import SessionGuard from './components/SessionGuard'; // <-- The New Security Bouncer

const Verify = () => (
  <div className="min-h-screen flex items-center justify-center bg-slate-50">
    <h2 className="text-2xl font-bold text-ashesi-red">Check your email to verify...</h2>
  </div>
);

function App() {
  return (
    <Router>
      <Routes>
        {/* PUBLIC ROUTES (No lock needed) */}
        <Route path="/" element={<Landing />} />
        <Route path="/register" element={<Register />} />
        <Route path="/login" element={<Login />} />
        <Route path="/verify" element={<Verify />} />

        {/* SECURE STUDENT ROUTES (Database-Backed Session Timer) */}
        <Route path="/dashboard" element={
          <SessionGuard>
            <Dashboard />
          </SessionGuard>
        } /> 
        
        <Route path="/profile" element={
          <SessionGuard>
            <Profile />
          </SessionGuard>
        } /> 
        
        <Route path="/loan-request" element={
          <SessionGuard>
            <LoanRequestModal />
          </SessionGuard>
        } />

        {/* SECURE ADMIN ROUTE (Database-Backed Session Timer) */}
        {/* Note: AdminDashboard.jsx already checks for is_admin === 1 internally! */}
        <Route path="/admin" element={
          <SessionGuard>
            <AdminDashboard />
          </SessionGuard>
        } />
      </Routes>
    </Router>
  );
}

export default App;