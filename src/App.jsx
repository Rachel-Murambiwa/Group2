import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import './App.css';

// 1. Import our actual, finished pages
import Landing from './pages/Landing';
import Register from './pages/Register';
import Login from './pages/Login'; // <-- This is the missing link!

// 2. Keep only the placeholders for pages we haven't built yet
const Verify = () => <div className="page-container"><h2>Check your email to verify...</h2></div>;
const Dashboard = () => <div className="page-container"><h2>Anonymous Lending Dashboard</h2></div>;

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Landing />} />
        
        {/* We wrap the auth pages in .app-layout so they stay centered and premium */}
        <Route path="/register" element={<div className="app-layout"><Register /></div>} />
        <Route path="/login" element={<div className="app-layout"><Login /></div>} />
        
        <Route path="/verify" element={<div className="app-layout"><Verify /></div>} />
        <Route path="/dashboard" element={<div className="app-layout"><Dashboard /></div>} />
      </Routes>
    </Router>
  );
}

export default App;