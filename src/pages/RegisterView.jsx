import { Link, useNavigate } from 'react-router-dom';

export default function RegisterView({ 
  step, formData, otp, errors, onChange, onOtpChange, onSubmit, onVerify, onGenerateAlias, onBackToStep1 
}) {
  const navigate = useNavigate();

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-6 lg:px-8 font-sans relative">
      
      {/* Back to Home Link */}
      <div className="absolute top-8 left-8">
        <Link to="/" className="flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-ashesi-red transition-all">
          <span>&larr;</span> BACK TO HOME
        </Link>
      </div>

      <div className="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <h2 className="mt-6 text-center text-3xl font-extrabold text-ashesi-red">
          CharleeDash+
        </h2>
        <p className="mt-2 text-center text-sm text-slate-600">
          {step === 1 ? "Secure peer-to-peer lending for everyone." : 
           step === 2 ? "Verification required." : "Account Secured."}
        </p>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div className="bg-white py-8 px-6 shadow-xl border border-slate-100 rounded-3xl sm:px-10">
          
          {/* STEP 1: REGISTRATION FORM */}
          {step === 1 ? (
            <form className="space-y-5" onSubmit={onSubmit}>
              <div>
                <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider">Full Name</label>
                <input
                  name="fullName"
                  type="text"
                  required
                  value={formData.fullName}
                  onChange={onChange}
                  className="mt-1 block w-full px-4 py-3 border-2 border-slate-100 rounded-xl focus:ring-0 focus:border-rich-gold bg-slate-50 focus:bg-white transition-colors font-medium text-slate-800"
                  placeholder="Kwame Mensah"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider">Phone Number</label>
                <input
                  name="phone"
                  type="tel"
                  required
                  value={formData.phone}
                  onChange={onChange}
                  className="mt-1 block w-full px-4 py-3 border-2 border-slate-100 rounded-xl focus:ring-0 focus:border-rich-gold bg-slate-50 focus:bg-white transition-colors font-medium text-slate-800"
                  placeholder="024XXXXXXX"
                />
                {errors.phone && <p className="mt-2 text-xs text-red-500 font-bold">{errors.phone}</p>}
              </div>

              <div>
                <div className="flex justify-between items-center">
                  <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider">Anonymous Alias</label>
                  <button type="button" onClick={onGenerateAlias} className="text-xs font-bold text-ashesi-red hover:text-rich-gold transition-colors">
                    Generate Random
                  </button>
                </div>
                <input
                  name="alias"
                  type="text"
                  required
                  value={formData.alias}
                  onChange={onChange}
                  className="mt-1 block w-full px-4 py-3 border-2 border-slate-100 rounded-xl focus:ring-0 focus:border-rich-gold bg-slate-50 focus:bg-white transition-colors font-medium text-slate-800"
                  placeholder="StarVault402"
                />
                {errors.alias && <p className="mt-2 text-xs text-red-500 font-bold">{errors.alias}</p>}
              </div>

              <div className="space-y-4">
                <div>
                  <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider">Create Password</label>
                  <input
                    name="password"
                    type="password"
                    required
                    value={formData.password}
                    onChange={onChange}
                    className="mt-1 block w-full px-4 py-3 border-2 border-slate-100 rounded-xl focus:ring-0 focus:border-rich-gold bg-slate-50 focus:bg-white transition-colors font-medium text-slate-800"
                  />
                  <div className="mt-2 grid grid-cols-2 gap-2">
                    <p className={`text-[10px] font-bold uppercase ${formData.password.length >= 8 ? 'text-green-500' : 'text-slate-400'}`}>● 8+ Characters</p>
                    <p className={`text-[10px] font-bold uppercase ${/[A-Z]/.test(formData.password) ? 'text-green-500' : 'text-slate-400'}`}>● 1 Uppercase</p>
                    <p className={`text-[10px] font-bold uppercase ${/\d/.test(formData.password) ? 'text-green-500' : 'text-slate-400'}`}>● 1 Number</p>
                    <p className={`text-[10px] font-bold uppercase ${/[@$!%*?&#^]/.test(formData.password) ? 'text-green-500' : 'text-slate-400'}`}>● 1 Special Char</p>
                  </div>
                  {errors.password && <p className="mt-2 text-xs text-red-500 font-bold">{errors.password}</p>}
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider">Confirm Password</label>
                  <input
                    name="confirmPassword"
                    type="password"
                    required
                    value={formData.confirmPassword}
                    onChange={onChange}
                    className="mt-1 block w-full px-4 py-3 border-2 border-slate-100 rounded-xl focus:ring-0 focus:border-rich-gold bg-slate-50 focus:bg-white transition-colors font-medium text-slate-800"
                  />
                  {/* Real-time Match Check */}
                  {formData.confirmPassword && (
                    <p className={`mt-1 text-[10px] font-bold uppercase ${formData.password === formData.confirmPassword ? 'text-green-500' : 'text-red-500'}`}>
                      {formData.password === formData.confirmPassword ? '✓ Passwords Match' : '✗ Passwords Do Not Match'}
                    </p>
                  )}
                  {errors.confirmPassword && <p className="mt-2 text-xs text-red-500 font-bold">{errors.confirmPassword}</p>}
                </div>
              </div>

              <button
                type="submit"
                className="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-slate-800 hover:bg-rich-gold transition-all focus:outline-none"
              >
                Create Vault Account
              </button>
            </form>

          ) : step === 2 ? (

            /* STEP 2: OTP VERIFICATION SCREEN */
            <form className="space-y-6" onSubmit={onVerify}>
              <div className="text-center">
                <p className="text-sm text-slate-600 font-medium leading-relaxed">
                  We've sent a 6-digit security code to <br/>
                  <span className="font-bold text-slate-800 text-base">{formData.phone}</span>
                </p>
                <button 
                  type="button" 
                  onClick={() => onBackToStep1()} 
                  className="mt-1 text-[10px] font-bold text-ashesi-red uppercase tracking-widest hover:underline"
                >
                  Change Number?
                </button>
              </div>

              <div className="flex justify-center gap-2 mt-6">
                {otp.map((data, index) => (
                  <input
                    className="w-12 h-14 text-center text-xl font-bold text-slate-800 bg-slate-50 border-2 border-slate-200 rounded-lg focus:border-rich-gold focus:bg-white focus:outline-none transition-all"
                    type="text"
                    maxLength="1"
                    key={index}
                    value={data}
                    onChange={e => onOtpChange(e.target, index)}
                    onFocus={e => e.target.select()}
                  />
                ))}
              </div>
              
              {errors.otp && <p className="text-center mt-2 text-xs text-red-500 font-bold">{errors.otp}</p>}

              <button
                type="submit"
                className="w-full mt-6 flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-rich-gold hover:bg-amber-600 transition-all focus:outline-none"
              >
                Verify & Finalize
              </button>
              
              <div className="text-center mt-4">
                <button type="button" className="text-xs font-bold text-slate-400 hover:text-ashesi-red transition-colors">
                  Didn't receive a code? Resend SMS
                </button>
              </div>
            </form>

          ) : (

            /* STEP 3: SUCCESS MESSAGE (NO POP-UPS) */
            <div className="text-center py-6 space-y-6 animate-in fade-in zoom-in duration-300">
              <div className="flex justify-center">
                <div className="h-24 w-24 bg-green-50 rounded-full flex items-center justify-center border-4 border-white shadow-inner">
                  <span className="text-5xl">🛡️</span>
                </div>
              </div>
              
              <div>
                <h3 className="text-2xl font-black text-slate-800">Vault Activated</h3>
                <p className="mt-3 text-sm text-slate-600 leading-relaxed">
                  Congratulations, <span className="font-bold text-ashesi-red">{formData.alias}</span>! 
                  Your account is verified and your identity is now protected.
                </p>
              </div>

              <button
                onClick={() => navigate('/login')}
                className="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-slate-800 hover:bg-rich-gold transition-all transform active:scale-95"
              >
                Sign In to Your Vault
              </button>
            </div>
          )}

          {step === 1 && (
            <div className="mt-6 text-center">
              <p className="text-sm text-slate-600">
                Already have a vault?{' '}
                <Link to="/login" className="font-bold text-ashesi-red hover:text-rich-gold transition-colors">
                  Sign in
                </Link>
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}