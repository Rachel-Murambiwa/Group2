import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

import Landing from './pages/Landing';
import Register from './pages/Register';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Profile from './pages/Profile'; 
import LoanRequestModal from './pages/LoanRequestModal'; 
import AdminDashboard from './pages/AdminDashboard';
import ProtectedRoute from './components/ProtectedRoute'; // <-- Security Wrapper

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

        {/* SECURE STUDENT ROUTES (Requires Login + 10min Timeout) */}
        <Route path="/dashboard" element={
          <ProtectedRoute>
            <Dashboard />
          </ProtectedRoute>
        } /> 
        
        <Route path="/profile" element={
          <ProtectedRoute>
            <Profile />
          </ProtectedRoute>
        } /> 
        
        <Route path="/loan-request" element={
          <ProtectedRoute>
            <LoanRequestModal />
          </ProtectedRoute>
        } />

        {/* SECURE ADMIN ROUTE (Requires Login + is_admin = 1) */}
        <Route path="/admin" element={
          <ProtectedRoute requireAdmin={true}>
            <AdminDashboard />
          </ProtectedRoute>
        } />
      </Routes>
    </Router>
  );
}

export default App;