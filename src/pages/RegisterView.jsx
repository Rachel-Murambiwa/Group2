import { Link } from 'react-router-dom';

export default function RegisterView({ 
  formData, 
  errors, 
  isSubmitted, 
  formFields, 
  onChange, 
  onSubmit 
}) {
  return (
    <div className="flex justify-center items-center min-h-screen bg-slate-50 px-4 py-12">
      <div className="bg-white w-full max-w-lg p-10 rounded-2xl shadow-xl border-t-4 border-ashesi-red">
        
        {/* The Escape Hatch */}
        <div className="mb-6">
          <Link to="/" className="text-sm font-semibold text-slate-500 hover:text-ashesi-red transition-colors flex items-center gap-2">
            &larr; Back to Home
          </Link>
        </div>

        <div className="text-center mb-8">
          <h2 className="text-3xl font-bold text-ashesi-red mb-2 tracking-tight">CharleeDash+</h2>
          <p className="text-slate-500 font-medium">Secure peer-to-peer lending for Ashesi students.</p>
        </div>

        {!isSubmitted ? (
          <form onSubmit={onSubmit} className="space-y-5">
            
            {formFields.map((field) => (
              <div key={field.name}>
                <label className="block text-sm font-semibold text-slate-700 uppercase tracking-wide mb-2">{field.label}</label>
                <input
                  type={field.type}
                  name={field.name}
                  className={`w-full p-4 border-2 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:border-rich-gold focus:ring-4 focus:ring-rich-gold/20 transition-all text-slate-800 ${errors[field.name] ? 'border-red-500 bg-red-50' : 'border-slate-200'}`}
                  value={formData[field.name]}
                  onChange={onChange}
                  placeholder={field.placeholder}
                  required
                />
                {errors[field.name] && <p className="text-red-500 text-xs font-bold mt-2">{errors[field.name]}</p>}
              </div>
            ))}
            
            <button type="submit" className="w-full py-4 mt-4 bg-ashesi-red text-white font-bold rounded-lg uppercase tracking-wider hover:bg-ashesi-red-dark hover:-translate-y-1 hover:shadow-lg transition-all">
              Create Account
            </button>
          </form>
        ) : (
          <div className="bg-light-gold border-2 border-rich-gold p-8 rounded-xl text-center">
            <h3 className="text-xl font-bold text-ashesi-red mb-3">Verification Sent</h3>
            <p className="text-slate-700 font-medium leading-relaxed">
              We've securely dispatched a link to <strong className="text-slate-900">{formData.email}</strong>. Please check your inbox to activate your vault.
            </p>
          </div>
        )}

        {!isSubmitted && (
          <p className="text-center mt-8 text-sm font-medium text-slate-500">
            Already have an account?{' '}
            <Link to="/login" className="text-ashesi-red font-bold hover:text-rich-gold transition-colors">
              Sign In
            </Link>
          </p>
        )}
      </div>
    </div>
  );
}