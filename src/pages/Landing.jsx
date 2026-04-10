import { Link } from 'react-router-dom';

export default function Landing() {
  return (
    <div className="landing-wrapper">
      {/* Header / Navbar */}
      <header className="landing-header">
        <div className="logo">
          <h1>CharleeDash+</h1>
        </div>
        <nav className="header-nav">
          <Link to="/login" className="nav-link">Sign In</Link>
          <Link to="/register" className="nav-btn">Get Started</Link>
        </nav>
      </header>

      {/* Main Hero Section */}
      <main className="hero-section">
        <div className="hero-content">
          <h2 className="hero-title">Empowering the Campus Economy.</h2>
          <p className="hero-subtitle">
            A secure, anonymous peer-to-peer lending platform built exclusively for Ashesi students. Bridge the gap between stipends with trust and transparency.
          </p>
          <div className="hero-actions">
            <Link to="/register" className="btn-primary hero-btn">Create Account</Link>
            <Link to="/login" className="btn-secondary hero-btn">Access Account</Link>
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="landing-footer">
        <p>&copy; {new Date().getFullYear()} CharleeDash+. Secure campus micro-lending.</p>
      </footer>
    </div>
  );
}