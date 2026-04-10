import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

// Placeholder components for the two different modes
const BorrowerFeed = () => (
  <div className="dashboard-panel">
    <h3>Available Vaults</h3>
    <p>The anonymous feed of available loan offers will go here.</p>
  </div>
);

const LenderPortfolio = () => (
  <div className="dashboard-panel">
    <h3>Your Investments</h3>
    <p>Your active loans and the form to offer new funds will go here.</p>
  </div>
);

export default function Dashboard() {
  // Global state to track which mode the user is currently in
  const [mode, setMode] = useState('borrower'); 
  const navigate = useNavigate();

  const handleLogout = () => {
    // In the future, you will clear the user's secure token here
    navigate('/');
  };

  return (
    <div className="dashboard-wrapper">
      {/* Top Navigation Bar */}
      <header className="dashboard-header">
        <div className="logo">
          <h2>Student Capital</h2>
        </div>

        {/* SOFTWARE ENGINEERING CONCEPT: Conditional UI Toggle */}
        <div className="mode-toggle">
          <button 
            className={`toggle-btn ${mode === 'borrower' ? 'active' : ''}`}
            onClick={() => setMode('borrower')}
          >
            Borrow
          </button>
          <button 
            className={`toggle-btn ${mode === 'lender' ? 'active' : ''}`}
            onClick={() => setMode('lender')}
          >
            Lend
          </button>
        </div>

        <button onClick={handleLogout} className="btn-logout">
          Sign Out
        </button>
      </header>

      {/* Main Content Area */}
      <main className="dashboard-main">
        {mode === 'borrower' ? <BorrowerFeed /> : <LenderPortfolio />}
      </main>
    </div>
  );
}