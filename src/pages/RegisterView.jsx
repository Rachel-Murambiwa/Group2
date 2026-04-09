// SOFTWARE ENGINEERING CONCEPT: Presentational Component (The View)
// This component ONLY cares about UI. It receives all its data and functions via props.

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