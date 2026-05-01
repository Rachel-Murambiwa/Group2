import { useEffect } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';

export default function ProtectedRoute({ children, requireAdmin = false }) {
  const navigate = useNavigate();
  const savedUser = localStorage.getItem('user');
  const user = savedUser ? JSON.parse(savedUser) : null;

  // --- ISSUE #7: 10-MINUTE INACTIVITY TIMEOUT ---
  useEffect(() => {
    let timeoutId;

    const logoutUser = () => {
      localStorage.removeItem('user');
      localStorage.removeItem('token');
      alert("You have been logged out due to inactivity to protect your account.");
      navigate('/login');
    };

    const resetTimer = () => {
      clearTimeout(timeoutId);
      // 10 minutes = 10 * 60 * 1000 = 600000 milliseconds
      timeoutId = setTimeout(logoutUser, 600000);
    };

    // Listen for any interaction to reset the 10-minute timer
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
    events.forEach(event => document.addEventListener(event, resetTimer));

    // Start the timer when they hit the page
    resetTimer();

    return () => {
      clearTimeout(timeoutId);
      events.forEach(event => document.removeEventListener(event, resetTimer));
    };
  }, [navigate]);

  // --- ISSUE #6: ROUTE SECURITY ---
  
  // 1. If they aren't logged in at all, kick them to login
  if (!user) {
    return <Navigate to="/login" replace />;
  }

  // 2. If the route requires Admin, but they aren't an admin, kick them to dashboard
  if (requireAdmin && user.is_admin != 1) {
    return <Navigate to="/dashboard" replace />;
  }

  // 3. If they pass all checks, let them see the page!
  return children;
}