import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

export default function AdminDashboard() {
    const navigate = useNavigate();
    const [requests, setRequests] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');
    const [actionLoading, setActionLoading] = useState(null); 

    // Authenticate and Fetch Data
    useEffect(() => {
        const storedUser = JSON.parse(localStorage.getItem('user'));
        
        // Security: Ensure only admins can see this page
        if (!storedUser || storedUser.is_admin !== 1) {
            navigate('/dashboard');
            return;
        }
        
        fetchPendingRequests();
    }, [navigate]);

    const fetchPendingRequests = async () => {
        setIsLoading(true);
        setError('');
        try {
            const token = localStorage.getItem('token');
            // UPDATED URL: Pointing to the new get_requests.php
            const response = await fetch('http://194.147.58.241:8091/vaults/get_requests.php', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            
            if (response.ok) {
                setRequests(data.requests || []);
            } else {
                setError(data.error || 'Failed to load requests.');
            }
        } catch (err) {
            setError('Cannot connect to server. Please check your connection.');
        } finally {
            setIsLoading(false);
        }
    };

    const handleAction = async (requestID, actionType) => {
        if (!window.confirm(`Are you sure you want to ${actionType} this request?`)) return;
        
        setActionLoading(requestID);
        try {
            const token = localStorage.getItem('token');
            // UPDATED URL: Pointing to the new loan_action.php
            const response = await fetch('http://194.147.58.241:8091/vaults/loan_action.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ 
                    requestID: requestID, 
                    action: actionType // 'approve' or 'reject'
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Remove the processed request from the UI list
                // Note: The key in your new SQL query is 'request_id'
                setRequests(prev => prev.filter(req => req.request_id !== requestID));
                alert(data.message || `Request ${actionType}d successfully!`);
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
        localStorage.removeItem('token');
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
                        Management Portal
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
                            <h3 className="text-xl font-bold text-slate-800">Pending Loan Applications</h3>
                            <p className="text-xs text-slate-500 mt-1 font-medium">Review and authorize capital requests from the peer network.</p>
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
                                        <th className="p-4 font-bold">Borrower</th>
                                        <th className="p-4 font-bold">Amount (GHS)</th>
                                        <th className="p-4 font-bold">Repayment</th>
                                        <th className="p-4 font-bold">Term</th>
                                        <th className="p-4 font-bold text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {requests.map((req) => (
                                        <tr key={req.request_id} className="border-b border-slate-100 hover:bg-slate-50/80 transition-colors">
                                            <td className="p-4">
                                                <span className="font-bold text-slate-700 text-sm">#{req.request_id}</span>
                                            </td>
                                            <td className="p-4">
                                                <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold">
                                                    {req.borrower_alias}
                                                </span>
                                            </td>
                                            <td className="p-4 font-black text-slate-800">
                                                {parseFloat(req.requested_amount).toFixed(2)}
                                            </td>
                                            <td className="p-4 font-bold text-green-600">
                                                {parseFloat(req.amount_to_repay).toFixed(2)}
                                            </td>
                                            <td className="p-4 text-sm font-bold text-slate-600">
                                                {req.duration} Days
                                            </td>
                                            <td className="p-4">
                                                <div className="flex justify-center gap-2">
                                                    <button 
                                                        onClick={() => handleAction(req.request_id, 'approve')}
                                                        disabled={actionLoading === req.request_id}
                                                        className="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-[10px] font-bold uppercase rounded-lg shadow-sm transition-colors disabled:opacity-50"
                                                    >
                                                        {actionLoading === req.request_id ? '...' : 'Approve'}
                                                    </button>
                                                    <button 
                                                        onClick={() => handleAction(req.request_id, 'reject')}
                                                        disabled={actionLoading === req.request_id}
                                                        className="px-4 py-2 bg-slate-100 hover:bg-red-500 hover:text-white text-slate-500 text-[10px] font-bold uppercase rounded-lg transition-colors disabled:opacity-50"
                                                    >
                                                        {actionLoading === req.request_id ? '...' : 'Reject'}
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