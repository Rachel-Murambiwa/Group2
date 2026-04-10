import { Link } from 'react-router-dom';

export default function LoginView({ formData, errors, onChange, onSubmit }) {
  return (
    <div className="auth-card">
      
      {/* NEW: The Escape Hatch back to the Landing Page */}
      <div style={{ marginBottom: '20px' }}>
        <Link to="/" style={{ textDecoration: 'none', color: 'var(--text-muted)', fontSize: '14px', fontWeight: '600' }}>
          &larr; Back to Home
        </Link>
      </div>

      <div className="auth-header">
        <h2>Welcome Back</h2>
        <p>Access your Student Capital vault.</p>
      </div>

      <form onSubmit={onSubmit}>
        <div className="form-group">
          <label>Ashesi Email</label>
          <input
            type="email"
            name="email"
            className={`form-input ${errors.email ? 'error' : ''}`}
            value={formData.email}
            onChange={onChange}
            placeholder="name@ashesi.edu.gh"
            required
          />
          {errors.email && <p className="error-text">{errors.email}</p>}
        </div>

        <div className="form-group">
          <label>Password</label>
          <input
            type="password"
            name="password"
            className={`form-input ${errors.password ? 'error' : ''}`}
            value={formData.password}
            onChange={onChange}
            placeholder="••••••••"
            required
          />
          {errors.password && <p className="error-text">{errors.password}</p>}
        </div>

        <button type="submit" className="btn-primary">
          Sign In
        </button>
      </form>

      <p style={{ textAlign: 'center', marginTop: '25px', fontSize: '14px', color: 'var(--text-muted)' }}>
        Don't have an account?{' '}
        <Link to="/register" style={{ color: 'var(--ashesi-red)', fontWeight: '600', textDecoration: 'none' }}>
          Create one here
        </Link>
      </p>
    </div>
  );
}