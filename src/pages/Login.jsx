import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import LoginView from './LoginView';

export default function Login() {
  const navigate = useNavigate(); 
  
  const [formData, setFormData] = useState({
    phone: '', 
    password: '',
  });
  
  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    if (errors[e.target.name]) {
      setErrors({ ...errors, [e.target.name]: '' });
    }
  };

  const handleLogin = async (e) => {
    e.preventDefault();
    const newErrors = {};

    // Domain validation check for Ghanaian numbers
    const phoneRegex = /^0\d{9}$/;
    if (!phoneRegex.test(formData.phone)) {
      newErrors.phone = 'Please enter a valid 10-digit Ghanaian number.';
    }
    
    if (!formData.password) {
      newErrors.password = 'Password is required.';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    try {
      const response = await fetch('http://194.147.58.241:8091/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (response.ok) {
        // 1. Save user session data (now including is_admin) and token
        localStorage.setItem('user', JSON.stringify(data.user));
        localStorage.setItem('token', data.token);

        // 2. SMART REDIRECT: Check the is_admin flag from the backend
        if (data.user && data.user.is_admin === 1) {
          // Send Admins directly to the management panel
          navigate('/admin');
        } else {
          // Send standard Students to the borrower/lender dashboard
          navigate('/dashboard');
        }
      } else {
        setErrors({ auth: data.error || "Login failed." });
      }
    } catch (err) {
      setErrors({ auth: "Cannot connect to server. Please try again later." });
    }
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