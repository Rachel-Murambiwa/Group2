import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import './App.css';

// Import our pages
import Landing from './pages/Landing';
import Register from './pages/Register';

// Temporary placeholders
const Login = () => <div className="page-container"><h2>Login Page (Coming Soon)</h2></div>;
const Verify = () => <div className="page-container"><h2>Check your email to verify...</h2></div>;
const Dashboard = () => <div className="page-container"><h2>Anonymous Lending Dashboard</h2></div>;

function App() {
  return (
    <Router>
      {/* We removed the .app-layout wrapper here so the landing page can take up the full screen natively */}
      <Routes>
        {/* The root path now points to our new Landing page! */}
        <Route path="/" element={<Landing />} />
        
        <Route path="/register" element={<div className="app-layout"><Register /></div>} />
        <Route path="/login" element={<div className="app-layout"><Login /></div>} />
        <Route path="/verify" element={<div className="app-layout"><Verify /></div>} />
        <Route path="/dashboard" element={<div className="app-layout"><Dashboard /></div>} />
      </Routes>
    </Router>
  );
}

export default App;