import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import RegisterView from './RegisterView';

export default function Register() {
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    fullName: '',
    phone: '',
    alias: '',
    password: '',
    confirmPassword: '', 
  });
  
  const [otp, setOtp] = useState(['', '', '', '', '', '']); 
  const [step, setStep] = useState(1); 
  const [errors, setErrors] = useState({});

  const handleGenerateAlias = () => {
    const prefixes = ['Star', 'Micro', 'Vault', 'Cipher', 'Ghost', 'Nova', 'Echo', 'Neon'];
    const randomPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
    const randomNumber = Math.floor(Math.random() * 900) + 100;
    setFormData({ ...formData, alias: `${randomPrefix}${randomNumber}` });
    if (errors.alias) setErrors({ ...errors, alias: '' });
  };

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    if (errors[e.target.name]) setErrors({ ...errors, [e.target.name]: '' });
  };

  const handleOtpChange = (element, index) => {
    if (isNaN(element.value)) return;
    let newOtp = [...otp];
    newOtp[index] = element.value;
    setOtp(newOtp);
    if (element.nextSibling && element.value !== "") {
      element.nextSibling.focus();
    }
  };

  const handleBackToStep1 = () => {
    setStep(1);
    setOtp(['', '', '', '', '', '']);
    setErrors({});
  };

  const handleRegister = async (e) => {
    e.preventDefault();
    const newErrors = {};

    const phoneRegex = /^0\d{9}$/;
    if (!phoneRegex.test(formData.phone)) {
      newErrors.phone = 'Please enter a valid 10-digit Ghanaian number.';
    }

    if (!formData.alias || formData.alias.length < 3) {
      newErrors.alias = 'Please enter or generate an anonymous alias.';
    }

    const hasUpper = /[A-Z]/.test(formData.password);
    const hasNumber = /\d/.test(formData.password);
    const hasSpecial = /[@$!%*?&#^]/.test(formData.password);
    
    if (formData.password.length < 8 || !hasUpper || !hasNumber || !hasSpecial) {
      newErrors.password = 'Password must be 8+ characters with uppercase, number, and symbol.';
    }

    // Passwords Match Check
    if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Passwords do not match.';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    try {
      const response = await fetch('http://localhost:8091/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });

      const data = await response.json();
      if (response.ok) {
        setStep(2); 
      } else {
        setErrors({ phone: data.error || "Registration failed." });
      }
    } catch (err) {
      setErrors({ phone: "Cannot connect to server. Is XAMPP running?" });
    }
  };

  const handleVerifyOTP = async (e) => {
    e.preventDefault();
    const otpString = otp.join('');

    if (otpString.length !== 6) {
      setErrors({ otp: "Please enter the full 6-digit code." });
      return;
    }

    try {
      const response = await fetch('http://localhost:8091/verify.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: formData.phone, otp: otpString })
      });

      if (response.ok) {
        setStep(3); // Success Screen
      } else {
        const data = await response.json();
        setErrors({ otp: data.error || "Invalid OTP code." });
      }
    } catch (err) {
      setErrors({ otp: "Connection error." });
    }
  };

  return (
    <RegisterView
      step={step}
      formData={formData}
      otp={otp}
      errors={errors}
      onChange={handleChange}
      onOtpChange={handleOtpChange}
      onSubmit={handleRegister}
      onVerify={handleVerifyOTP}
      onGenerateAlias={handleGenerateAlias} 
      onBackToStep1={handleBackToStep1}
    />
  );
}
