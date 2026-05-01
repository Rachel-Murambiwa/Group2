import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function AdminDashboard() {
    const navigate = useNavigate();
    const [requests, setRequests] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');
    const [actionLoading, setActionLoading] = useState(null); // Stores ID of the request being processed

    // Authenticate and Fetch Data
    useEffect(() => {
        const storedUser = JSON.parse(localStorage.getItem('user'));
        if (!storedUser) {
            navigate('/login');
            return;
        }
        
        // Note: In a real app, check if storedUser.role === 'admin' here
        fetchPendingRequests();
    }, [navigate]);

    const fetchPendingRequests = async () => {
        try {
            // Pointing to your API folder
            const response = await fetch('http://194.147.58.241:8091/api/admin/get_requests.php');
            const data = await response.json();
            
            if (response.ok) {
                setRequests(data.requests || []);
            } else {
                setError(data.error || 'Failed to load requests.');
            }
        } catch (err) {
            setError('Cannot connect to server.');
        } finally {
            setIsLoading(false);
        }
    };

    const handleAction = async (requestID, actionType) => {
        if (!window.confirm(`Are you sure you want to ${actionType} this request?`)) return;
        
        setActionLoading(requestID);
        try {
            const response = await fetch('http://194.147.58.241:8091/api/admin/loan_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    requestID: requestID, 
                    action: actionType // 'approve' or 'reject'
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Remove the processed request from the UI
                setRequests(prev => prev.filter(req => req.id !== requestID));
                alert(`Request ${actionType}d successfully!`);
            } else {
                alert(data.error || `Failed to ${actionType} request.`);
            }
        } catch (err) {
            alert('Cannot connect to server. Please try again.');
        } finally {
            setActionLoading(null);
        }
    };

    const handleSignOut = () => {
        localStorage.removeItem('user');
        navigate('/');
    };

    return (
        <div className="min-h-screen w-full bg-slate-50 font-sans flex flex-col">
            {/* Admin Header */}
            <header className="bg-slate-900 shadow-md border-b-4 border-rich-gold px-6 md:px-10 py-4 flex justify-between items-center sticky top-0 z-20">
                <div className="flex items-center gap-4">
                    <h2 className="text-white text-2xl font-black tracking-tight m-0">
                        CharleeDash<span className="text-rich-gold">+</span>
                    </h2>
                    <span className="hidden md:inline-block bg-ashesi-red text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full">
                        Admin Portal
                    </span>
                </div>
                <button 
                    onClick={handleSignOut}
                    className="px-4 py-2 bg-transparent border-2 border-slate-700 rounded-lg text-slate-300 font-bold text-[10px] uppercase transition-all hover:bg-slate-800 hover:text-white hover:border-slate-600"
                >
                    Sign Out
                </button>
            </header>

            <main className="flex-1 max-w-6xl mx-auto w-full p-6 mt-6">
                {/* Stats Row */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-amber-500">
                        <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Pending Requests</p>
                        <h4 className="text-3xl font-bold text-slate-800">{requests.length}</h4>
                    </div>
                    <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-green-500">
                        <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">System Health</p>
                        <h4 className="text-3xl font-bold text-green-600">Operational</h4>
                    </div>
                    <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-rich-gold">
                        <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Action Required</p>
                        <h4 className="text-3xl font-bold text-slate-800">{requests.length > 0 ? 'Yes' : 'No'}</h4>
                    </div>
                </div>

                {/* Data Table Section */}
                <div className="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div className="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 className="text-xl font-bold text-slate-800">Loan Applications</h3>
                            <p className="text-xs text-slate-500 mt-1 font-medium">Review and authorize pending capital requests.</p>
                        </div>
                        <button 
                            onClick={fetchPendingRequests}
                            className="text-rich-gold hover:text-amber-600 font-bold text-sm flex items-center gap-1 transition-colors"
                        >
                            ↻ Refresh
                        </button>
                    </div>

                    {isLoading ? (
                        <div className="flex justify-center items-center py-20">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-ashesi-red"></div>
                        </div>
                    ) : error ? (
                        <div className="p-10 text-center">
                            <p className="text-red-500 font-bold">{error}</p>
                        </div>
                    ) : requests.length === 0 ? (
                        <div className="p-20 text-center">
                            <div className="text-5xl mb-4">🙌</div>
                            <h4 className="text-lg font-bold text-slate-700">All caught up!</h4>
                            <p className="text-slate-500 text-sm mt-2">There are no pending loan requests at the moment.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                        <th className="p-4 font-bold">Request ID</th>
                                        <th className="p-4 font-bold">Borrower Alias</th>
                                        <th className="p-4 font-bold">Amount</th>
                                        <th className="p-4 font-bold">Duration</th>
                                        <th className="p-4 font-bold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {requests.map((req) => (
                                        <tr key={req.id} className="border-b border-slate-100 hover:bg-slate-50/80 transition-colors">
                                            <td className="p-4">
                                                <span className="font-bold text-slate-700 text-sm">#{req.id}</span>
                                            </td>
                                            <td className="p-4">
                                                <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold">
                                                    {req.borrower_alias || 'Unknown'}
                                                </span>
                                            </td>
                                            <td className="p-4 font-black text-ashesi-red">
                                                GHS {req.amount}
                                            </td>
                                            <td className="p-4 text-sm font-bold text-slate-600">
                                                {req.duration} Days
                                            </td>
                                            <td className="p-4">
                                                <div className="flex justify-center gap-2">
                                                    <button 
                                                        onClick={() => handleAction(req.id, 'approve')}
                                                        disabled={actionLoading === req.id}
                                                        className="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-bold uppercase rounded-lg shadow-sm transition-colors disabled:opacity-50"
                                                    >
                                                        {actionLoading === req.id ? '...' : 'Approve'}
                                                    </button>
                                                    <button 
                                                        onClick={() => handleAction(req.id, 'reject')}
                                                        disabled={actionLoading === req.id}
                                                        className="px-4 py-2 bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 text-xs font-bold uppercase rounded-lg transition-colors disabled:opacity-50"
                                                    >
                                                        {actionLoading === req.id ? '...' : 'Reject'}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </main>
        </div>
    );
}