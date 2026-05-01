import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function Profile() {
  const navigate = useNavigate();

  const [userProfile, setUserProfile] = useState({
    id: null,
    alias: "", 
    tier: "Gold Vault",
    trustScore: 0,
    nextTierAt: 1000,
    vaultsFunded: 0,
    repaymentRate: "100%",
    totalImpact: 0
  });

  const [comms, setComms] = useState({ borrowed: [], lent: [] });
  const [isLoading, setIsLoading] = useState(true);

  // Settings State
  const [passwords, setPasswords] = useState({ current: '', new: '', confirm: '' });
  const [emailNotifs, setEmailNotifs] = useState(true);
  const [twoFactor, setTwoFactor] = useState(false);

  useEffect(() => {
    const savedUser = localStorage.getItem('user');
    if (savedUser) {
      const parsedUser = JSON.parse(savedUser);
      setUserProfile(prev => ({ ...prev, alias: parsedUser.alias, id: parsedUser.id }));
      fetchProfileData(parsedUser.id);
    } else {
      navigate('/login');
    }
  }, [navigate]);

  const fetchProfileData = async (userId) => {
    try {
      const response = await fetch(`http://194.147.58.241:8091/user/get_profile.php?user_id=${userId}`);
      const data = await response.json();
      
      if (response.ok) {
        setUserProfile(prev => ({
          ...prev,
          trustScore: data.stats.trustScore,
          vaultsFunded: data.stats.vaultsFunded,
          totalImpact: data.stats.totalImpact,
          tier: data.stats.trustScore >= 800 ? "Platinum Vault" : "Gold Vault"
        }));
        setComms(data.comms);
      }
    } catch (err) {
      console.error("Failed to fetch profile");
    } finally {
      setIsLoading(false);
    }
  };

  const handlePasswordChange = (e) => {
    e.preventDefault();
    setPasswords({ current: '', new: '', confirm: '' });
    alert("Password updated successfully!");
  };

  // Helper to format 059... into 23359... for the WhatsApp API
  const formatWhatsAppLink = (phone, type, alias) => {
    const formattedPhone = phone.startsWith('0') ? '233' + phone.substring(1) : phone;
    let text = "";
    if (type === 'borrower') {
      text = `Hi ${alias}! I'm ${userProfile.alias} from CharleeDash. I just got approved for the loan from your vault! Reaching out to coordinate.`;
    } else {
      text = `Hi ${alias}! I'm ${userProfile.alias} from CharleeDash. I saw your loan from my vault was approved! Reaching out to say hello.`;
    }
    return `https://wa.me/${formattedPhone}?text=${encodeURIComponent(text)}`;
  };

  const firstLetter = userProfile.alias ? userProfile.alias.charAt(0).toUpperCase() : "?";
  const progressPercentage = (userProfile.trustScore / userProfile.nextTierAt) * 100;

  return (
    <div className="min-h-screen w-full bg-slate-50 font-sans pb-12">
      <header className="bg-white shadow-sm border-b-2 border-slate-200 px-10 py-5 flex justify-between items-center sticky top-0 z-20">
        <div className="flex items-center gap-4">
          <Link to="/dashboard" className="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-ashesi-red hover:text-white transition-all text-decoration-none">
            &larr;
          </Link>
          <h2 className="text-ashesi-red text-2xl font-bold tracking-tight m-0">CharleeDash<span className="text-rich-gold">+</span></h2>
        </div>
        <div className="text-sm font-bold text-slate-400 uppercase tracking-wider">Identity Vault</div>
      </header>

      <main className="max-w-4xl mx-auto px-6 mt-10 space-y-8">
        
        {/* Profile Header */}
        <div className="flex items-center gap-6 bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <div className="w-24 h-24 rounded-full bg-gradient-to-tr from-ashesi-red to-rich-gold flex items-center justify-center shadow-lg text-white text-3xl font-bold">
            {firstLetter}
          </div>
          <div>
            <p className="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Peer Alias</p>
            <h1 className="text-4xl font-bold text-slate-800">{userProfile.alias}</h1>
            <p className="text-slate-500 font-medium mt-1">Active contributor to the campus economy.</p>
          </div>
        </div>

        {/* Trust Card */}
        <div className="bg-slate-800 rounded-3xl p-8 relative overflow-hidden shadow-2xl shadow-slate-800/20">
          <div className="absolute top-0 right-0 w-64 h-64 bg-rich-gold rounded-full filter blur-[80px] opacity-20 -translate-y-1/2 translate-x-1/3"></div>
          <div className="relative z-10 flex justify-between items-end mb-8">
            <div>
              <p className="text-slate-400 text-sm font-bold uppercase tracking-widest mb-2">Community Trust Tier</p>
              <h2 className="text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-rich-gold to-amber-200">{userProfile.tier}</h2>
            </div>
            <div className="text-right">
              <h3 className="text-3xl font-bold text-white">{isLoading ? "..." : userProfile.trustScore} <span className="text-lg text-slate-400 font-medium">PTS</span></h3>
            </div>
          </div>
          <div className="relative z-10">
            <div className="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
              <span>Current Progress</span>
              <span>{userProfile.nextTierAt - userProfile.trustScore} PTS to next tier</span>
            </div>
            <div className="w-full h-3 bg-slate-700 rounded-full overflow-hidden">
              <div className="h-full bg-gradient-to-r from-rich-gold to-amber-300 rounded-full transition-all duration-1000" style={{ width: `${Math.min(progressPercentage, 100)}%` }}></div>
            </div>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div className="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xl">🤝</div>
            <div>
              <p className="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Vaults Funded</p>
              <h4 className="text-2xl font-bold text-slate-800">{isLoading ? "-" : userProfile.vaultsFunded}</h4>
            </div>
          </div>
          <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div className="w-12 h-12 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-xl">✓</div>
            <div>
              <p className="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Repayment Rate</p>
              <h4 className="text-2xl font-bold text-slate-800">{userProfile.repaymentRate}</h4>
            </div>
          </div>
          <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4 border-l-4 border-l-rich-gold">
            <div className="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-xl">💰</div>
            <div>
              <p className="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Total Impact</p>
              <h4 className="text-2xl font-bold text-slate-800">GHS {isLoading ? "-" : parseFloat(userProfile.totalImpact).toFixed(2)}</h4>
            </div>
          </div>
        </div>

        {/* NEW: ACTIVE CONNECTIONS & WHATSAPP */}
        {(comms.borrowed.length > 0 || comms.lent.length > 0) && (
          <div className="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
            <h3 className="text-xl font-bold text-slate-800 mb-6 border-b border-slate-100 pb-4">Active Contracts & Comms</h3>
            
            <div className="space-y-4">
              {/* If I borrowed money, show my lenders */}
              {comms.borrowed.map(contract => (
                <div key={`b-${contract.id}`} className="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                  <div>
                    <p className="text-[10px] font-bold uppercase tracking-widest text-ashesi-red mb-1">You Owe Them</p>
                    <p className="font-bold text-slate-800 text-lg">{contract.counterparty_alias}</p>
                    <p className="text-sm font-medium text-slate-500">GHS {parseFloat(contract.amount_to_repay).toFixed(2)} due by {new Date(contract.due_date).toLocaleDateString()}</p>
                  </div>
                  <a 
                    href={formatWhatsAppLink(contract.counterparty_phone, 'borrower', contract.counterparty_alias)} 
                    target="_blank" 
                    rel="noopener noreferrer"
                    className="flex items-center gap-2 px-5 py-3 bg-[#25D366] hover:bg-[#128C7E] text-white rounded-xl font-bold transition-colors shadow-sm"
                  >
                    <span>💬</span> Chat on WhatsApp
                  </a>
                </div>
              ))}

              {/* If I lent money, show my borrowers */}
              {comms.lent.map(contract => (
                <div key={`l-${contract.id}`} className="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                  <div>
                    <p className="text-[10px] font-bold uppercase tracking-widest text-green-600 mb-1">They Owe You</p>
                    <p className="font-bold text-slate-800 text-lg">{contract.counterparty_alias}</p>
                    <p className="text-sm font-medium text-slate-500">GHS {parseFloat(contract.amount_to_repay).toFixed(2)} due by {new Date(contract.due_date).toLocaleDateString()}</p>
                  </div>
                  <a 
                    href={formatWhatsAppLink(contract.counterparty_phone, 'lender', contract.counterparty_alias)} 
                    target="_blank" 
                    rel="noopener noreferrer"
                    className="flex items-center gap-2 px-5 py-3 bg-[#25D366] hover:bg-[#128C7E] text-white rounded-xl font-bold transition-colors shadow-sm"
                  >
                    <span>💬</span> Message Borrower
                  </a>
                </div>
              ))}
            </div>
          </div>
        )}

      </main>
    </div>
  );
}