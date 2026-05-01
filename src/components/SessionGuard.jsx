import { useEffect, useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';

export default function SessionGuard({ children }) {
    const navigate = useNavigate();
    const location = useLocation(); // This lets us track when the user changes pages
    const [isVerifying, setIsVerifying] = useState(true);

    useEffect(() => {
        const verifyToken = async () => {
            const token = localStorage.getItem('token');

            // 1. If there's no token at all, kick them out immediately
            if (!token) {
                handleLogout();
                return;
            }

            // 2. Ping the database to check if the token is still alive
            try {
                const response = await fetch('http://194.147.58.241:8091/auth/verify_session.php', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                const data = await response.json();

                if (!response.ok || !data.valid) {
                    // Token is expired or invalid (Hacker alert or timeout!)
                    handleLogout();
                } else {
                    // Token is good, unlock the door!
                    setIsVerifying(false);
                }
            } catch (err) {
                console.error("Session verification failed", err);
                // If the connection fails, fail securely by logging them out
                handleLogout();
            }
        };

        verifyToken();
    }, [location.pathname, navigate]); // The dependency array ensures this runs on every page load/change

    const handleLogout = () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        navigate('/login');
    };

    // 3. Prevent "flashing" protected content while we verify the token
    if (isVerifying) {
        return (
            <div className="min-h-screen w-full flex items-center justify-center bg-slate-50">
                <div className="flex flex-col items-center gap-4">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-4 border-rich-gold"></div>
                    <p className="text-slate-400 text-xs font-bold uppercase tracking-widest">Securing Connection...</p>
                </div>
            </div>
        );
    }

    // 4. If verification passes, render the actual page (Dashboard, Profile, Admin)
    return children;
}