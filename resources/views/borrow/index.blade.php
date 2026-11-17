@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    Borrow Requests 
                    <span id="role-badge" class="ml-4 px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-xs font-bold uppercase">Loading Role...</span>
                </h2>
                <p class="text-sm text-gray-500">Submit item borrow requests and manage pending approvals</p>
            </div>
            <button onclick="toggleRequestForm()" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-md shadow">
                + New Borrow Request
            </button>
        </div>

        <div id="alert-box" class="hidden mb-4 p-3 rounded-md text-sm"></div>

        <div id="request-form-box" class="hidden mb-8 bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="text-lg font-bold text-gray-700 mb-4">Request an Item</h3>
            <form id="borrowRequestForm" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Item</label>
                    <select id="inventory_item_id" required class="w-full p-2 border rounded-md bg-white focus:ring-2 focus:ring-green-500">
                        <option value="">Loading available items...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input type="date" id="due_date" required class="w-full p-2 border rounded-md focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes / Purpose</label>
                    <input type="text" id="notes" placeholder="e.g. Lab assignment" class="w-full p-2 border rounded-md focus:ring-2 focus:ring-green-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md">Submit Request</button>
                    <button type="button" onclick="toggleRequestForm()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-3 rounded-md">Cancel</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm uppercase border-b">
                        <th class="p-3">ID</th>
                        <th class="p-3">Requested By</th>
                        <th class="p-3">Item</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="requests-table-body" class="divide-y divide-gray-200 text-sm">
                    <tr><td colspan="5" class="p-6 text-center text-gray-500">Loading borrow requests...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-800">Loan Transactions (Issued & Returned)</h2>
            <p class="text-sm text-gray-500">Track item issuance and return history</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm uppercase border-b">
                        <th class="p-3">Loan ID</th>
                        <th class="p-3">Borrower</th>
                        <th class="p-3">Item</th>
                        <th class="p-3">Issue Date</th>
                        <th class="p-3">Due Date</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="loans-table-body" class="divide-y divide-gray-200 text-sm">
                    <tr><td colspan="7" class="p-6 text-center text-gray-500">Loading loan transactions...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="receipt-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-2xl relative">
        <div id="receipt-content" class="space-y-4 text-gray-800">
            <div class="text-center border-b pb-3">
                <h3 class="text-xl font-bold text-indigo-700">Campus Library System</h3>
                <p class="text-xs text-gray-500">Official Item Borrow Receipt & Gate Pass</p>
            </div>
            <div class="text-sm space-y-2">
                <p><strong>Receipt ID:</strong> #SLIP-<span id="slip-id"></span></p>
                <p><strong>Borrower Name:</strong> <span id="slip-borrower"></span></p>
                <p><strong>Item Name:</strong> <span id="slip-item"></span></p>
                <p><strong>Issue Date:</strong> <span id="slip-issue"></span></p>
                <p><strong>Due Date:</strong> <span id="slip-due"></span></p>
                <div class="p-2 bg-green-50 border border-green-200 text-green-800 text-xs rounded text-center font-bold">
                    STATUS: APPROVED (Authorized by Muzakkir Mirza)
                </div>
            </div>
            <p class="text-xs text-gray-400 text-center pt-2">Note: This is a system-generated slip and does not require a physical signature.</p>
        </div>
        <div class="mt-6 flex gap-2">
            <button onclick="printReceipt()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-semibold text-sm">Print / Download Slip</button>
            <button onclick="closeReceiptModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded text-sm">Close</button>
        </div>
    </div>
</div>

