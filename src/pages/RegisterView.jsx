import { Link } from 'react-router-dom';
import courtyardImage from '../assets/courtyard.jpg';

export default function RegisterView({ 
  formData, 
  errors, 
  isSubmitted, 
  onChange, 
  onSubmit,
  onGenerateAlias
}) {

  const hasLength = formData.password.length >= 8;
  const hasUpper = /[A-Z]/.test(formData.password);
  const hasNumber = /\d/.test(formData.password);
  const hasSpecial = /[@$!%*?&#^]/.test(formData.password);
  
  const passwordsMatch = formData.password.length > 0 && formData.password === formData.confirmPassword;

  const CheckIcon = ({ active }) => (
    <svg className={`w-4 h-4 transition-colors duration-300 ${active ? 'text-green-500' : 'text-slate-300'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
    </svg>
  );

  return (
    <div 
      className="min-h-screen w-full flex justify-center items-center font-sans bg-cover bg-center bg-fixed px-4 py-12"
      style={{
        backgroundImage: `linear-gradient(rgba(138, 21, 56, 0.85), rgba(10, 10, 10, 0.8)), url('${courtyardImage}')`
      }}
    >
      <div className="w-full max-w-lg bg-white p-8 sm:p-10 rounded-2xl shadow-2xl border-t-4 border-ashesi-red relative z-10">
        
        <div className="mb-6">
          <Link to="/" className="text-sm font-bold text-slate-500 hover:text-ashesi-red transition-colors flex items-center gap-2 uppercase tracking-wider">
            &larr; Back to Home
          </Link>
        </div>

        <div className="mb-8 text-center">
          <h2 className="text-3xl font-bold text-ashesi-red mb-2 tracking-tight">Create Vault</h2>
          <p className="text-slate-500 font-medium">Secure peer-to-peer lending for Ashesi students.</p>
        </div>

        {!isSubmitted ? (
          <form onSubmit={onSubmit} className="space-y-5">
            
            {/* Real Name */}
            <div>
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Full Legal Name (Kept Private)</label>
              <input
                type="text"
                name="fullName"
                className={`w-full p-4 border-2 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:border-rich-gold transition-all text-slate-800 font-bold ${errors.fullName ? 'border-red-500' : 'border-slate-200'}`}
                value={formData.fullName}
                onChange={onChange}
                placeholder="First Last"
                required
              />
            </div>

            {/* Ashesi Email */}
            <div>
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Ashesi Email</label>
              <input
                type="email"
                name="email"
                className={`w-full p-4 border-2 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:border-rich-gold transition-all text-slate-800 font-bold ${errors.email ? 'border-red-500' : 'border-slate-200'}`}
                value={formData.email}
                onChange={onChange}
                placeholder="name@ashesi.edu.gh"
                required
              />
              {errors.email && <p className="text-red-500 text-xs font-bold mt-2">{errors.email}</p>}
            </div>

            {/* ALIAS FIELD */}
            <div className="bg-slate-100 p-4 rounded-xl border border-slate-200">
              <label className="block text-xs font-bold text-ashesi-red uppercase tracking-wide mb-1">Public Anonymous Alias</label>
              <p className="text-xs text-slate-500 mb-3 font-medium">This is the only name other students will see.</p>
              <div className="flex gap-3">
                <input
                  type="text"
                  name="alias"
                  className={`flex-1 p-3 border-2 rounded-lg bg-white focus:outline-none focus:border-rich-gold transition-all text-slate-800 font-bold ${errors.alias ? 'border-red-500' : 'border-slate-200'}`}
                  value={formData.alias}
                  onChange={onChange}
                  placeholder="e.g. Star2003"
                  required
                />
                <button 
                  type="button" 
                  onClick={onGenerateAlias}
                  className="px-4 py-3 bg-slate-800 text-white font-bold rounded-lg uppercase tracking-wider text-xs hover:bg-rich-gold transition-colors"
                >
                  Generate
                </button>
              </div>
              {errors.alias && <p className="text-red-500 text-xs font-bold mt-2">{errors.alias}</p>}
            </div>

            {/* PASSWORD FIELD */}
            <div>
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Secure Password</label>
              <input
                type="password"
                name="password"
                className={`w-full p-4 border-2 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:border-rich-gold transition-all text-slate-800 font-bold ${errors.password ? 'border-red-500' : 'border-slate-200'}`}
                value={formData.password}
                onChange={onChange}
                placeholder="••••••••"
                required
              />
              
              <div className="mt-3 grid grid-cols-2 gap-2 text-xs font-bold text-slate-500">
                <div className="flex items-center gap-2"><CheckIcon active={hasLength} /> 8+ Characters</div>
                <div className="flex items-center gap-2"><CheckIcon active={hasUpper} /> 1 Capital Letter</div>
                <div className="flex items-center gap-2"><CheckIcon active={hasNumber} /> 1 Number</div>
                <div className="flex items-center gap-2"><CheckIcon active={hasSpecial} /> 1 Special Char</div>
              </div>
              {errors.password && <p className="text-red-500 text-xs font-bold mt-2">{errors.password}</p>}
            </div>

            {/* CONFIRM PASSWORD FIELD */}
            <div>
              <label className="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1 flex justify-between">
                Confirm Password
                {passwordsMatch && <span className="text-green-500">✓ Passwords Match</span>}
              </label>
              <input
                type="password"
                name="confirmPassword"
                className={`w-full p-4 border-2 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:border-rich-gold transition-all text-slate-800 font-bold ${errors.confirmPassword ? 'border-red-500' : (passwordsMatch ? 'border-green-400' : 'border-slate-200')}`}
                value={formData.confirmPassword}
                onChange={onChange}
                placeholder="••••••••"
                required
              />
              {errors.confirmPassword && <p className="text-red-500 text-xs font-bold mt-2">{errors.confirmPassword}</p>}
            </div>
            
            <button type="submit" className="w-full py-4 mt-4 bg-ashesi-red text-white font-bold rounded-lg uppercase tracking-wider hover:bg-ashesi-red-dark hover:-translate-y-1 hover:shadow-lg transition-all">
              Create Vault Account
            </button>
          </form>
        ) : (
          <div className="bg-light-gold border-2 border-rich-gold p-8 rounded-2xl shadow-xl text-center">
            <h3 className="text-xl font-bold text-ashesi-red mb-3">Verification Sent</h3>
            <p className="text-slate-700 font-medium leading-relaxed">
              We've securely dispatched a link to <strong className="text-slate-900">{formData.email}</strong>. Please check your inbox to activate your vault.
            </p>
          </div>
        )}

        {!isSubmitted && (
          <p className="text-center mt-8 text-sm font-medium text-slate-500">
            Already have an account?{' '}
            <Link to="/login" className="text-ashesi-red font-bold hover:text-rich-gold transition-colors uppercase tracking-wider">
              Sign In
            </Link>
          </p>
        )}
      </div>
    </div>
  );
}