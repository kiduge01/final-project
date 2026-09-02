<?php
$user = $_SESSION['user'] ?? [];
$canCreate   = \App\Core\Auth::can('procurement.request.create');
$canApprove  = \App\Core\Auth::can('procurement.request.approve');
$canComplete = \App\Core\Auth::can('procurement.po.complete');
?>

<div class="procurement-module max-w-7xl mx-auto">

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-xs font-bold uppercase tracking-wider text-royal-700 mb-1">Operations</p>
        <h1 class="text-2xl font-bold text-gray-900">Procurement</h1>
        <p class="text-gray-500 mt-1">Purchase requests linked to approved budgets</p>
    </div>
    <?php if ($canCreate): ?>
    <button onclick="openNewPRModal()" class="px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl shadow-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Request
    </button>
    <?php endif; ?>
</div>

<!-- Status summary -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500 font-bold">Open requests</p>
        <p id="pr-stat-open" class="text-2xl font-extrabold text-gray-900 mt-2">--</p>
        <p class="text-xs text-gray-500 mt-1">Submitted, approved, or purchased</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs uppercase tracking-wide text-amber-600 font-bold">Needs review</p>
        <p id="pr-stat-review" class="text-2xl font-extrabold text-amber-700 mt-2">--</p>
        <p class="text-xs text-gray-500 mt-1">Waiting for approval decision</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs uppercase tracking-wide text-royal-700 font-bold">Approved budgets</p>
        <p id="pr-stat-budgets" class="text-2xl font-extrabold text-royal-800 mt-2">--</p>
        <p class="text-xs text-gray-500 mt-1">Available for new requests</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs uppercase tracking-wide text-blue-600 font-bold">Completed</p>
        <p id="pr-stat-completed" class="text-2xl font-extrabold text-blue-700 mt-2">--</p>
        <p class="text-xs text-gray-500 mt-1">Closed purchases and cancellations</p>
    </div>
</div>

<!-- Sub-tabs -->
<div class="border-b border-gray-200 mb-5">
    <nav class="flex gap-1 -mb-px">
        <button data-ptab="requests" class="ptab ptab-active px-4 py-2.5 text-sm font-semibold border-b-2 border-royal-600 text-royal-700">All Requests</button>
        <button data-ptab="approved" class="ptab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700">Approved Orders</button>
        <button data-ptab="completed" class="ptab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700">Completed</button>
    </nav>
</div>

<!-- Tab: All Requests -->
<div id="ptab-requests" class="ptab-panel">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Request #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Budget</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Purpose</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Cost</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Items</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Requested By</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr></thead>
                <tbody id="pr-all-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="pr-all-empty" class="hidden px-5 py-12 text-center text-gray-400">
            <p class="font-medium">No procurement requests yet</p>
        </div>
    </div>
</div>

<!-- Tab: Approved Orders -->
<div id="ptab-approved" class="ptab-panel hidden">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Request #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Budget</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Purpose</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Cost</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Vendor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Approved By</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr></thead>
                <tbody id="pr-approved-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="pr-approved-empty" class="hidden px-5 py-12 text-center text-gray-400">No approved orders</div>
    </div>
</div>

<!-- Tab: Completed -->
<div id="ptab-completed" class="ptab-panel hidden">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Request #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Budget</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Purpose</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Cost</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Completed</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                </tr></thead>
                <tbody id="pr-completed-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="pr-completed-empty" class="hidden px-5 py-12 text-center text-gray-400">No completed purchases</div>
    </div>
</div>

<!-- PR Detail Modal -->
<div id="pr-detail-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50" style="display:none; align-items:center; justify-content:center;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto" style="margin:1rem;">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-900">Purchase Request Detail</h2>
            <button onclick="closePRDetailModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div id="pr-detail-content" class="px-6 py-4 space-y-4"></div>
    </div>
</div>

