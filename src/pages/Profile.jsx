import { useState } from 'react';
import { Link } from 'react-router-dom';

export default function Profile() {
  // Dummy data for the profile
  const userProfile = {
    alias: "Star2003",
    tier: "Gold Vault",
    trustScore: 850,
    nextTierAt: 1000,
    vaultsFunded: 12,
    repaymentRate: "100%",
    totalImpact: "GHS 4,500"
  };

  const progressPercentage = (userProfile.trustScore / userProfile.nextTierAt) * 100;

  // NEW: State for our settings panel
  const [passwords, setPasswords] = useState({ current: '', new: '', confirm: '' });
  const [emailNotifs, setEmailNotifs] = useState(true);
  const [twoFactor, setTwoFactor] = useState(false);

  const handlePasswordChange = (e) => {
    e.preventDefault();
    console.log("Updating password...");
    // Reset fields after "saving"
    setPasswords({ current: '', new: '', confirm: '' });
    alert("Password updated successfully!");
  };

  return (
    <div className="min-h-screen w-full bg-slate-50 font-sans pb-12">
      
      {/* Premium Header Bar */}
      <header className="bg-white shadow-sm border-b-2 border-slate-200 px-10 py-5 flex justify-between items-center sticky top-0 z-20">
        <div className="flex items-center gap-4">
          <Link to="/dashboard" className="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-ashesi-red hover:text-white transition-all text-decoration-none">
            &larr;
          </Link>
          <h2 className="text-ashesi-red text-2xl font-bold tracking-tight m-0">CharleeDash+</h2>
        </div>
        <div className="text-sm font-bold text-slate-400 uppercase tracking-wider">
          Identity Vault
        </div>
      </header>

      <main className="max-w-4xl mx-auto px-6 mt-10 space-y-8">
        
        {/* The Identity Header */}
        <div className="flex items-center gap-6 bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <div className="w-24 h-24 rounded-full bg-gradient-to-tr from-ashesi-red to-rich-gold flex items-center justify-center shadow-lg text-white text-3xl font-bold">
            {userProfile.alias.charAt(0)}
          </div>
          <div>
            <p className="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Anonymous Alias</p>
            <h1 className="text-4xl font-bold text-slate-800">{userProfile.alias}</h1>
            <p className="text-slate-500 font-medium mt-1">Active contributor to the campus economy.</p>
          </div>
        </div>

        {/* The "Wow Factor" Trust Card */}
        <div className="bg-slate-800 rounded-3xl p-8 relative overflow-hidden shadow-2xl shadow-slate-800/20">
          <div className="absolute top-0 right-0 w-64 h-64 bg-rich-gold rounded-full filter blur-[80px] opacity-20 -translate-y-1/2 translate-x-1/3"></div>
          
          <div className="relative z-10 flex justify-between items-end mb-8">
            <div>
              <p className="text-slate-400 text-sm font-bold uppercase tracking-widest mb-2">Community Trust Tier</p>
              <h2 className="text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-rich-gold to-amber-200 drop-shadow-sm">
                {userProfile.tier}
              </h2>
            </div>
            <div className="text-right">
              <h3 className="text-3xl font-bold text-white">{userProfile.trustScore} <span className="text-lg text-slate-400 font-medium">PTS</span></h3>
            </div>
          </div>

          <div className="relative z-10">
            <div className="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
              <span>Current Progress</span>
              <span>{userProfile.nextTierAt - userProfile.trustScore} PTS to Platinum</span>
            </div>
            <div className="w-full h-3 bg-slate-700 rounded-full overflow-hidden">
              <div 
                className="h-full bg-gradient-to-r from-rich-gold to-amber-300 rounded-full transition-all duration-1000 ease-out relative"
                style={{ width: `${progressPercentage}%` }}
              >
                <div className="absolute top-0 right-0 bottom-0 w-10 bg-white/30 blur-sm"></div>
              </div>
            </div>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div className="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xl">🤝</div>
            <div>
              <p className="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Vaults Funded</p>
              <h4 className="text-2xl font-bold text-slate-800">{userProfile.vaultsFunded}</h4>
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
              <h4 className="text-2xl font-bold text-slate-800">{userProfile.totalImpact}</h4>
            </div>
          </div>
        </div>

        {/* NEW: Security & Preferences Section */}
        <div className="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 className="text-xl font-bold text-slate-800 mb-6 border-b border-slate-100 pb-4">Security & Preferences</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
            
            {/* Left Side: Change Password Form */}
            <div>
              <h4 className="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Update Password</h4>
              <form onSubmit={handlePasswordChange} className="space-y-4">
                <div>
                  <label className="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Current Password</label>
                  <input 
                    type="password" 
                    className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 focus:bg-white focus:border-rich-gold outline-none transition-all font-bold text-slate-700" 
                    value={passwords.current}
                    onChange={(e) => setPasswords({...passwords, current: e.target.value})}
                    required
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">New Password</label>
                  <input 
                    type="password" 
                    className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 focus:bg-white focus:border-rich-gold outline-none transition-all font-bold text-slate-700" 
                    value={passwords.new}
                    onChange={(e) => setPasswords({...passwords, new: e.target.value})}
                    required
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Confirm New Password</label>
                  <input 
                    type="password" 
                    className="w-full p-3 border-2 border-slate-100 rounded-lg bg-slate-50 focus:bg-white focus:border-rich-gold outline-none transition-all font-bold text-slate-700" 
                    value={passwords.confirm}
                    onChange={(e) => setPasswords({...passwords, confirm: e.target.value})}
                    required
                  />
                </div>
                <button type="submit" className="w-full py-3 bg-slate-800 text-white font-bold rounded-lg uppercase tracking-wider hover:bg-rich-gold transition-colors mt-2 text-sm">
                  Save Password
                </button>
              </form>
            </div>

            {/* Right Side: Toggles for "Etc" */}
            <div>
              <h4 className="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Account Settings</h4>
              <div className="space-y-6">
                
                {/* Email Notifications Toggle */}
                <div className="flex items-center justify-between">
                  <div>
                    <p className="font-bold text-slate-800">Email Notifications</p>
                    <p className="text-xs font-medium text-slate-400 mt-1">Get alerts for new vaults and due dates.</p>
                  </div>
                  <button 
                    onClick={() => setEmailNotifs(!emailNotifs)}
                    className={`w-12 h-6 rounded-full transition-colors relative ${emailNotifs ? 'bg-green-500' : 'bg-slate-300'}`}
                  >
                    <div className={`w-4 h-4 bg-white rounded-full absolute top-1 transition-all ${emailNotifs ? 'left-7' : 'left-1'}`}></div>
                  </button>
                </div>

                {/* Two-Factor Auth Toggle */}
                <div className="flex items-center justify-between">
                  <div>
                    <p className="font-bold text-slate-800">Two-Factor Auth (2FA)</p>
                    <p className="text-xs font-medium text-slate-400 mt-1">Require an email code to withdraw funds.</p>
                  </div>
                  <button 
                    onClick={() => setTwoFactor(!twoFactor)}
                    className={`w-12 h-6 rounded-full transition-colors relative ${twoFactor ? 'bg-green-500' : 'bg-slate-300'}`}
                  >
                    <div className={`w-4 h-4 bg-white rounded-full absolute top-1 transition-all ${twoFactor ? 'left-7' : 'left-1'}`}></div>
                  </button>
                </div>

              </div>
            </div>

          </div>
        </div>

      </main>
    </div>
  );
}