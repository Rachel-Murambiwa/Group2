import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

export default function AdminDashboard() {
    const navigate = useNavigate();
    const [requests, setRequests] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');
    const [actionLoading, setActionLoading] = useState(null); 
    
    // UI State for Custom Modals
    const [confirmModal, setConfirmModal] = useState({ show: false, id: null, type: '' });
    const [notification, setNotification] = useState({ show: false, message: '', isError: false });

    useEffect(() => {
        const storedUser = JSON.parse(localStorage.getItem('user'));
        if (!storedUser || storedUser.is_admin !== 1) {
            navigate('/dashboard');
            return;
        }
        fetchPendingRequests();
    }, [navigate]);

    const showNotify = (msg, isErr = false) => {
        setNotification({ show: true, message: msg, isError: isErr });
        if (!isErr) setTimeout(() => setNotification({ ...notification, show: false }), 4000);
    };

    const fetchPendingRequests = async () => {
        setIsLoading(true);
        try {
            const token = localStorage.getItem('token');
            const response = await fetch('http://194.147.58.241:8091/vaults/get_requests.php', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            if (response.ok) setRequests(data.requests || []);
            else setError(data.error);
        } catch (err) {
            setError('Cannot connect to server.');
        } finally {
            setIsLoading(false);
        }
    };

    const handleAction = async () => {
        const { id, type } = confirmModal;
        setConfirmModal({ show: false, id: null, type: '' });
        setActionLoading(id);

        try {
            const token = localStorage.getItem('token');
            const response = await fetch('http://194.147.58.241:8091/vaults/loan_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify({ requestID: id, action: type })
            });
            
            const data = await response.json();
            if (response.ok) {
                setRequests(prev => prev.filter(req => req.request_id !== id));
                showNotify(`Request #${id} successfully ${type}d.`);
            } else {
                showNotify(data.error || 'Action failed.', true);
            }
        } catch (err) {
            showNotify('Connection error. Try again.', true);
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
        <div className="min-h-screen w-full bg-slate-50 font-sans flex flex-col relative">
            
            {/* CUSTOM NOTIFICATION POPUP */}
            {notification.show && (
                <div className={`fixed top-24 right-10 z-[60] p-4 rounded-xl shadow-2xl border-l-4 animate-in slide-in-from-right duration-300 ${notification.isError ? 'bg-red-50 border-red-500 text-red-800' : 'bg-green-50 border-green-500 text-green-800'}`}>
                    <div className="flex items-center gap-3">
                        <span className="font-bold">{notification.isError ? 'Error' : 'Success'}</span>
                        <p className="text-sm font-medium">{notification.message}</p>
                        <button onClick={() => setNotification({show:false})} className="ml-4 font-bold opacity-50 hover:opacity-100">✕</button>
                    </div>
                </div>
            )}

            {/* CUSTOM CONFIRMATION MODAL */}
            {confirmModal.show && (
                <div className="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                    <div className="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl animate-in zoom-in duration-200">
                        <h3 className="text-xl font-black text-slate-800 mb-2 uppercase tracking-tight">Confirm Action</h3>
                        <p className="text-slate-500 text-sm mb-8 leading-relaxed">
                            Are you sure you want to <span className="font-bold text-slate-800">{confirmModal.type}</span> loan request <span className="font-bold text-slate-800">#{confirmModal.id}</span>? 
                            This action cannot be undone.
                        </p>
                        <div className="flex gap-3">
                            <button onClick={handleAction} className={`flex-1 py-3 rounded-xl font-bold text-white uppercase text-xs tracking-widest transition-transform hover:scale-105 ${confirmModal.type === 'approve' ? 'bg-green-500 shadow-green-200 shadow-lg' : 'bg-ashesi-red shadow-red-200 shadow-lg'}`}>
                                Yes, Proceed
                            </button>
                            <button onClick={() => setConfirmModal({show:false})} className="flex-1 py-3 bg-slate-100 text-slate-500 rounded-xl font-bold uppercase text-xs tracking-widest hover:bg-slate-200">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            )}

            <header className="bg-slate-900 shadow-md border-b-4 border-rich-gold px-6 md:px-10 py-4 flex justify-between items-center sticky top-0 z-20">
                <div className="flex items-center gap-4">
                    <h2 className="text-white text-2xl font-black tracking-tight m-0">CharleeDash<span className="text-rich-gold">+</span></h2>
                    <span className="bg-ashesi-red text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full">Admin Portal</span>
                </div>
                <button onClick={handleSignOut} className="px-4 py-2 border-2 border-slate-700 rounded-lg text-slate-300 font-bold text-[10px] uppercase hover:text-white hover:border-slate-600 transition-all">Sign Out</button>
            </header>

            <main className="flex-1 max-w-6xl mx-auto w-full p-6 mt-6">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 text-center">
                    <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-amber-500">
                        <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Pending Requests</p>
                        <h4 className="text-3xl font-bold text-slate-800">{requests.length}</h4>
                    </div>
                    <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-green-500">
                        <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">System Health</p>
                        <h4 className="text-3xl font-bold text-green-600 tracking-tight">OPERATIONAL</h4>
                    </div>
                    <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-rich-gold">
                        <p className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Status</p>
                        <h4 className="text-3xl font-bold text-slate-800">{requests.length > 0 ? 'ACTION' : 'CLEAR'}</h4>
                    </div>
                </div>

                <div className="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div className="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 className="text-xl font-bold text-slate-800">Pending Applications</h3>
                            <p className="text-xs text-slate-500 mt-1 font-medium">Verify and authorize community capital requests.</p>
                        </div>
                        <button onClick={fetchPendingRequests} className="text-rich-gold hover:text-amber-600 font-bold text-sm transition-colors">↻ Refresh</button>
                    </div>

                    {isLoading ? (
                        <div className="flex justify-center items-center py-20"><div className="animate-spin rounded-full h-12 w-12 border-b-2 border-ashesi-red"></div></div>
                    ) : error ? (
                        <div className="p-10 text-center"><p className="text-red-500 font-bold">{error}</p></div>
                    ) : requests.length === 0 ? (
                        <div className="p-20 text-center">
                            <div className="text-5xl mb-4">🙌</div>
                            <h4 className="text-lg font-bold text-slate-700">Inbox Zero</h4>
                            <p className="text-slate-500 text-sm mt-2">No pending loan requests currently.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead className="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                    <tr>
                                        <th className="p-4 font-bold">Request</th>
                                        <th className="p-4 font-bold">Borrower</th>
                                        <th className="p-4 font-bold">Principal</th>
                                        <th className="p-4 font-bold">Total Repay</th>
                                        <th className="p-4 font-bold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {requests.map((req) => (
                                        <tr key={req.request_id} className="border-b border-slate-100 hover:bg-slate-50/80 transition-colors">
                                            <td className="p-4 font-bold text-slate-700 text-sm">#{req.request_id}</td>
                                            <td className="p-4"><span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase">{req.borrower_alias}</span></td>
                                            <td className="p-4 font-black text-slate-800">GHS {parseFloat(req.requested_amount).toFixed(2)}</td>
                                            <td className="p-4 font-bold text-green-600">GHS {parseFloat(req.amount_to_repay).toFixed(2)}</td>
                                            <td className="p-4">
                                                <div className="flex justify-center gap-2">
                                                    <button onClick={() => setConfirmModal({show:true, id:req.request_id, type:'approve'})} disabled={actionLoading === req.request_id} className="px-5 py-2 bg-green-500 text-white text-[10px] font-bold uppercase rounded-lg shadow-md hover:bg-green-600 disabled:opacity-50 transition-all">{actionLoading === req.request_id ? '...' : 'Approve'}</button>
                                                    <button onClick={() => setConfirmModal({show:true, id:req.request_id, type:'reject'})} disabled={actionLoading === req.request_id} className="px-5 py-2 bg-slate-100 text-slate-500 text-[10px] font-bold uppercase rounded-lg hover:bg-ashesi-red hover:text-white disabled:opacity-50 transition-all">{actionLoading === req.request_id ? '...' : 'Reject'}</button>
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