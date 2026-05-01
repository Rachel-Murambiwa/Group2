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

    // UPDATED: Flexible regex to allow international numbers
    const phoneRegex = /^\+?\d{7,15}$/;
    if (!phoneRegex.test(formData.phone)) {
      newErrors.phone = 'Please enter a valid phone number with country code.';
    }
    
    if (!formData.password) {
      newErrors.password = 'Password is required.';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    // NORMALIZATION: Strip '+' for database consistency
    const normalizedData = {
      ...formData,
      phone: formData.phone.replace('+', '')
    };

    try {
      const response = await fetch('http://194.147.58.241:8091/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(normalizedData)
      });

      const data = await response.json();

      if (response.ok) {
        localStorage.setItem('user', JSON.stringify(data.user));
        localStorage.setItem('token', data.token);

        if (data.user && data.user.is_admin === 1) {
          navigate('/admin');
        } else {
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