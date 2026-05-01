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

  const [comms, setComms] = useState({ borrowed: [], lent: [], pending: [] });
  const [isLoading, setIsLoading] = useState(true);

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

  // NEW: Borrower's "Mark as Paid" Logic
  const handleMarkAsPaid = async (contractId) => {
    if (!window.confirm("Confirming this will notify the lender that you have sent the MOMO. Proceed?")) return;

    try {
      const response = await fetch('http://194.147.58.241:8091/vaults/mark_as_paid.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ contract_id: contractId })
      });

      if (response.ok) {
        alert("Lender notified! They must now confirm the receipt on their end.");
        fetchProfileData(userProfile.id);
      }
    } catch (err) {
      alert("Error updating status.");
    }
  };

  // NEW: Lender's "Confirm Repayment" Logic
  const handleConfirmPayment = async (contractId) => {
    if (!window.confirm("Only click this if you have actually received the MOMO transfer. This action closes the contract and boosts the borrower's trust score!")) return;

    try {
      const response = await fetch('http://194.147.58.241:8091/vaults/confirm_repayment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ contract_id: contractId })
      });

      if (response.ok) {
        alert("Payment Confirmed! Borrower rewarded with trust points.");
        fetchProfileData(userProfile.id);
      }
    } catch (err) {
      alert("Connection error.");
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    navigate('/login');
  };

  const formatWhatsAppLink = (phone, type, alias, amount = null) => {
    const formattedPhone = phone.startsWith('0') ? '233' + phone.substring(1) : phone;
    let text = "";
    
    if (type === 'borrower') {
      text = `Hi ${alias}! I'm ${userProfile.alias} from CharleeDash. My loan request was approved. What is your MOMO number?`;
    } else if (type === 'lender') {
      text = `Hi ${alias}! I'm ${userProfile.alias} from CharleeDash. Your loan was approved. Send me your MOMO number.`;
    } else if (type === 'pending_lender') {
      text = `Hi ${alias}! I'm ${userProfile.alias} from CharleeDash. I saw you requested GHS ${amount} from my vault.`;
    }
    
    return `https://wa.me/${formattedPhone}?text=${encodeURIComponent(text)}`;
  };

  const firstLetter = userProfile.alias ? userProfile.alias.charAt(0).toUpperCase() : "?";
  const progressPercentage = (userProfile.trustScore / userProfile.nextTierAt) * 100;

  return (
    <div className="min-h-screen w-full bg-slate-50 font-sans pb-12">
      <header className="bg-white shadow-sm border-b-2 border-slate-200 px-6 md:px-10 py-5 flex justify-between items-center sticky top-0 z-20">
        <div className="flex items-center gap-4">
          <Link to="/dashboard" className="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-ashesi-red hover:text-white transition-all text-decoration-none">&larr;</Link>
          <h2 className="text-ashesi-red text-2xl font-bold tracking-tight m-0 hidden md:block">CharleeDash<span className="text-rich-gold">+</span></h2>
        </div>
        
        <div className="flex items-center gap-6">
          <button onClick={handleLogout} className="px-4 py-2 bg-transparent border-2 border-slate-200 rounded-lg text-slate-500 font-bold text-[10px] uppercase transition-all hover:bg-red-50 hover:text-red-600">Sign Out</button>
        </div>
      </header>

      <main className="max-w-4xl mx-auto px-6 mt-10 space-y-8">
        
        {/* Profile Header */}
        <div className="flex items-center gap-6 bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <div className="w-24 h-24 rounded-full bg-gradient-to-tr from-ashesi-red to-rich-gold flex items-center justify-center shadow-lg text-white text-3xl font-bold">{firstLetter}</div>
          <div>
            <p className="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Peer Alias</p>
            <h1 className="text-4xl font-bold text-slate-800">{userProfile.alias}</h1>
          </div>
        </div>

        {/* Trust Card */}
        <div className="bg-slate-800 rounded-3xl p-8 relative overflow-hidden shadow-2xl">
          <div className="absolute top-0 right-0 w-64 h-64 bg-rich-gold rounded-full filter blur-[80px] opacity-20 -translate-y-1/2 translate-x-1/3"></div>
          <div className="relative z-10 flex justify-between items-end mb-8">
            <div>
              <p className="text-slate-400 text-sm font-bold uppercase tracking-widest mb-2">Trust Tier</p>
              <h2 className="text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-rich-gold to-amber-200">{userProfile.tier}</h2>
            </div>
            <div className="text-right">
              <h3 className="text-3xl font-bold text-white">{isLoading ? "..." : userProfile.trustScore} <span className="text-lg text-slate-400 font-medium">PTS</span></h3>
            </div>
          </div>
          <div className="relative z-10">
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
          <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div className="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-xl">💰</div>
            <div>
              <p className="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Total Impact</p>
              <h4 className="text-2xl font-bold text-slate-800">GHS {isLoading ? "-" : parseFloat(userProfile.totalImpact).toFixed(2)}</h4>
            </div>
          </div>
        </div>

        {/* CONNECTIONS & DISBURSEMENTS */}
        <div className="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 className="text-xl font-bold text-slate-800 mb-6 border-b border-slate-100 pb-4">Connections & Disbursements</h3>
          
          <div className="space-y-6">
            {/* LENDER VIEW: PENDING VETTING */}
            {comms.pending?.map(req => (
              <div key={`p-${req.id}`} className="flex flex-col md:flex-row md:items-center justify-between gap-6 p-6 bg-slate-50 rounded-2xl border border-slate-200">
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-2">
                    <span className="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <p className="text-[10px] font-bold uppercase tracking-widest text-amber-600">Pending Request on Your Vault</p>
                  </div>
                  <p className="font-black text-slate-800 text-xl mb-1">{req.counterparty_alias}</p>
                </div>
                <a href={formatWhatsAppLink(req.counterparty_phone, 'pending_lender', req.counterparty_alias, req.requested_amount)} target="_blank" className="px-6 py-4 bg-slate-800 text-white rounded-xl font-bold uppercase text-xs">Vet Borrower</a>
              </div>
            ))}

            {/* BORROWER VIEW */}
            {comms.borrowed.map(contract => (
              <div key={`b-${contract.id}`} className="p-6 bg-slate-50 rounded-2xl border border-slate-200">
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <p className="text-[10px] font-bold uppercase tracking-widest text-green-600 mb-1">Lender: {contract.counterparty_alias}</p>
                    <p className="font-black text-slate-800 text-xl">GHS {parseFloat(contract.amount_to_repay).toFixed(2)}</p>
                  </div>
                  {contract.status === 'pending_confirmation' && (
                    <span className="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-[10px] font-bold">AWAITING CONFIRMATION</span>
                  )}
                </div>
                
                <div className="flex flex-col md:flex-row gap-3">
                  {contract.status !== 'pending_confirmation' && (
                    <button onClick={() => handleMarkAsPaid(contract.id)} className="flex-1 py-3 bg-slate-800 text-white rounded-xl font-bold text-xs uppercase hover:bg-rich-gold">Mark as Paid</button>
                  )}
                  <a href={formatWhatsAppLink(contract.counterparty_phone, 'borrower', contract.counterparty_alias)} target="_blank" className="flex-1 py-3 bg-[#25D366] text-white rounded-xl font-bold text-xs text-center uppercase no-underline">WhatsApp Lender</a>
                </div>
              </div>
            ))}

            {/* LENDER VIEW: ACTIVE CONTRACTS */}
            {comms.lent.map(contract => (
              <div key={`l-${contract.id}`} className={`p-6 rounded-2xl border transition-all ${contract.status === 'pending_confirmation' ? 'bg-green-50 border-green-200 scale-[1.02] shadow-md' : 'bg-slate-50 border-slate-200'}`}>
                {contract.status === 'pending_confirmation' && (
                  <div className="flex items-center gap-2 mb-3">
                    <span className="w-2 h-2 rounded-full bg-green-500 animate-ping"></span>
                    <p className="text-[10px] font-black uppercase text-green-700">BORROWER CLAIMS PAYMENT SENT</p>
                  </div>
                )}
                <p className="font-black text-slate-800 text-xl mb-1">{contract.counterparty_alias}</p>
                <div className="flex flex-col gap-3 mt-4">
                  <button onClick={() => handleConfirmPayment(contract.id)} className={`py-3 rounded-xl font-bold text-xs uppercase ${contract.status === 'pending_confirmation' ? 'bg-green-600 text-white' : 'bg-slate-800 text-white'}`}>Confirm Repayment</button>
                  <a href={formatWhatsAppLink(contract.counterparty_phone, 'lender', contract.counterparty_alias)} target="_blank" className="py-3 bg-[#25D366] text-white rounded-xl font-bold text-xs text-center uppercase no-underline">WhatsApp Borrower</a>
                </div>
              </div>
            ))}
          </div>
        </div>
      </main>
    </div>
  );
}