<!-- New PR Modal -->
<div id="pr-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50" style="display:none; align-items:center; justify-content:center;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto" style="margin:1rem;">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">New Procurement Request</h2>
            <p class="text-xs text-gray-500 mt-0.5">Must be linked to an approved budget</p>
        </div>
        <form id="pr-form" class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Budget <span class="text-red-500">*</span></label>
                <select name="budget_id" id="pr-budget-select" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    <option value="">Loading budgets...</option>
                </select>
                <p id="pr-budget-info" class="text-xs text-gray-500 mt-1"></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose <span class="text-red-500">*</span></label>
                    <input name="purpose" required placeholder="What is this purchase for?" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input name="vendor_name" placeholder="Supplier/vendor name" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Items <span class="text-red-500">*</span></label>
                    <button type="button" onclick="addPRItemRow()" class="text-xs text-royal-600 hover:text-royal-800 font-semibold">+ Add Item</button>
                </div>
                <div id="pr-items-container" class="space-y-2"></div>
                <p id="pr-items-total" class="text-right text-sm font-bold text-gray-700 mt-2">Total: TZS 0</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closePRModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<!-- Approve/Reject Modal -->
<div id="pr-approve-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50" style="display:none; align-items:center; justify-content:center;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" style="margin:1rem;">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Approve / Reject Request</h2>
        </div>
        <form id="pr-approve-form" class="px-6 py-4 space-y-4">
            <input type="hidden" id="approve-pr-id">
            <p id="approve-pr-label" class="text-sm font-medium text-gray-700"></p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="approve-pr-notes" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closePRApproveModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl">Cancel</button>
                <button type="button" onclick="submitPRDecision('rejected')" class="px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl">Reject</button>
                <button type="button" onclick="submitPRDecision('approved')" class="px-4 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-xl">Approve</button>
            </div>
        </form>
    </div>
</div>

</div>

