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
  const [serverOtp, setServerOtp] = useState(''); // NEW: Added to store the OTP for the WhatsApp Link[cite: 5]
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

    // UPDATED: Flexible regex to allow international country codes (7-15 digits)[cite: 5]
    const phoneRegex = /^\+?\d{7,15}$/; 
    if (!phoneRegex.test(formData.phone)) {
      newErrors.phone = 'Enter a valid number with country code (e.g., 23324XXXXXXX).';
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

    if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Passwords do not match.';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    // NORMALIZATION: Clean the phone number of "+" for database storage
    const normalizedData = {
      ...formData,
      phone: formData.phone.replace('+', '')
    };

    try {
      const response = await fetch('http://194.147.58.241:8091/auth/register_send_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(normalizedData)
      });

      const data = await response.json();
      if (response.ok) {
        // NEW: Store the OTP returned from the server to build the WhatsApp link[cite: 5]
        setServerOtp(data.otp); 
        setStep(2); 
      } else {
        setErrors({ phone: data.error || "Registration failed." });
      }
    } catch (err) {
      setErrors({ phone: "Cannot connect to server. Check your connection." });
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
      const response = await fetch('http://194.147.58.241:8091/auth/verify_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          phone: formData.phone.replace('+', ''), // Ensure clean comparison
          otp: otpString 
        })
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
      serverOtp={serverOtp} // NEW: Pass the OTP to RegisterView[cite: 5]
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