<script>
    const authToken = localStorage.getItem('auth_token');
    if (!authToken) window.location.href = '/login';

    const headers = {
        'Authorization': 'Bearer ' + authToken,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };

    let userRole = 'student';

    const nextWeek = new Date();
    nextWeek.setDate(nextWeek.getDate() + 7);
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput) dueDateInput.value = nextWeek.toISOString().split('T')[0];

    function toggleRequestForm() {
        document.getElementById('request-form-box').classList.toggle('hidden');
    }

    function showAlert(message, isSuccess = true) {
        const alertBox = document.getElementById('alert-box');
        alertBox.innerText = message;
        alertBox.className = `mb-4 p-3 rounded-md text-sm ${isSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
        alertBox.classList.remove('hidden');
        setTimeout(() => alertBox.classList.add('hidden'), 4000);
    }

    async function checkUserRole() {
        try {
            const res = await fetch('/api/user', { headers });
            const data = await res.json();
            userRole = data.role || 'student';
            
            const badge = document.getElementById('role-badge');
            if (badge) {
                badge.innerText = userRole + ' View';
                badge.className = userRole === 'admin' 
                    ? 'ml-4 px-3 py-1 bg-purple-200 text-purple-800 rounded-full text-xs font-bold uppercase' 
                    : 'ml-4 px-3 py-1 bg-blue-200 text-blue-800 rounded-full text-xs font-bold uppercase';
            }
        } catch (err) { console.error('Error fetching user role:', err); }
    }

    async function loadAvailableItems() {
        try {
            const res = await fetch('/api/inventory-items?is_available=true', { headers });
            const json = await res.json();
            const items = json.data || json;
            const select = document.getElementById('inventory_item_id');
            if (select) {
                select.innerHTML = '<option value="">Select Available Item</option>';
                items.forEach(item => {
                    select.innerHTML += `<option value="${item.id}">${item.name} (#${item.id})</option>`;
                });
            }
        } catch (err) {}
    }

    async function fetchBorrowRequests() {
        try {
            const res = await fetch('/api/borrow-requests', { headers });
            const json = await res.json();
            const requests = json.data || json;
            const tbody = document.getElementById('requests-table-body');
            
            if (!requests || requests.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-gray-500">No borrow requests found.</td></tr>';
                return;
            }

            tbody.innerHTML = requests.map(req => {
                const status = (req.status || 'pending').toLowerCase();
                const userName = req.user ? req.user.name : `User #${req.user_id || ''}`;
                const itemName = req.item ? req.item.name : (req.inventory_item ? req.inventory_item.name : `Item #${req.inventory_item_id}`);
                const issueDate = req.updated_at ? req.updated_at.split('T')[0] : new Date().toISOString().split('T')[0];
                
                let badgeClass = status === 'approved' ? 'bg-green-100 text-green-800' : (status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800');

                let actionButtons = '';
                if (userRole === 'admin') {
                    if (status === 'pending') {
                        actionButtons = `
                            <button onclick="updateRequestStatus(${req.id}, 'approved')" class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1 rounded">Approve</button>
                            <button onclick="updateRequestStatus(${req.id}, 'rejected')" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded">Reject</button>
                        `;
                    } else if (status === 'approved') {
                        actionButtons = `
                            <button onclick="viewReceipt(${req.id}, '${userName}', '${itemName}', '${issueDate}')" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded">View Slip</button>
                            <span class="text-xs text-gray-400 ml-1">Processed</span>
                        `;
                    } else {
                        actionButtons = '<span class="text-xs text-gray-400">Processed</span>';
                    }
                } else {
                    if (status === 'approved') {
                        actionButtons = `
                            <button onclick="viewReceipt(${req.id}, '${userName}', '${itemName}', '${issueDate}')" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded">View Slip</button>
                        `;
                    } else {
                        actionButtons = status === 'pending' ? '<span class="text-xs text-amber-600 font-semibold">Waiting for Admin</span>' : '<span class="text-xs text-gray-400">Processed</span>';
                    }
                }

                return `
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-medium text-gray-600">#${req.id}</td>
                        <td class="p-3 text-gray-800">${userName}</td>
                        <td class="p-3 font-semibold text-gray-800">${itemName}</td>
                        <td class="p-3"><span class="px-2 py-1 text-xs font-semibold rounded-full capitalize ${badgeClass}">${status}</span></td>
                        <td class="p-3 text-right space-x-1">${actionButtons}</td>
                    </tr>
                `;
            }).join('');
        } catch (err) { console.error(err); }
    }

    async function fetchLoanTransactions() {
        try {
            const res = await fetch('/api/loan-transactions', { headers });
            const json = await res.json();
            const loans = json.data || json;
            const tbody = document.getElementById('loans-table-body');
            
            if (!loans || loans.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="p-6 text-center text-gray-500">No loan transactions yet.</td></tr>';
                return;
            }

            tbody.innerHTML = loans.map(loan => {
                const isReturned = (loan.status && loan.status.toLowerCase() === 'returned') || Boolean(loan.return_date);
                const borrower = loan.user ? loan.user.name : `User #${loan.user_id || ''}`;
                const itemName = loan.item ? loan.item.name : (loan.inventory_item ? loan.inventory_item.name : `Item #${loan.inventory_item_id || ''}`);
                
                let returnBtn = '';
                if (userRole === 'admin') {
                    returnBtn = !isReturned ? `<button onclick="markLoanReturned(${loan.id})" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1 rounded">Mark Returned</button>` : '<span class="text-xs text-gray-400">Completed</span>';
                } else {
                    returnBtn = '<span class="text-xs text-gray-400">-</span>';
                }

                return `
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-medium text-gray-600">#${loan.id}</td>
                        <td class="p-3 text-gray-800">${borrower}</td>
                        <td class="p-3 font-semibold text-gray-800">${itemName}</td>
                        <td class="p-3 text-gray-600">${loan.issue_date || 'N/A'}</td>
                        <td class="p-3 text-gray-600">${loan.due_date || 'N/A'}</td>
                        <td class="p-3">
                            ${isReturned ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-700">Returned</span>' : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Issued</span>'}
                        </td>
                        <td class="p-3 text-right">${returnBtn}</td>
                    </tr>
                `;
            }).join('');
        } catch (err) {}
    }

    function viewReceipt(id, borrower, item, issue) {
        document.getElementById('slip-id').innerText = id;
        document.getElementById('slip-borrower').innerText = borrower;
        document.getElementById('slip-item').innerText = item;
        document.getElementById('slip-issue').innerText = issue;
        document.getElementById('slip-due').innerText = '7 Days from Issue';
        
        document.getElementById('receipt-modal').classList.remove('hidden');
    }

    function closeReceiptModal() {
        document.getElementById('receipt-modal').classList.add('hidden');
    }

    function printReceipt() {
        const printContents = document.getElementById('receipt-content').innerHTML;
        const originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }

    const borrowForm = document.getElementById('borrowRequestForm');
    if (borrowForm) {
        borrowForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const res = await fetch('/api/borrow-requests', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({ 
                        inventory_item_id: document.getElementById('inventory_item_id').value,
                        due_date: document.getElementById('due_date').value,
                        notes: document.getElementById('notes').value
                    })
                });
                if (res.ok) {
                    borrowForm.reset();
                    toggleRequestForm();
                    showAlert('Borrow request submitted successfully!');
                    fetchBorrowRequests();
                    loadAvailableItems();
                } else {
                    showAlert('Failed to submit borrow request.', false);
                }
            } catch (err) {}
        });
    }

    async function updateRequestStatus(id, status) {
        try {
            const res = await fetch(`/api/borrow-requests/${id}`, { 
                method: 'PUT', 
                headers, 
                body: JSON.stringify({ status }) 
            });
            if (res.ok) {
                showAlert(`Request ${status} successfully!`);
                fetchBorrowRequests();
                fetchLoanTransactions();
                loadAvailableItems();
            } else {
                showAlert('Failed to update request status.', false);
            }
        } catch (err) {}
    }

    async function markLoanReturned(id) {
        if (!confirm('Are you sure you want to mark this item as returned?')) return;
        try {
            const res = await fetch(`/api/loan-transactions/${id}`, {
                method: 'PUT', 
                headers, 
                body: JSON.stringify({ status: 'returned', return_date: new Date().toISOString().split('T')[0] })
            });
            if (res.ok) {
                showAlert('Item marked as returned!');
                fetchLoanTransactions();
                loadAvailableItems();
            } else {
                showAlert('Failed to mark loan as returned.', false);
            }
        } catch (err) {}
    }

    async function init() {
        await checkUserRole();
        loadAvailableItems();
        fetchBorrowRequests();
        fetchLoanTransactions();
    }
    
    init();
</script>
@endsection