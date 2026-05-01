import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import LoanRequestModal from './LoanRequestModal';

// -------------------------------------------------------------------------
// 1. COMPONENT: Borrower Feed UI (Responsive & Live)
// -------------------------------------------------------------------------
const BorrowerFeed = () => {
  const [selectedVault, setSelectedVault] = useState(null);
  const [vaults, setVaults] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchVaults = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await fetch('http://194.147.58.241:8091/vaults/get_available.php', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        const data = await response.json();
        
        if (response.ok) {
          setVaults(data.vaults || []);
        }
      } catch (error) {
        console.error("Connection error:", error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchVaults();
  }, []);

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-slate-200 pb-4 gap-2">
        <div>
          <h3 className="text-2xl md:text-3xl font-bold text-ashesi-red tracking-tight">Available Vaults</h3>
          <p className="text-slate-500 text-sm md:text-base font-medium mt-1">
            Browse anonymous, peer-funded stipends ready for immediate transfer.
          </p>
        </div>
        <div className="text-[10px] md:text-sm font-bold text-slate-400 uppercase tracking-wider">
          {isLoading ? "Loading..." : `${vaults.length} Vaults Active`}
        </div>
      </div>

      {isLoading ? (
        <div className="flex justify-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-ashesi-red"></div>
        </div>
      ) : vaults.length === 0 ? (
        <div className="text-center py-12 bg-white rounded-2xl border border-slate-100">
          <p className="text-slate-500 font-medium">No vaults are currently available. Check back later!</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
          {vaults.map((vault) => (
            <div 
              key={vault.id} 
              className="bg-white rounded-2xl p-5 md:p-6 shadow-sm border border-slate-100 hover:shadow-xl hover:border-rich-gold transition-all duration-300 group flex flex-col justify-between relative overflow-hidden"
            >
              <div className="absolute top-0 right-0 w-24 h-24 bg-rose-50 rounded-bl-full -z-10 opacity-50 transition-transform group-hover:scale-110"></div>
              <div>
                <div className="flex justify-between items-center mb-4">
                  <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase">
                    {vault.alias}
                  </span>
                  <span className="text-rich-gold text-xs tracking-widest">★★★★★</span>
                </div>
                <div className="mb-6">
                  <p className="text-slate-400 text-[10px] font-semibold uppercase tracking-wider mb-1">Amount</p>
                  <h4 className="text-3xl md:text-4xl font-bold text-slate-800">
                    <span className="text-lg text-slate-400 mr-1">GHS</span>
                    {vault.amount}
                  </h4>
                </div>
                <div className="flex gap-3 mb-6">
                  <div className="bg-slate-50 p-3 rounded-lg flex-1 border border-slate-100">
                    <p className="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-1">Interest</p>
                    <p className="font-bold text-ashesi-red text-sm">{vault.interest}%</p>
                  </div>
                  <div className="bg-slate-50 p-3 rounded-lg flex-1 border border-slate-100">
                    <p className="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-1">Term</p>
                    <p className="font-bold text-slate-700 text-sm">{vault.duration} Days</p>
                  </div>
                </div>
              </div>
              <button 
                onClick={() => setSelectedVault(vault)}
                className="w-full py-3 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-xl uppercase tracking-wider text-xs hover:bg-ashesi-red hover:text-white hover:border-ashesi-red transition-all shadow-sm"
              >
                Request Funds
              </button>
            </div>
          ))}
        </div>
      )}

      <LoanRequestModal 
        isOpen={!!selectedVault} 
        vault={selectedVault} 
        onClose={() => setSelectedVault(null)} 
      />
    </div>
  );
};

