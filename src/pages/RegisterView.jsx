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
    <div className="auth-card">
      
      {/* NEW: The Escape Hatch back to the Landing Page */}
      <div style={{ marginBottom: '20px' }}>
        <Link to="/" style={{ textDecoration: 'none', color: 'var(--text-muted)', fontSize: '14px', fontWeight: '600' }}>
          &larr; Back to Home
        </Link>
      </div>

      <div className="auth-header">
        <h2>Student Capital</h2>
        <p>Secure peer-to-peer lending for Ashesi students.</p>
      </div>

      {!isSubmitted ? (
        <form onSubmit={onSubmit}>
          
          {formFields.map((field) => (
            <div className="form-group" key={field.name}>
              <label>{field.label}</label>
              <input
                type={field.type}
                name={field.name}
                className={`form-input ${errors[field.name] ? 'error' : ''}`}
                value={formData[field.name]}
                onChange={onChange}
                placeholder={field.placeholder}
                required
              />
              {errors[field.name] && <p className="error-text">{errors[field.name]}</p>}
            </div>
          ))}
          
          <button type="submit" className="btn-primary">
            Create Account
          </button>
        </form>
      ) : (
        <div className="success-box">
          <h3>Verification Sent</h3>
          <p>We've securely dispatched a link to <strong>{formData.email}</strong>. Please check your inbox to activate your vault.</p>
        </div>
      )}
    </div>
  );
}