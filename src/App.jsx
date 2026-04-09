import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import './App.css';

// Import the page we just built
import Register from './pages/Register';

// Temporary placeholders for pages we haven't built yet
const Login = () => <div className="page-container"><h2>Login Page</h2></div>;
const Verify = () => <div className="page-container"><h2>Check your email to verify...</h2></div>;
const Dashboard = () => <div className="page-container"><h2>Anonymous Lending Dashboard</h2></div>;

function App() {
  return (
    <Router>
      <div className="app-layout">
        {/* The Routes determine which component renders based on the URL */}
        <Routes>
          {/* Automatically redirect the root URL to the register page for now */}
          <Route path="/" element={<Navigate to="/register" />} />
          
          <Route path="/register" element={<Register />} />
          <Route path="/login" element={<Login />} />
          <Route path="/verify" element={<Verify />} />
          <Route path="/dashboard" element={<Dashboard />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;