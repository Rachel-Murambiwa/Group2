import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import LoginView from './LoginView';

// SOFTWARE ENGINEERING CONCEPT: Container Component
export default function Login() {
  const navigate = useNavigate(); // React Router's navigation hook
  
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });
  
  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    // Clear errors when the user starts typing
    if (errors[e.target.name]) {
      setErrors({ ...errors, [e.target.name]: '' });
    }
  };

  const handleLogin = (e) => {
    e.preventDefault();
    const newErrors = {};

    // Domain validation check
    if (!formData.email.endsWith('@ashesi.edu.gh')) {
      newErrors.email = 'Please use your official Ashesi student email.';
    }
    
    // Basic password presence check
    if (!formData.password) {
      newErrors.password = 'Password is required.';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    // In a real scenario, you would await an API call here.
    console.log("Authenticating user:", formData.email);
    
    // If successful, push them into the application dashboard!
    navigate('/dashboard');
  };

  return (
    <LoginView
      formData={formData}
      errors={errors}
      onChange={handleChange}
      onSubmit={handleLogin}
    />
  );
}