<script>
(function() {
    // Use the global BASE_URL from layout, don't re-declare
    var API = BASE_URL + '/api/v1';
    var CAN_CREATE   = <?= $canCreate   ? 'true' : 'false' ?>;
    var CAN_APPROVE  = <?= $canApprove  ? 'true' : 'false' ?>;
    var CAN_COMPLETE = <?= $canComplete ? 'true' : 'false' ?>;
    var allPRs = [];
    var activeBudgets = [];
    var prItemIndex = 0;
    var budgetListenerAttached = false;

    function fmt(n) { return 'TZS ' + Number(n || 0).toLocaleString('en-US', {minimumFractionDigits: 0}); }

    // Escape HTML to prevent XSS
    function esc(str) {
        if (str === null || str === undefined) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    }

    function showToast(msg, type) {
        var div = document.createElement('div');
        div.className = 'fixed top-4 right-4 z-[9999] px-5 py-3 rounded-xl text-sm font-semibold text-white shadow-lg ' + (type === 'success' ? 'bg-green-600' : 'bg-red-600');
        div.textContent = msg;
        document.body.appendChild(div);
        setTimeout(function() { div.remove(); }, 3000);
    }

    // Custom styled confirm dialog
    function showConfirmDialog(title, message, actionLabel, onConfirm, onCancel, isDestructive) {
        var overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[9998] bg-black/40 flex items-center justify-center p-4';
        overlay.style.animation = 'fadeIn .15s ease';
        var btnColor = isDestructive ? 'bg-red-600 hover:bg-red-700' : 'bg-royal-700 hover:bg-royal-800';
        overlay.innerHTML = '<div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" style="animation:fadeIn .15s ease">' +
            '<h3 class="text-lg font-bold text-gray-900 mb-2">' + esc(title) + '</h3>' +
            '<p class="text-sm text-gray-600 mb-6 whitespace-pre-line">' + esc(message) + '</p>' +
            '<div class="flex justify-end gap-3">' +
            '<button id="_cf_cancel" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</button>' +
            '<button id="_cf_ok" class="px-4 py-2 text-sm font-semibold text-white ' + btnColor + ' rounded-lg transition">' + esc(actionLabel || 'OK') + '</button>' +
            '</div></div>';
        document.body.appendChild(overlay);
        overlay.querySelector('#_cf_cancel').onclick = function() { overlay.remove(); if (onCancel) onCancel(); };
        overlay.querySelector('#_cf_ok').onclick = function() { overlay.remove(); onConfirm(); };
        overlay.addEventListener('click', function(e) { if (e.target === overlay) { overlay.remove(); if (onCancel) onCancel(); } });
    }

    // Sub-tab switching
    document.querySelectorAll('.ptab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.ptab').forEach(function(t) {
                t.classList.remove('ptab-active','border-royal-600','text-royal-700');
                t.classList.add('border-transparent','text-gray-500');
            });
            btn.classList.add('ptab-active','border-royal-600','text-royal-700');
            btn.classList.remove('border-transparent','text-gray-500');
            document.querySelectorAll('.ptab-panel').forEach(function(p) { p.classList.add('hidden'); });
            document.getElementById('ptab-' + btn.dataset.ptab).classList.remove('hidden');
        });
    });

    var SC = {
        draft:'bg-gray-100 text-gray-600', submitted:'bg-amber-100 text-amber-800', pending:'bg-amber-100 text-amber-800',
        approved:'bg-green-100 text-green-800', rejected:'bg-red-100 text-red-800',
        purchased:'bg-blue-100 text-blue-800', completed:'bg-purple-100 text-purple-700', cancelled:'bg-gray-200 text-gray-500'
    };

    // Load all data
    function loadPRData() {
        Promise.all([
            fetch(API + '/procurement/requests'),
            fetch(API + '/procurement/active-budgets')
        ]).then(function(responses) {
            var prRes = responses[0], budRes = responses[1];
            if (!prRes.ok) throw new Error('Requests API: ' + prRes.status);
            if (!budRes.ok) throw new Error('Budgets API: ' + budRes.status);
            return Promise.all([prRes.json(), budRes.json()]);
        }).then(function(data) {
            allPRs = data[0].data || [];
            activeBudgets = data[1].data || [];
            renderProcurementStats();
            renderAllRequests();
            renderApproved();
            renderCompleted();
            populateBudgetSelect();
        }).catch(function(e) {
            console.error('PR load failed:', e);
            var el = document.getElementById('pr-all-empty');
            el.textContent = 'Failed to load: ' + e.message;
            el.classList.remove('hidden');
        });
    }

    function renderProcurementStats() {
        var open = allPRs.filter(function(r) { return ['completed','cancelled'].indexOf(r.status) === -1; }).length;
        var review = allPRs.filter(function(r) { return r.status === 'submitted' || r.status === 'pending'; }).length;
        var completed = allPRs.filter(function(r) { return r.status === 'completed' || r.status === 'cancelled'; }).length;
        document.getElementById('pr-stat-open').textContent = open;
        document.getElementById('pr-stat-review').textContent = review;
        document.getElementById('pr-stat-budgets').textContent = activeBudgets.length;
        document.getElementById('pr-stat-completed').textContent = completed;
    }

    function renderAllRequests() {
        var rows = allPRs.filter(function(r) { return ['completed','cancelled'].indexOf(r.status) === -1; });
        var tbody = document.getElementById('pr-all-tbody');
        var empty = document.getElementById('pr-all-empty');
        if (!rows.length) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
        empty.classList.add('hidden');
        tbody.innerHTML = rows.map(function(r) {
            var actions = '';
            if (r.status === 'submitted' && CAN_APPROVE) {
                actions += '<button onclick="window._prApprove(' + r.id + ')" class="px-2.5 py-1 text-xs bg-green-50 hover:bg-green-100 text-green-700 rounded-lg font-semibold">Review</button>';
            }
            if (r.status === 'approved' && CAN_COMPLETE) {
                actions += '<button onclick="window._prPurchase(' + r.id + ')" class="px-2.5 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">Mark Purchased</button>';
            }
            if (['completed','cancelled','purchased'].indexOf(r.status) === -1 && CAN_CREATE) {
                actions += '<button onclick="window._prCancel(' + r.id + ')" class="px-2.5 py-1 text-xs text-red-500 hover:text-red-700 font-semibold">Cancel</button>';
            }
            actions += '<button onclick="window._prView(' + r.id + ')" class="px-2.5 py-1 text-xs text-gray-500 hover:text-gray-700 font-semibold">View</button>';
            return '<tr class="hover:bg-gray-50">' +
                '<td class="px-4 py-3 text-xs font-mono text-royal-600 font-bold">' + esc(r.request_no) + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-700">' + esc(r.budget_department || '-') + ' <span class="text-xs text-gray-400">' + esc(r.budget_month || '') + '</span></td>' +
                '<td class="px-4 py-3 text-sm text-gray-600 max-w-[200px] truncate">' + esc(r.purpose) + '</td>' +
                '<td class="px-4 py-3 text-right font-semibold text-gray-900">' + fmt(r.items_total || r.estimated_cost) + '</td>' +
                '<td class="px-4 py-3 text-xs text-gray-500">' + (r.item_count || 0) + ' items</td>' +
                '<td class="px-4 py-3 text-xs text-gray-600">' + esc(r.requested_by_name || '-') + '</td>' +
                '<td class="px-4 py-3 text-xs text-gray-500">' + esc(r.requested_date || '-') + '</td>' +
                '<td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ' + (SC[r.status]||'') + '">' + esc(r.status) + '</span></td>' +
                '<td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1 flex-wrap">' + actions + '</div></td></tr>';
        }).join('');
    }

    function renderApproved() {
        var rows = allPRs.filter(function(r) { return r.status === 'approved' || r.status === 'purchased'; });
        var tbody = document.getElementById('pr-approved-tbody');
        var empty = document.getElementById('pr-approved-empty');
        if (!rows.length) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
        empty.classList.add('hidden');
        tbody.innerHTML = rows.map(function(r) {
            var actions = '';
            if (r.status === 'approved' && CAN_COMPLETE) {
                actions += '<button onclick="window._prPurchase(' + r.id + ')" class="px-2.5 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">Mark Purchased</button>';
            }
            if (r.status === 'purchased' && CAN_COMPLETE) {
                actions += '<button onclick="window._prComplete(' + r.id + ')" class="px-2.5 py-1 text-xs bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold">Complete</button>';
            }
            return '<tr class="hover:bg-gray-50">' +
                '<td class="px-4 py-3 text-xs font-mono text-royal-600 font-bold">' + esc(r.request_no) + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-700">' + esc(r.budget_department || '-') + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-600">' + esc(r.purpose) + '</td>' +
                '<td class="px-4 py-3 text-right font-semibold text-gray-900">' + fmt(r.items_total || r.estimated_cost) + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-600">' + esc(r.vendor_name || '-') + '</td>' +
                '<td class="px-4 py-3 text-xs text-gray-600">' + esc(r.approved_by_name || '-') + '</td>' +
                '<td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1 flex-wrap">' + actions + '</div></td></tr>';
        }).join('');
    }

    function renderCompleted() {
        var rows = allPRs.filter(function(r) { return r.status === 'completed' || r.status === 'cancelled'; });
        var tbody = document.getElementById('pr-completed-tbody');
        var empty = document.getElementById('pr-completed-empty');
        if (!rows.length) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
        empty.classList.add('hidden');
        tbody.innerHTML = rows.map(function(r) {
            return '<tr class="hover:bg-gray-50 ' + (r.status === 'cancelled' ? 'opacity-60' : '') + '">' +
                '<td class="px-4 py-3 text-xs font-mono text-gray-600">' + esc(r.request_no) + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-700">' + esc(r.budget_department || '-') + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-600">' + esc(r.purpose) + '</td>' +
                '<td class="px-4 py-3 text-right font-semibold text-gray-900">' + fmt(r.items_total || r.estimated_cost) + '</td>' +
                '<td class="px-4 py-3 text-xs text-gray-500">' + (r.completed_at ? esc(r.completed_at.substring(0,10)) : '-') + '</td>' +
                '<td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ' + (SC[r.status]||'') + '">' + esc(r.status) + '</span></td></tr>';
        }).join('');
    }

    function populateBudgetSelect() {
        var sel = document.getElementById('pr-budget-select');
        sel.innerHTML = '<option value="">-- Select an approved budget --</option>';
        activeBudgets.forEach(function(b) {
            var avail = parseFloat(b.available) || 0;
            var opt = document.createElement('option');
            opt.value = b.id;
            opt.textContent = b.department + ' - ' + b.fiscal_month +
                (b.event_title ? ' (' + b.event_title + ')' : '') +
                ' | Available: ' + fmt(avail);
            sel.appendChild(opt);
        });
    }

    // Budget select change listener — set up ONCE, not inside populateBudgetSelect
    document.getElementById('pr-budget-select').addEventListener('change', function() {
        var sel = this;
        var b = activeBudgets.find(function(x) { return x.id == sel.value; });
        var info = document.getElementById('pr-budget-info');
        if (b) {
            info.innerHTML = '<b>Approved:</b> ' + fmt(b.planned_amount) + ' | <b>Spent:</b> ' + fmt(b.actual_amount) + ' | <b>Reserved:</b> ' + fmt(b.reserved_amount) + ' | <b>Available:</b> ' + fmt(b.available);
        } else { info.innerHTML = ''; }
    });

    // PR Item rows
    function addPRItemRow() {
        var idx = prItemIndex++;
        var row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-2 pr-item-row';
        row.dataset.idx = idx;
        row.innerHTML =
            '<input name="items[' + idx + '][item_name]" placeholder="Item name" required class="col-span-5 border border-gray-300 rounded-lg px-2 py-1.5 text-sm">' +
            '<input name="items[' + idx + '][quantity]" type="number" step="1" min="1" value="1" required class="col-span-2 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-right pr-item-qty" onchange="window._prUpdateTotal()" oninput="window._prUpdateTotal()">' +
            '<input name="items[' + idx + '][estimated_unit_cost]" type="number" step="1" min="0" placeholder="Unit cost" required class="col-span-3 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-right pr-item-cost" onchange="window._prUpdateTotal()" oninput="window._prUpdateTotal()">' +
            '<button type="button" onclick="this.closest(\'.pr-item-row\').remove();window._prUpdateTotal()" class="col-span-2 text-xs text-red-500 hover:text-red-700 font-semibold">Remove</button>';
        document.getElementById('pr-items-container').appendChild(row);
        updatePRItemsTotal();
    }

    function updatePRItemsTotal() {
        var total = 0;
        document.querySelectorAll('.pr-item-row').forEach(function(row) {
            var qty = parseFloat(row.querySelector('.pr-item-qty') ? row.querySelector('.pr-item-qty').value : 0) || 0;
            var cost = parseFloat(row.querySelector('.pr-item-cost') ? row.querySelector('.pr-item-cost').value : 0) || 0;
            total += qty * cost;
        });
        document.getElementById('pr-items-total').textContent = 'Total: ' + fmt(total);
    }

    // Modals
    function openNewPRModal() {
        var m = document.getElementById('pr-modal');
        if (m.parentElement !== document.body) document.body.appendChild(m);
        m.style.display = 'flex';
        addPRItemRow();
    }

    function closePRModal() {
        document.getElementById('pr-modal').style.display = 'none';
        document.getElementById('pr-form').reset();
        document.getElementById('pr-items-container').innerHTML = '';
        prItemIndex = 0;
        updatePRItemsTotal();
    }

    function closePRDetailModal() { document.getElementById('pr-detail-modal').style.display = 'none'; }
    function closePRApproveModal() { document.getElementById('pr-approve-modal').style.display = 'none'; }

    // Submit PR
    document.getElementById('pr-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var fd = new FormData(e.target);
        var items = [];
        document.querySelectorAll('.pr-item-row').forEach(function(row) {
            var idx = row.dataset.idx;
            items.push({
                item_name: fd.get('items[' + idx + '][item_name]'),
                quantity: fd.get('items[' + idx + '][quantity]'),
                estimated_unit_cost: fd.get('items[' + idx + '][estimated_unit_cost]'),
            });
        });
        // Validate at least one item
        if (items.length === 0) {
            showToast('Please add at least one item', 'error');
            return;
        }
        var payload = {
            budget_id: fd.get('budget_id'),
            purpose: fd.get('purpose'),
            vendor_name: fd.get('vendor_name'),
            items: items
        };
        fetch(API + '/procurement/requests', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        }).then(function(res) {
            if (!res.ok) return res.json().then(function(d) { throw new Error(d.message || 'HTTP ' + res.status); });
            return res.json();
        }).then(function(data) {
            if (!data.success) throw new Error(data.message || 'Failed');
            showToast('Procurement request submitted', 'success');
            closePRModal();
            loadPRData();
        }).catch(function(err) { showToast(err.message, 'error'); });
    });

    // Approve/Reject
    function openPRApproveModal(id) {
        var pr = allPRs.find(function(r) { return r.id == id; });
        if (!pr) return;
        document.getElementById('approve-pr-id').value = id;
        document.getElementById('approve-pr-label').textContent = pr.request_no + ' - ' + pr.purpose + ' (' + fmt(pr.items_total || pr.estimated_cost) + ')';
        document.getElementById('approve-pr-notes').value = '';
        var m = document.getElementById('pr-approve-modal');
        if (m.parentElement !== document.body) document.body.appendChild(m);
        m.style.display = 'flex';
    }

    function submitPRDecision(decision) {
        var id = document.getElementById('approve-pr-id').value;
        var notes = document.getElementById('approve-pr-notes').value;
        fetch(API + '/procurement/requests/' + id + '/approve', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ decision: decision, notes: notes })
        }).then(function(res) {
            if (!res.ok) return res.json().then(function(d) { throw new Error(d.message || 'HTTP ' + res.status); });
            return res.json();
        }).then(function(data) {
            if (!data.success) throw new Error(data.message || 'Failed');
            showToast('Request ' + decision, 'success');
            closePRApproveModal();
            loadPRData();
        }).catch(function(err) { showToast(err.message, 'error'); });
    }

    // Mark Purchased
    function markAsPurchased(id) {
        showConfirmDialog(
            'Mark as Purchased?',
            'This will convert costs to actual budget expenses.',
            'Mark Purchased',
            function() {
                fetch(API + '/procurement/requests/' + id + '/purchase', {
                    method: 'POST', headers: {'Content-Type':'application/json'}, body: '{}'
                }).then(function(res) {
                    if (!res.ok) return res.json().then(function(d) { throw new Error(d.message || 'HTTP ' + res.status); });
                    return res.json();
                }).then(function(data) {
                    if (!data.success) throw new Error(data.message || 'Failed');
                    showToast('Marked as purchased - expenses recorded', 'success');
                    loadPRData();
                }).catch(function(err) { showToast(err.message, 'error'); });
            }
        );
    }

    // Complete
    function completePR(id) {
        showConfirmDialog(
            'Complete Procurement?',
            'Mark this procurement as completed?',
            'Complete',
            function() {
                fetch(API + '/procurement/requests/' + id + '/complete', {
                    method: 'POST', headers: {'Content-Type':'application/json'}, body: '{}'
                }).then(function(res) {
                    if (!res.ok) return res.json().then(function(d) { throw new Error(d.message || 'HTTP ' + res.status); });
                    return res.json();
                }).then(function(data) {
                    if (!data.success) throw new Error(data.message || 'Failed');
                    showToast('Procurement completed', 'success');
                    loadPRData();
                }).catch(function(err) { showToast(err.message, 'error'); });
            }
        );
    }

    // Cancel
    function cancelPR(id) {
        showConfirmDialog(
            'Cancel Request?',
            'Are you sure you want to cancel this procurement request?',
            'Cancel Request',
            function() {
                fetch(API + '/procurement/requests/' + id + '/cancel', {
                    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ reason: 'Cancelled by user' })
                }).then(function(res) {
                    if (!res.ok) return res.json().then(function(d) { throw new Error(d.message || 'HTTP ' + res.status); });
                    return res.json();
                }).then(function(data) {
                    if (!data.success) throw new Error(data.message || 'Failed');
                    showToast('Request cancelled', 'success');
                    loadPRData();
                }).catch(function(err) { showToast(err.message, 'error'); });
            },
            null,
            true
        );
    }

    // View Detail
    function viewPRDetail(id) {
        fetch(API + '/procurement/requests/' + id)
        .then(function(res) {
            if (!res.ok) throw new Error('Failed to load details (HTTP ' + res.status + ')');
            return res.json();
        })
        .then(function(data) {
            if (!data.success) {
                showToast(data.message || 'Failed to load request details', 'error');
                return;
            }
            var pr = data.data;
            var items = pr.items || [];
            var history = pr.approval_history || [];

            var html = '<div class="grid grid-cols-2 gap-4 text-sm">'
                + '<div><b>Request #:</b> ' + esc(pr.request_no) + '</div>'
                + '<div><b>Status:</b> <span class="px-2 py-0.5 rounded-full text-xs font-semibold ' + (SC[pr.status]||'') + '">' + esc(pr.status) + '</span></div>'
                + '<div><b>Budget:</b> ' + esc(pr.budget_department || '-') + ' - ' + esc(pr.budget_month || '') + '</div>'
                + '<div><b>Purpose:</b> ' + esc(pr.purpose) + '</div>'
                + '<div><b>Vendor:</b> ' + esc(pr.vendor_name || '-') + '</div>'
                + '<div><b>Requested By:</b> ' + esc(pr.requested_by_name || '-') + '</div>'
                + '<div><b>Total Cost:</b> ' + fmt(pr.estimated_cost) + '</div>'
                + '</div>';

            if (items.length) {
                html += '<h3 class="font-semibold text-gray-800 mt-4 mb-2">Line Items</h3>'
                    + '<table class="w-full text-sm border border-gray-100 rounded-xl overflow-hidden">'
                    + '<thead class="bg-gray-50"><tr>'
                    + '<th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Item</th>'
                    + '<th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Qty</th>'
                    + '<th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Unit Cost</th>'
                    + '<th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Total</th>'
                    + '</tr></thead><tbody class="divide-y divide-gray-100">';
                items.forEach(function(i) {
                    html += '<tr>'
                        + '<td class="px-3 py-2">' + esc(i.item_name || '') + '</td>'
                        + '<td class="px-3 py-2 text-right">' + (i.quantity || 0) + '</td>'
                        + '<td class="px-3 py-2 text-right">' + fmt(i.estimated_unit_cost) + '</td>'
                        + '<td class="px-3 py-2 text-right font-semibold">' + fmt(i.line_total) + '</td>'
                        + '</tr>';
                });
                html += '</tbody></table>';
            }

            if (history.length) {
                html += '<h3 class="font-semibold text-gray-800 mt-4 mb-2">Approval Trail</h3><div class="space-y-2">';
                history.forEach(function(h) {
                    var c = h.action === 'approved' ? 'text-green-700 bg-green-50'
                          : h.action === 'rejected' ? 'text-red-700 bg-red-50'
                          : 'text-amber-700 bg-amber-50';
                    html += '<div class="flex items-center gap-2 text-xs p-2 rounded-lg ' + c + '">'
                        + '<b>' + esc(h.actor_name || 'System') + '</b> '
                        + esc(h.action)
                        + ' <span class="text-gray-400">@ ' + esc(h.acted_at || '') + '</span>'
                        + (h.notes ? ' - ' + esc(h.notes) : '')
                        + '</div>';
                });
                html += '</div>';
            }

            document.getElementById('pr-detail-content').innerHTML = html;
            document.getElementById('pr-detail-modal').style.display = 'flex';
        }).catch(function(e) {
            console.error('viewPRDetail error:', e);
            showToast('Failed to load request details', 'error');
        });
    }

    // Expose functions to onclick handlers
    window.openNewPRModal = openNewPRModal;
    window.closePRModal = closePRModal;
    window.closePRDetailModal = closePRDetailModal;
    window.closePRApproveModal = closePRApproveModal;
    window.addPRItemRow = addPRItemRow;
    window.submitPRDecision = submitPRDecision;
    window._prApprove = openPRApproveModal;
    window._prPurchase = markAsPurchased;
    window._prComplete = completePR;
    window._prCancel = cancelPR;
    window._prView = viewPRDetail;
    window._prUpdateTotal = updatePRItemsTotal;

    // Init
    loadPRData();
})();
</script>

<style>
.procurement-module .ptab-active { border-color: #3344a5; color: #3344a5; }
</style>
