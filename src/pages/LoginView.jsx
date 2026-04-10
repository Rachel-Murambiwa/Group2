import { Link } from 'react-router-dom';

export default function LoginView({ formData, errors, onChange, onSubmit }) {
  return (
    <div className="flex justify-center items-center min-h-screen bg-slate-50 px-4 py-12">
      <div className="bg-white w-full max-w-md p-10 rounded-2xl shadow-xl border-t-4 border-ashesi-red">
        
        {/* The Escape Hatch */}
        <div className="mb-6">
          <Link to="/" className="text-sm font-semibold text-slate-500 hover:text-ashesi-red transition-colors flex items-center gap-2">
            &larr; Back to Home
          </Link>
        </div>

        <div className="text-center mb-8">
          <h2 className="text-3xl font-bold text-ashesi-red mb-2 tracking-tight">Welcome Back</h2>
          <p className="text-slate-500 font-medium">Access your Student Capital vault.</p>
        </div>

        <form onSubmit={onSubmit} className="space-y-5">
          <div>
            <label className="block text-sm font-semibold text-slate-700 uppercase tracking-wide mb-2">Ashesi Email</label>
            <input
              type="email"
              name="email"
              className={`w-full p-4 border-2 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:border-rich-gold focus:ring-4 focus:ring-rich-gold/20 transition-all text-slate-800 ${errors.email ? 'border-red-500 bg-red-50' : 'border-slate-200'}`}
              value={formData.email}
              onChange={onChange}
              placeholder="name@ashesi.edu.gh"
              required
            />
            {errors.email && <p className="text-red-500 text-xs font-bold mt-2">{errors.email}</p>}
          </div>

          <div>
            <label className="block text-sm font-semibold text-slate-700 uppercase tracking-wide mb-2">Password</label>
            <input
              type="password"
              name="password"
              className={`w-full p-4 border-2 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:border-rich-gold focus:ring-4 focus:ring-rich-gold/20 transition-all text-slate-800 ${errors.password ? 'border-red-500 bg-red-50' : 'border-slate-200'}`}
              value={formData.password}
              onChange={onChange}
              placeholder="••••••••"
              required
            />
            {errors.password && <p className="text-red-500 text-xs font-bold mt-2">{errors.password}</p>}
          </div>

          <button type="submit" className="w-full py-4 mt-4 bg-ashesi-red text-white font-bold rounded-lg uppercase tracking-wider hover:bg-ashesi-red-dark hover:-translate-y-1 hover:shadow-lg transition-all">
            Sign In
          </button>
        </form>

        <p className="text-center mt-8 text-sm font-medium text-slate-500">
          Don't have an account?{' '}
          <Link to="/register" className="text-ashesi-red font-bold hover:text-rich-gold transition-colors">
            Create one here
          </Link>
        </p>
      </div>
    </div>
  );
}