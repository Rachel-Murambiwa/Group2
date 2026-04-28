import { Link } from 'react-router-dom';
import courtyardImage from '../assets/courtyard.jpg';

export default function LoginView({ formData, errors, onChange, onSubmit }) {
  return (
    <div 
      className="min-h-screen w-full flex justify-center items-center font-sans bg-cover bg-center bg-fixed px-4 py-12"
      style={{
        backgroundImage: `linear-gradient(rgba(138, 21, 56, 0.85), rgba(10, 10, 10, 0.8)), url('${courtyardImage}')`
      }}
    >
      <div className="w-full max-w-md bg-white p-8 sm:p-10 rounded-2xl shadow-2xl border-t-4 border-ashesi-red relative z-10">
        
        <div className="mb-6">
          <Link to="/" className="text-sm font-bold text-slate-500 hover:text-ashesi-red transition-colors flex items-center gap-2 uppercase tracking-wider">
            &larr; Back to Home
          </Link>
        </div>

        <div className="mb-8">
          <h2 className="text-3xl font-bold text-ashesi-red mb-2 tracking-tight">Welcome Back</h2>
          <p className="text-slate-500 font-medium">Access your Student Capital vault.</p>
        </div>

        {/* Global Error Message (e.g., Wrong Password, Not Verified) */}
        {errors.auth && (
          <div className="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-bold rounded">
            {errors.auth}
          </div>
        )}

        <form onSubmit={onSubmit} className="space-y-5">
          <div>
            <label className="block text-sm font-bold text-slate-600 uppercase tracking-wide mb-2">Phone Number</label>
            <input
              type="tel"
              name="phone"
              className={`w-full p-4 border-2 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:border-rich-gold focus:ring-4 focus:ring-rich-gold/20 transition-all text-slate-800 font-bold ${errors.phone ? 'border-red-500 bg-red-50' : 'border-slate-200'}`}
              value={formData.phone}
              onChange={onChange}
              placeholder="024XXXXXXX"
              required
            />
            {errors.phone && <p className="text-red-500 text-xs font-bold mt-2">{errors.phone}</p>}
          </div>

          <div>
            <label className="block text-sm font-bold text-slate-600 uppercase tracking-wide mb-2">Password</label>
            <input
              type="password"
              name="password"
              className={`w-full p-4 border-2 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:border-rich-gold focus:ring-4 focus:ring-rich-gold/20 transition-all text-slate-800 font-bold ${errors.password ? 'border-red-500 bg-red-50' : 'border-slate-200'}`}
              value={formData.password}
              onChange={onChange}
              placeholder="••••••••"
              required
            />
            {errors.password && <p className="text-red-500 text-xs font-bold mt-2">{errors.password}</p>}
          </div>

          <button type="submit" className="w-full py-4 mt-6 bg-ashesi-red text-white font-bold rounded-lg uppercase tracking-wider hover:bg-ashesi-red-dark hover:-translate-y-1 hover:shadow-lg transition-all">
            Sign In
          </button>
        </form>

        <p className="text-center mt-8 text-sm font-medium text-slate-500">
          Don't have an account?{' '}
          <Link to="/register" className="text-ashesi-red font-bold hover:text-rich-gold transition-colors uppercase tracking-wider">
            Create one here
          </Link>
        </p>
      </div>
    </div>
  );
}