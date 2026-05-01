import { useState } from 'react';

export default function LoanRequestModal({ isOpen, onClose, vault }) {
  const [purpose, setPurpose] = useState('');
  const [agreed, setAgreed] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [successMsg, setSuccessMsg] = useState('');

  if (!isOpen || !vault) return null;

  // Calculate the total repayment based on the FIXED vault amount
  const calculateTotal = () => {
    const principal = parseFloat(vault.amount);
    const interest = principal * (vault.interest / 100);
    return (principal + interest).toFixed(2);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMsg('');
    setSuccessMsg('');

    const savedUser = localStorage.getItem('user');
    const user = savedUser ? JSON.parse(savedUser) : null;
    const token = localStorage.getItem('token');

    if (!user || (!user.id && !user.userID)) {
      setErrorMsg("Please log in again to request funds.");
      return;
    }

    setIsLoading(true);

    try {
      // NOTE: Using absolute URL without /api/ to match your Docker context
      const response = await fetch('http://194.147.58.241:8091/vaults/request_funds.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          vaultID: vault.id,
          borrowerID: user.id || user.userID
          // Note: 'purpose' is kept for UI/UX feel, but the backend doesn't require it currently.
        })
      });

      const data = await response.json();

      if (response.ok) {
        setSuccessMsg(data.message || "Request submitted successfully!");
        // Wait 1.5 seconds so the user can read the success message before closing
        setTimeout(() => {
          onClose();
          // Reload the page to refresh the feed so the requested vault disappears
          window.location.reload(); 
        }, 1500);
      } else {
        setErrorMsg(data.error || "Failed to submit request.");
      }
    } catch (error) {
      setErrorMsg("Cannot connect to server. Please try again.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      {/* Blurred Backdrop */}
      <div 
        className="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
        onClick={onClose}
      ></div>

      {/* Modal Content */}
      <div className="bg-white rounded-3xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden animate-in fade-in zoom-in duration-200">
        
        {/* Header Ribbon */}
        <div className="bg-gradient-to-r from-ashesi-red to-rich-gold h-2 w-full"></div>

        <div className="p-8">
          <div className="flex justify-between items-start mb-6">
            <div>
              <h2 className="text-2xl font-black text-slate-800 tracking-tight">Request Funds</h2>
              <p className="text-sm font-medium text-slate-500 mt-1">
                From <span className="font-bold text-slate-700">{vault.alias}</span>
              </p>
            </div>
            <button 
              onClick={onClose}
              disabled={isLoading}
              className="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors font-bold disabled:opacity-50"
            >
              ✕
            </button>
          </div>

          {/* Vault Constraints Banner */}
          <div className="bg-amber-50 border border-amber-100 rounded-xl p-4 mb-6 flex justify-between items-center">
            <div>
              <p className="text-[10px] font-bold text-amber-600 uppercase tracking-widest mb-1">Available Limit</p>
              <p className="text-lg font-bold text-slate-800">GHS {vault.amount}</p>
            </div>
            <div className="text-right">
              <p className="text-[10px] font-bold text-amber-600 uppercase tracking-widest mb-1">Interest & Term</p>
              <p className="text-sm font-bold text-slate-800">{vault.interest}% • {vault.duration} Days</p>
            </div>
          </div>

          <form onSubmit={handleSubmit} className="space-y-6">
            
            <div>
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Request Amount (GHS)</label>
              <input 
                type="number" 
                readOnly
                value={vault.amount}
                className="w-full p-4 border-2 border-slate-100 rounded-xl bg-slate-100 text-slate-500 font-bold text-lg cursor-not-allowed"
              />
              <p className="text-[10px] text-slate-400 mt-1 font-bold tracking-wide">Vaults must be requested in full.</p>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Brief Purpose</label>
              <input 
                type="text" 
                required
                value={purpose}
                onChange={(e) => setPurpose(e.target.value)}
                className="w-full p-4 border-2 border-slate-100 rounded-xl bg-slate-50 focus:bg-white focus:border-rich-gold focus:ring-4 focus:ring-rich-gold/20 outline-none transition-all font-medium text-slate-700"
                placeholder="e.g., Textbooks, Groceries, Project supplies"
              />
            </div>

            {/* Repayment Summary */}
            <div className="bg-slate-50 rounded-xl p-5 border border-slate-200">
              <div className="flex justify-between items-center mb-2">
                <span className="text-sm font-bold text-slate-500">Total Repayment:</span>
                <span className="text-xl font-black text-ashesi-red">GHS {calculateTotal()}</span>
              </div>
              <p className="text-xs text-slate-400 font-medium">
                Due {vault.duration} days after admin approval.
              </p>
            </div>

            <label className="flex items-start gap-3 cursor-pointer group">
              <div className="relative flex items-center justify-center mt-0.5">
                <input 
                  type="checkbox" 
                  required
                  checked={agreed}
                  onChange={(e) => setAgreed(e.target.checked)}
                  className="peer appearance-none w-5 h-5 border-2 border-slate-300 rounded focus:ring-2 focus:ring-rich-gold/30 checked:bg-rich-gold checked:border-rich-gold transition-all"
                />
                <svg className="absolute w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="3">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <span className="text-xs font-medium text-slate-500 leading-relaxed group-hover:text-slate-700 transition-colors">
                I agree to the CharleeDash+ peer lending terms and commit to repaying the total amount within the specified timeframe to protect my Community Trust Tier.
              </span>
            </label>

            {errorMsg && <p className="text-sm font-bold text-red-600 text-center">{errorMsg}</p>}
            {successMsg && <p className="text-sm font-bold text-green-600 text-center">{successMsg}</p>}

            <button 
              type="submit" 
              disabled={isLoading || successMsg !== ''}
              className="w-full py-4 bg-slate-800 text-white font-bold rounded-xl uppercase tracking-wider hover:bg-rich-gold hover:-translate-y-1 hover:shadow-lg transition-all disabled:opacity-60 disabled:hover:translate-y-0"
            >
              {isLoading ? 'Submitting...' : 'Submit Request'}
            </button>
          </form>

        </div>
      </div>
    </div>
  );
}