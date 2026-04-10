import { Link } from 'react-router-dom';

export default function Landing() {
  return (
    <div className="min-h-screen w-full flex flex-col font-sans">
      {/* Header / Navbar */}
      <header className="flex justify-between items-center px-10 py-5 bg-white shadow-md z-10">
        <div className="logo">
          <h1 className="text-ashesi-red text-2xl font-bold tracking-tight m-0">CharleeDash+</h1>
        </div>
        <nav className="flex items-center gap-6">
          <Link to="/login" className="text-slate-700 font-semibold hover:text-rich-gold transition-colors text-decoration-none">
            Sign In
          </Link>
          <Link to="/register" className="bg-light-gold text-ashesi-red px-5 py-2 rounded-lg font-semibold border border-rich-gold hover:bg-rich-gold hover:text-white transition-all text-decoration-none">
            Get Started
          </Link>
        </nav>
      </header>

      {/* Main Hero Section */}
      <main 
        className="flex-1 flex justify-center items-center text-center px-5 bg-cover bg-center bg-fixed"
        style={{
          backgroundImage: `linear-gradient(rgba(138, 21, 56, 0.85), rgba(10, 10, 10, 0.8)), url('https://upload.wikimedia.org/wikipedia/commons/5/5f/Ashesi%27s_Archer_Cornfield_Courtyard.jpg')`
        }}
      >
        <div className="max-w-3xl">
          <h2 className="text-5xl text-white mb-6 leading-tight drop-shadow-lg font-bold">
            Empowering the Campus Economy.
          </h2>
          <p className="text-xl text-light-gold mb-10 leading-relaxed">
            A secure, anonymous peer-to-peer lending platform built exclusively for Ashesi students. Bridge the gap between stipends with trust and transparency.
          </p>
          <div className="flex justify-center gap-5">
            <Link to="/register" className="inline-block px-8 py-4 bg-ashesi-red text-white rounded-lg font-bold hover:bg-ashesi-red-dark hover:-translate-y-1 hover:shadow-[0_10px_20px_rgba(138,21,56,0.4)] transition-all uppercase tracking-wide text-decoration-none">
              Create Account
            </Link>
            <Link to="/login" className="inline-block px-8 py-4 bg-white text-ashesi-red border-2 border-ashesi-red rounded-lg font-bold hover:bg-ashesi-red hover:text-white hover:shadow-lg transition-all uppercase tracking-wide text-decoration-none">
              Access Account
            </Link>
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="text-center py-5 bg-white text-slate-500 text-sm border-t border-slate-100">
        <p className="m-0">&copy; {new Date().getFullYear()} CharleeDash+. Secure campus micro-lending.</p>
      </footer>
    </div>
  );
}