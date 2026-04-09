import { useState } from 'react';
import RegisterView from './RegisterView'; // Import the dumb View component

// SOFTWARE ENGINEERING CONCEPT: Container Component (The Logic)
// This component handles state, validation, and API calls, maintaining Separation of Concerns.
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
    
    // API Call to the actual Backend will go here
    console.log("Valid registration data sent to server:", formData);
  };

  const formFields = [
    { name: 'fullName', label: 'Full Name', type: 'text', placeholder: 'Enter your legal name' },
    { name: 'studentId', label: 'Student ID', type: 'text', placeholder: 'e.g., 12342026' },
    { name: 'email', label: 'Ashesi Email', type: 'email', placeholder: 'name@ashesi.edu.gh' },
    { name: 'password', label: 'Password', type: 'password', placeholder: '••••••••' },
    { name: 'confirmPassword', label: 'Confirm Password', type: 'password', placeholder: '••••••••' }
  ];

  // Pass all the logic down to the View as props
  return (
    <RegisterView 
      formData={formData}
      errors={errors}
      isSubmitted={isSubmitted}
      formFields={formFields}
      onChange={handleChange}
      onSubmit={handleRegister}
    />
  );
}