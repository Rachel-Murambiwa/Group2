import { useState } from 'react';

export default function Register() {
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    studentId: '',
    password: '',
    confirmPassword: '',
  });
  
  const [errors, setErrors] = useState({});
  const [isSubmitted, setIsSubmitted] = useState(false);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    if (errors[e.target.name]) {
      setErrors({ ...errors, [e.target.name]: '' });
    }
  };

  const handleRegister = (e) => {
    e.preventDefault();
    const newErrors = {};

    if (!formData.email.endsWith('@ashesi.edu.gh')) {
      newErrors.email = 'Only Ashesi University emails are allowed';
    }
    if (formData.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters';
    }
    if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Passwords do not match';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    setErrors({});
    setIsSubmitted(true);
    console.log("Valid registration data sent to server:", formData);
  };

  return (
    <div className="auth-card">
      <div className="auth-header">
        <h2>Student Capital</h2>
        <p>Secure peer-to-peer lending for Ashesi students.</p>
      </div>

      {!isSubmitted ? (
        <form onSubmit={handleRegister}>
          
          <div className="form-group">
            <label>Full Name</label>
            <input
              type="text"
              name="fullName"
              className="form-input"
              value={formData.fullName}
              onChange={handleChange}
              placeholder="Enter your legal name"
              required
            />
          </div>

          <div className="form-group">
            <label>Student ID</label>
            <input
              type="text"
              name="studentId"
              className="form-input"
              value={formData.studentId}
              onChange={handleChange}
              placeholder="e.g., 12342026"
              required
            />
          </div>

          <div className="form-group">
            <label>Ashesi Email</label>
            <input
              type="email"
              name="email"
              className={`form-input ${errors.email ? 'error' : ''}`}
              value={formData.email}
              onChange={handleChange}
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
              onChange={handleChange}
              placeholder="••••••••"
              required
            />
            {errors.password && <p className="error-text">{errors.password}</p>}
          </div>

          <div className="form-group">
            <label>Confirm Password</label>
            <input
              type="password"
              name="confirmPassword"
              className={`form-input ${errors.confirmPassword ? 'error' : ''}`}
              value={formData.confirmPassword}
              onChange={handleChange}
              placeholder="••••••••"
              required
            />
            {errors.confirmPassword && <p className="error-text">{errors.confirmPassword}</p>}
          </div>
          
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