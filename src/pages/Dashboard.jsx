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
          setVaults(data.vaults);
        } else {
          console.error("Failed to fetch vaults:", data.error);
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
// 2. COMPONENT: Lender Portfolio UI (Responsive & Live)
// -------------------------------------------------------------------------
const LenderPortfolio = () => {
  const [formData, setFormData] = useState({
    amount: '',
    interest: '',
    duration: '',
  });
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Temporary Dummy Data for display until we fetch live investments
  const ACTIVE_INVESTMENTS = [
    { id: 101, alias: "Vault #804", amount: 500, return: "+GHS 25", status: "Active", due: "In 12 Days" },
    { id: 102, alias: "Vault #211", amount: 200, return: "+GHS 0", status: "Paid", due: "Completed" },
  ];

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    setMessage('');
    setError('');
  };

  const handleDeployCapital = async (e) => {
    e.preventDefault();
    setMessage('');
    setError('');

    const amount = Number(formData.amount);
    const interest = Number(formData.interest);
    const duration = Number(formData.duration);

    if (!amount || amount <= 0) {
      setError('Enter an amount greater than zero.');
      return;
    }

    if (Number.isNaN(interest) || interest < 0 || interest > 15) {
      setError('Interest must be between 0 and 15%.');
      return;
    }

    if (!duration || duration <= 0) {
      setError('Enter a duration of at least 1 day.');
      return;
    }

    const savedUser = localStorage.getItem('user');
    const user = savedUser ? JSON.parse(savedUser) : null;

    if (!user?.id && !user?.userID) {
      setError('Please log in again before deploying capital.');
      return;
    }

    setIsSubmitting(true);

    try {
      const token = localStorage.getItem('token');
      const response = await fetch('http://194.147.58.241:8091/vaults/create.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          userID: user.userID || user.id,
          amount,
          interest,
          duration,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        setError(data.error || 'Could not deploy capital.');
        return;
      }

      setFormData({ amount: '', interest: '', duration: '' });
      setMessage(data.message || 'Capital deployed successfully.');
    } catch (err) {
      setError('Cannot connect to server. Please try again.');
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

      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
        <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
          <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Total Deployed</p>
          <h4 className="text-2xl md:text-3xl font-bold text-slate-800"><span className="text-base text-slate-400 mr-1">GHS</span>700</h4>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center border-l-4 border-l-rich-gold">
          <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Projected Returns</p>
          <h4 className="text-2xl md:text-3xl font-bold text-green-600"><span className="text-base text-green-400 mr-1">+GHS</span>25</h4>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center sm:col-span-2 md:col-span-1">
          <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Vault Status</p>
          <h4 className="text-lg md:text-xl font-bold text-slate-700">1 Active / 1 Paid</h4>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
          <div className="absolute top-0 right-0 w-32 h-1 bg-rich-gold"></div>
          <h4 className="text-lg font-bold text-slate-800 mb-5">Create New Vault</h4>
          
          <form className="space-y-4" onSubmit={handleDeployCapital}>
            <div>
              <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">Amount (GHS)</label>
              <input name="amount" value={formData.amount} onChange={handleChange} type="number" min="1" step="0.01" className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 focus:bg-white focus:border-rich-gold outline-none transition-all font-bold text-slate-700 text-sm" placeholder="e.g. 200" />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">Interest (%)</label>
                <input name="interest" value={formData.interest} onChange={handleChange} type="number" min="0" max="15" step="0.01" className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 focus:bg-white focus:border-rich-gold outline-none transition-all font-bold text-slate-700 text-sm" placeholder="0 - 15" />
              </div>
              <div>
                <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">Days</label>
                <input name="duration" value={formData.duration} onChange={handleChange} type="number" min="1" step="1" className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 focus:bg-white focus:border-rich-gold outline-none transition-all font-bold text-slate-700 text-sm" placeholder="7" />
              </div>
            </div>
            
            {error && <p className="text-xs font-bold text-red-600">{error}</p>}
            {message && <p className="text-xs font-bold text-green-600">{message}</p>}
            
            <button disabled={isSubmitting} className="w-full py-3 mt-2 bg-slate-800 text-white font-bold rounded-lg uppercase tracking-wider text-xs hover:bg-rich-gold hover:shadow-lg transition-all disabled:opacity-60 disabled:cursor-not-allowed">
              {isSubmitting ? 'Deploying...' : 'Deploy Capital'}
            </button>
          </form>
        </div>

        <div className="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
          <h4 className="text-lg font-bold text-slate-800 mb-5">Active Contracts</h4>
          <div className="space-y-4">
            {ACTIVE_INVESTMENTS.map((inv) => (
              <div key={inv.id} className="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors gap-4">
                <div className="flex items-center gap-4">
                  <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm ${inv.status === 'Paid' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600'}`}>
                    {inv.status === 'Paid' ? '✓' : '↻'}
                  </div>
                  <div>
                    <h5 className="font-bold text-slate-800 text-sm">{inv.alias}</h5>
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{inv.due}</p>
                  </div>
                </div>
                <div className="text-left sm:text-right w-full sm:w-auto border-t sm:border-0 pt-2 sm:pt-0">
                  <h5 className="font-bold text-slate-800 text-sm">GHS {inv.amount}</h5>
                  <p className={`text-[10px] font-bold tracking-wider ${inv.status === 'Paid' ? 'text-green-500' : 'text-rich-gold'}`}>
                    {inv.return}
                  </p>
                </div>
              </div>
            ))}
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
          <Link to="/dashboard" className="text-ashesi-red text-xl md:text-2xl font-bold tracking-tight hover:text-ashesi-red-dark transition-colors no-underline">
            CharleeDash+
          </Link>
          <div className="md:hidden flex items-center gap-2">
             <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-ashesi-red to-rich-gold flex items-center justify-center text-white font-bold text-[10px]">
              {alias ? alias.charAt(0).toUpperCase() : "?"}
            </div>
          </div>
        </div>

        <div className="flex bg-slate-100 rounded-full p-1 shadow-inner border border-slate-200 w-full max-w-[300px] md:w-auto">
          <button 
            className={`flex-1 md:px-8 py-2 rounded-full font-bold text-[10px] md:text-sm tracking-wide transition-all duration-300 uppercase ${
              mode === 'borrower' ? 'bg-white text-ashesi-red shadow-sm' : 'bg-transparent text-slate-500'
            }`}
            onClick={() => setMode('borrower')}
          >
            Borrow
          </button>
          <button 
            className={`flex-1 md:px-8 py-2 rounded-full font-bold text-[10px] md:text-sm tracking-wide transition-all duration-300 uppercase ${
              mode === 'lender' ? 'bg-white text-rich-gold shadow-sm' : 'bg-transparent text-slate-500'
            }`}
            onClick={() => setMode('lender')}
          >
            Lend
          </button>
        </div>

        <div className="hidden md:flex items-center gap-4">
          <Link to="/profile" className="flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-50 border border-slate-100 hover:border-rich-gold hover:bg-white transition-all no-underline group">
            <div className="w-6 h-6 rounded-full bg-gradient-to-tr from-ashesi-red to-rich-gold flex items-center justify-center text-white font-bold text-[10px] shadow-sm">
              {alias ? alias.charAt(0).toUpperCase() : "?"}
            </div>
            <span className="font-bold text-sm text-slate-700 tracking-wide group-hover:text-rich-gold transition-colors">
              {alias || "Loading..."}
            </span>
          </Link>
          <button onClick={handleLogout} className="px-4 py-2 bg-transparent border-2 border-slate-200 rounded-lg text-slate-500 font-bold text-[10px] uppercase transition-all hover:bg-red-50 hover:text-red-600">
            Sign Out
          </button>
        </div>

        {/* Mobile Logout Only */}
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