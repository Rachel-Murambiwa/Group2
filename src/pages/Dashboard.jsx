import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import LoanRequestModal from './LoanRequestModal';

// 1. DUMMY DATA: Borrower Feed
const AVAILABLE_VAULTS = [
  { id: 1, alias: "Vault #804", amount: 500, interest: 5, duration: "14 Days", rating: "★★★★★" },
  { id: 2, alias: "Vault #211", amount: 200, interest: 0, duration: "7 Days", rating: "★★★★☆" },
  { id: 3, alias: "Vault #993", amount: 1000, interest: 8, duration: "30 Days", rating: "★★★★★" },
  { id: 4, alias: "Vault #405", amount: 150, interest: 2, duration: "5 Days", rating: "★★★☆☆" },
];

// 2. COMPONENT: Borrower Feed UI
// Remove the old hardcoded const AVAILABLE_VAULTS = [...] array from the top of the file!

const BorrowerFeed = () => {
  const [selectedVault, setSelectedVault] = useState(null);
  
  // NEW: State to hold the live vaults and a loading state
  const [vaults, setVaults] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  // NEW: Fetch vaults from PHP when the component loads
  useEffect(() => {
    const fetchVaults = async () => {
      try {
        const response = await fetch('http://localhost/StudentLendingSystem/Group2/api/vaults/get_available.php');
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
      <div className="flex justify-between items-end border-b border-slate-200 pb-4">
        <div>
          <h3 className="text-3xl font-bold text-ashesi-red tracking-tight">Available Vaults</h3>
          <p className="text-slate-500 font-medium mt-1">
            Browse anonymous, peer-funded stipends ready for immediate transfer.
          </p>
        </div>
        <div className="text-sm font-bold text-slate-400 uppercase tracking-wider">
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
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {vaults.map((vault) => (
            <div 
              key={vault.id} 
              className="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-xl hover:border-rich-gold transition-all duration-300 group flex flex-col justify-between relative overflow-hidden"
            >
              <div className="absolute top-0 right-0 w-24 h-24 bg-rose-50 rounded-bl-full -z-10 opacity-50 transition-transform group-hover:scale-110"></div>

              <div>
                <div className="flex justify-between items-center mb-4">
                  <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold tracking-widest uppercase">
                    {vault.alias}
                  </span>
                  <span className="text-rich-gold text-sm tracking-widest">
                    ★★★★★
                  </span>
                </div>
                
                <div className="mb-6">
                  <p className="text-slate-400 text-sm font-semibold uppercase tracking-wider mb-1">Amount</p>
                  <h4 className="text-4xl font-bold text-slate-800">
                    <span className="text-xl text-slate-400 mr-1">GHS</span>
                    {vault.amount}
                  </h4>
                </div>

                <div className="flex gap-4 mb-6">
                  <div className="bg-slate-50 p-3 rounded-lg flex-1 border border-slate-100">
                    <p className="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Interest</p>
                    <p className="font-bold text-ashesi-red">{vault.interest}%</p>
                  </div>
                  <div className="bg-slate-50 p-3 rounded-lg flex-1 border border-slate-100">
                    <p className="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Term</p>
                    <p className="font-bold text-slate-700">{vault.duration} Days</p>
                  </div>
                </div>
              </div>

              <button 
                onClick={() => setSelectedVault(vault)}
                className="w-full py-3.5 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-xl uppercase tracking-wider hover:bg-ashesi-red hover:text-white hover:border-ashesi-red transition-all shadow-sm"
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

// 3. DUMMY DATA: Lender Portfolio
const ACTIVE_INVESTMENTS = [
  { id: 101, alias: "Vault #804", amount: 500, return: "+GHS 25", status: "Active", due: "In 12 Days" },
  { id: 102, alias: "Vault #211", amount: 200, return: "+GHS 0", status: "Paid", due: "Completed" },
];

// 4. COMPONENT: Lender Portfolio UI
const LenderPortfolio = () => (
  <div className="space-y-8">
    <div className="flex justify-between items-end border-b border-slate-200 pb-4">
      <div>
        <h3 className="text-3xl font-bold text-slate-800 tracking-tight">Your Portfolio</h3>
        <p className="text-slate-500 font-medium mt-1">
          Create vaults to safely grow your capital while empowering peers.
        </p>
      </div>
    </div>

    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
        <p className="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Total Deployed</p>
        <h4 className="text-3xl font-bold text-slate-800"><span className="text-lg text-slate-400 mr-1">GHS</span>700</h4>
      </div>
      <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center border-l-4 border-l-rich-gold">
        <p className="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Projected Returns</p>
        <h4 className="text-3xl font-bold text-green-600"><span className="text-lg text-green-400 mr-1">+GHS</span>25</h4>
      </div>
      <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
        <p className="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Vault Status</p>
        <h4 className="text-xl font-bold text-slate-700">1 Active / 1 Paid</h4>
      </div>
    </div>

    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div className="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
        <div className="absolute top-0 right-0 w-32 h-1 bg-rich-gold"></div>
        <h4 className="text-lg font-bold text-slate-800 mb-5">Create New Vault</h4>
        
        <form className="space-y-4" onSubmit={(e) => e.preventDefault()}>
          <div>
            <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Amount (GHS)</label>
            <input type="number" className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 focus:bg-white focus:border-rich-gold focus:ring-2 focus:ring-rich-gold/20 outline-none transition-all font-bold text-slate-700" placeholder="e.g. 200" />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Interest (%)</label>
              <input type="number" className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 focus:bg-white focus:border-rich-gold focus:ring-2 focus:ring-rich-gold/20 outline-none transition-all font-bold text-slate-700" placeholder="0 - 15" />
            </div>
            <div>
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Days</label>
              <input type="number" className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 focus:bg-white focus:border-rich-gold focus:ring-2 focus:ring-rich-gold/20 outline-none transition-all font-bold text-slate-700" placeholder="7" />
            </div>
          </div>
          <button className="w-full py-3 mt-2 bg-slate-800 text-white font-bold rounded-lg uppercase tracking-wider hover:bg-rich-gold hover:shadow-lg hover:-translate-y-0.5 transition-all">
            Deploy Capital
          </button>
        </form>
      </div>

      <div className="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h4 className="text-lg font-bold text-slate-800 mb-5">Active Contracts</h4>
        <div className="space-y-4">
          {ACTIVE_INVESTMENTS.map((inv) => (
            <div key={inv.id} className="flex items-center justify-between p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors">
              <div className="flex items-center gap-4">
                <div className={`w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm ${inv.status === 'Paid' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600'}`}>
                  {inv.status === 'Paid' ? '✓' : '↻'}
                </div>
                <div>
                  <h5 className="font-bold text-slate-800">{inv.alias}</h5>
                  <p className="text-xs font-bold text-slate-400 uppercase tracking-wider">{inv.due}</p>
                </div>
              </div>
              <div className="text-right">
                <h5 className="font-bold text-slate-800">GHS {inv.amount}</h5>
                <p className={`text-xs font-bold tracking-wider ${inv.status === 'Paid' ? 'text-green-500' : 'text-rich-gold'}`}>
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

// 5. MAIN COMPONENT: Dashboard
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
    navigate('/');
  };

  return (
    <div className="min-h-screen w-full flex flex-col bg-slate-50 font-sans">
      
      <header className="flex justify-between items-center px-10 py-4 bg-white shadow-sm border-b-2 border-slate-200 z-10">
        <div className="logo">
          <Link to="/dashboard" className="text-ashesi-red text-2xl font-bold tracking-tight m-0 hover:text-ashesi-red-dark transition-colors text-decoration-none">
            CharleeDash+
          </Link>
        </div>

        <div className="flex bg-slate-100 rounded-full p-1 shadow-inner border border-slate-200">
          <button 
            className={`px-8 py-2.5 rounded-full font-bold text-sm tracking-wide transition-all duration-300 uppercase ${
              mode === 'borrower' 
                ? 'bg-white text-ashesi-red shadow-md' 
                : 'bg-transparent text-slate-500 hover:text-slate-700'
            }`}
            onClick={() => setMode('borrower')}
          >
            Borrow
          </button>
          <button 
            className={`px-8 py-2.5 rounded-full font-bold text-sm tracking-wide transition-all duration-300 uppercase ${
              mode === 'lender' 
                ? 'bg-white text-rich-gold shadow-md' 
                : 'bg-transparent text-slate-500 hover:text-slate-700'
            }`}
            onClick={() => setMode('lender')}
          >
            Lend
          </button>
        </div>

        <div className="flex items-center gap-4">
          <Link 
            to="/profile" 
            className="flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-50 border border-slate-100 hover:border-rich-gold hover:bg-white transition-all text-decoration-none group"
          >
            <div className="w-6 h-6 rounded-full bg-gradient-to-tr from-ashesi-red to-rich-gold flex items-center justify-center text-white font-bold text-[10px] shadow-sm">
              {alias ? alias.charAt(0).toUpperCase() : "?"}
            </div>
            <span className="font-bold text-sm text-slate-700 tracking-wide group-hover:text-rich-gold transition-colors">
              {alias || "Loading..."}
            </span>
          </Link>
          
          <button 
            onClick={handleLogout} 
            className="px-5 py-2.5 bg-transparent border-2 border-slate-200 rounded-lg text-slate-500 font-bold text-sm tracking-wide uppercase transition-all hover:bg-red-50 hover:text-red-600 hover:border-red-200"
          >
            Sign Out
          </button>
        </div>
      </header>

      <main className="flex-1 p-10 max-w-5xl mx-auto w-full mt-4">
        {mode === 'borrower' ? <BorrowerFeed /> : <LenderPortfolio />}
      </main>
    </div>
  );
}