// -------------------------------------------------------------------------
// 2. COMPONENT: Lender Portfolio UI (ROI Tracking Enabled)
// -------------------------------------------------------------------------
const LenderPortfolio = () => {
  const [formData, setFormData] = useState({ amount: '', interest: '', duration: '' });
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const [portfolio, setPortfolio] = useState({
    stats: { total_deployed: 0, realized_profit: 0, active_risk: 0, active_count: 0, paid_count: 0 },
    contracts: []
  });

  const fetchPortfolioData = async () => {
    const savedUser = localStorage.getItem('user');
    if (!savedUser) return;
    
    const user = JSON.parse(savedUser);
    const userId = user.id;
    
    try {
      const response = await fetch(`http://194.147.58.241:8091/vaults/get_lender_stats.php?userID=${userId}`);
      const data = await response.json();
      if (response.ok) {
        setPortfolio(data);
      }
    } catch (err) {
      console.error("Connection error while fetching portfolio", err);
    }
  };

  useEffect(() => {
    fetchPortfolioData();
  }, []);

  const handleDeployCapital = async (e) => {
    e.preventDefault();
    setError('');
    setMessage('');
    
    const savedUser = JSON.parse(localStorage.getItem('user'));
    const token = localStorage.getItem('token');
    
    setIsSubmitting(true);
    try {
      const response = await fetch('http://194.147.58.241:8091/vaults/create.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          userID: savedUser.id,
          amount: formData.amount,
          interest: formData.interest,
          duration: formData.duration,
        }),
      });

      const data = await response.json();
      if (response.ok) {
        setFormData({ amount: '', interest: '', duration: '' });
        setMessage('Capital deployed successfully.');
        fetchPortfolioData();
      } else {
        setError(data.error || 'Failed to deploy capital.');
      }
    } catch (err) {
      setError('Cannot connect to server.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="space-y-8">
      <div className="flex justify-between items-end border-b border-slate-200 pb-4">
        <div>
          <h3 className="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">Your Portfolio</h3>
          <p className="text-slate-500 text-sm md:text-base font-medium mt-1">
            Create vaults to safely grow your capital while empowering peers.
          </p>
        </div>
      </div>

      {/* DYNAMIC ROI STATS DISPLAY */}
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
        <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-green-500">
          <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Total Profit Earned</p>
          <h4 className="text-2xl md:text-3xl font-bold text-green-600">
            <span className="text-base text-green-400 mr-1">+GHS</span>
            {Number(portfolio.stats.realized_profit).toFixed(2)}
          </h4>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-rich-gold">
          <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Capital at Risk</p>
          <h4 className="text-2xl md:text-3xl font-bold text-slate-800">
            <span className="text-base text-slate-400 mr-1">GHS</span>
            {Number(portfolio.stats.active_risk).toFixed(2)}
          </h4>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
          <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Portfolio Velocity</p>
          <h4 className="text-lg md:text-xl font-bold text-slate-700">
            {portfolio.stats.paid_count} Paid <span className="text-slate-300 mx-1">/</span> {portfolio.stats.active_count} Active
          </h4>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
          <div className="absolute top-0 right-0 w-32 h-1 bg-rich-gold"></div>
          <h4 className="text-lg font-bold text-slate-800 mb-5">Create New Vault</h4>
          <form className="space-y-4" onSubmit={handleDeployCapital}>
            <input 
               name="amount" 
               value={formData.amount} 
               onChange={(e) => setFormData({...formData, amount: e.target.value})} 
               type="number" 
               className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 text-sm font-bold" 
               placeholder="Amount (GHS)" 
               required 
            />
            <div className="grid grid-cols-2 gap-4">
              <input 
                name="interest" 
                value={formData.interest} 
                onChange={(e) => setFormData({...formData, interest: e.target.value})} 
                type="number" 
                className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 text-sm font-bold" 
                placeholder="Interest %" 
                required 
              />
              <input 
                name="duration" 
                value={formData.duration} 
                onChange={(e) => setFormData({...formData, duration: e.target.value})} 
                type="number" 
                className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 text-sm font-bold" 
                placeholder="Days" 
                required 
              />
            </div>
            {message && <p className="text-xs font-bold text-green-600">{message}</p>}
            {error && <p className="text-xs font-bold text-red-600">{error}</p>}
            <button disabled={isSubmitting} className="w-full py-3 bg-slate-800 text-white font-bold rounded-lg uppercase text-xs hover:bg-rich-gold transition-all">
              {isSubmitting ? 'Deploying...' : 'Deploy Capital'}
            </button>
          </form>
        </div>

        {/* DYNAMIC CONTRACTS LIST */}
        <div className="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
          <h4 className="text-lg font-bold text-slate-800 mb-5">Active Contracts</h4>
          <div className="space-y-4">
            {portfolio.contracts.length === 0 ? (
              <div className="text-center py-10 text-slate-400 font-medium">No active contracts yet.</div>
            ) : (
              portfolio.contracts.map((inv) => {
                const isPendingConf = inv.status === 'pending_confirmation';
                const isPaid = inv.status === 'paid';

                return (
                  <div 
                    key={inv.id} 
                    className={`flex flex-col sm:flex-row items-center justify-between p-4 border rounded-xl transition-all ${
                      isPendingConf ? 'bg-green-50 border-green-200 scale-[1.01]' : 'border-slate-100 hover:bg-slate-50'
                    }`}
                  >
                    <div className="flex items-center gap-4">
                      <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm ${
                        isPaid ? 'bg-green-100 text-green-600' : isPendingConf ? 'bg-green-500 text-white' : 'bg-slate-100 text-slate-500'
                      }`}>
                        {isPaid ? '✓' : isPendingConf ? '🔔' : '🕒'}
                      </div>
                      <div>
                        <h5 className="font-bold text-slate-800 text-sm">Vault #{inv.id}</h5>
                        <p className={`text-[10px] font-bold uppercase tracking-wider ${
                          isPendingConf ? 'text-green-600 animate-pulse' : 'text-slate-400'
                        }`}>
                          {isPendingConf ? 'Check MOMO - Borrower Paid' : inv.status}
                        </p>
                      </div>
                    </div>
                    <div className="text-right">
                      <h5 className="font-bold text-slate-800 text-sm">GHS {inv.amount}</h5>
                      <p className="text-[10px] font-bold text-rich-gold">+{inv.interest}% Interest</p>
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

// -------------------------------------------------------------------------
// 3. MAIN COMPONENT: Dashboard (Responsive Wrapper)
// -------------------------------------------------------------------------
export default function Dashboard() {
  const [mode, setMode] = useState('borrower'); 
  const navigate = useNavigate();
  const [alias, setAlias] = useState(""); 

  useEffect(() => {
    const savedUser = localStorage.getItem('user');
    if (savedUser) {
      const parsedUser = JSON.parse(savedUser);
      setAlias(parsedUser.alias);
    } else {
      navigate('/login');
    }
  }, [navigate]);

  const handleLogout = () => {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    navigate('/');
  };

  return (
    <div className="min-h-screen w-full flex flex-col bg-slate-50 font-sans">
      <header className="flex flex-col md:flex-row justify-between items-center px-6 md:px-10 py-4 bg-white shadow-sm border-b-2 border-slate-200 z-10 gap-4">
        <div className="w-full md:w-auto flex justify-between items-center">
          <Link to="/dashboard" className="text-ashesi-red text-xl md:text-2xl font-bold tracking-tight no-underline hover:text-ashesi-red-dark transition-colors">
            CharleeDash+
          </Link>
          <div className="md:hidden flex items-center gap-2">
             <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-ashesi-red to-rich-gold flex items-center justify-center text-white font-bold text-[10px]">
              {alias ? alias.charAt(0).toUpperCase() : "?"}
            </div>
          </div>
        </div>

        <div className="flex bg-slate-100 rounded-full p-1 shadow-inner border border-slate-200">
          <button 
            className={`px-8 py-2 rounded-full font-bold text-xs uppercase transition-all ${
              mode === 'borrower' ? 'bg-white text-ashesi-red shadow-sm' : 'text-slate-500'
            }`} 
            onClick={() => setMode('borrower')}
          >
            Borrow
          </button>
          <button 
            className={`px-8 py-2 rounded-full font-bold text-xs uppercase transition-all ${
              mode === 'lender' ? 'bg-white text-rich-gold shadow-sm' : 'text-slate-500'
            }`} 
            onClick={() => setMode('lender')}
          >
            Lend
          </button>
        </div>

        <div className="hidden md:flex items-center gap-4">
          <Link to="/profile" className="flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-50 border border-slate-100 no-underline group hover:border-rich-gold transition-all">
            <div className="w-6 h-6 rounded-full bg-gradient-to-tr from-ashesi-red to-rich-gold flex items-center justify-center text-white font-bold text-[10px] shadow-sm">
              {alias ? alias.charAt(0).toUpperCase() : "?"}
            </div>
            <span className="font-bold text-sm text-slate-700">{alias || "User"}</span>
          </Link>
          <button onClick={handleLogout} className="px-4 py-2 bg-transparent border-2 border-slate-200 rounded-lg text-slate-500 font-bold text-[10px] uppercase hover:bg-red-50 hover:text-red-600 transition-all">
            Sign Out
          </button>
        </div>

        <div className="md:hidden w-full">
           <button onClick={handleLogout} className="w-full py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-500 font-bold text-xs uppercase">
            Sign Out
          </button>
        </div>
      </header>

      <main className="flex-1 p-4 md:p-10 max-w-5xl mx-auto w-full">
        {mode === 'borrower' ? <BorrowerFeed /> : <LenderPortfolio />}
      </main>
    </div>
  );
}