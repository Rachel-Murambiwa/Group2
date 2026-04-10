import { useState } from 'react';
import RegisterView from './RegisterView';

export default function Register() {
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    alias: '',
    password: '',
    confirmPassword: '', // <-- NEW: Tracking the confirmation
  });
  
  const [errors, setErrors] = useState({});
  const [isSubmitted, setIsSubmitted] = useState(false);

  const handleGenerateAlias = () => {
    const prefixes = ['Star', 'Micro', 'Vault', 'Cipher', 'Ghost', 'Nova', 'Echo', 'Neon'];
    const randomPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
    const randomNumber = Math.floor(Math.random() * 900) + 100;
    
    setFormData({ ...formData, alias: `${randomPrefix}${randomNumber}` });
    
    if (errors.alias) {
      setErrors({ ...errors, alias: '' });
    }
  };

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
      newErrors.email = 'You must use an official Ashesi email.';
    }
    if (!formData.alias || formData.alias.length < 3) {
      newErrors.alias = 'Please enter or generate an anonymous alias.';
    }

    // NEW: Strict Password Validation Check
    const hasUpper = /[A-Z]/.test(formData.password);
    const hasNumber = /\d/.test(formData.password);
    const hasSpecial = /[@$!%*?&#^]/.test(formData.password);
    
    if (formData.password.length < 8 || !hasUpper || !hasNumber || !hasSpecial) {
      newErrors.password = 'Please ensure your password meets all security requirements.';
    }

    // NEW: Match Check
    if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Passwords do not match.';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    console.log("Registering user:", formData);
    setIsSubmitted(true);
  };

  return (
    <RegisterView
      formData={formData}
      errors={errors}
      isSubmitted={isSubmitted}
      onChange={handleChange}
      onSubmit={handleRegister}
      onGenerateAlias={handleGenerateAlias} 
    />
  );
}