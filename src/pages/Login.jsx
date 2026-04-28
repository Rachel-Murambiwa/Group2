import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import LoginView from './LoginView';

export default function Login() {
  const navigate = useNavigate(); 
  
  const [formData, setFormData] = useState({
    phone: '', // Changed from email
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

    // Domain validation check (Updated to Phone)
    const phoneRegex = /^0\d{9}$/;
    if (!phoneRegex.test(formData.phone)) {
      newErrors.phone = 'Please enter a valid 10-digit Ghanaian number.';
    }
    
    // Basic password presence check
    if (!formData.password) {
      newErrors.password = 'Password is required.';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    try {
      // Connect to the upcoming PHP backend
      const response = await fetch('http://localhost/StudentLendingSystem/Group2/api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (response.ok) {
        // SUCCESS: Save user session data (like their Alias) to localStorage
        localStorage.setItem('user', JSON.stringify(data.user));
        // Push them into the application dashboard!
        navigate('/dashboard');
      } else {
        // Show specific error from backend (e.g., "Invalid password" or "Account not verified")
        setErrors({ auth: data.error || "Login failed." });
      }
    } catch (err) {
      setErrors({ auth: "Cannot connect to server. Is XAMPP running?" });
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