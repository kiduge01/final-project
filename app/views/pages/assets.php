<?php $B = $baseUrl ?? ''; ?>

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-3xl font-heading font-semibold text-royal-900">Assets</h1>
        <p class="text-mist-600 text-sm mt-0.5">Register, assign to departments, track condition &amp; maintenance</p>
    </div>
    <button onclick="openModal('modal-new-asset')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-royal-600 text-white hover:bg-royal-700 text-sm font-semibold transition active:scale-95 shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Asset
    </button>
</div>

<!-- ── KPI Cards ──────────────────────────────────────── -->
<section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
    <div class="bg-white rounded-2xl border border-mist-200 p-4 hover:shadow-md transition">
        <p class="text-[11px] uppercase tracking-wider text-mist-500 font-semibold">Total Assets</p>
        <p id="stat-total" class="text-2xl font-bold text-royal-800 mt-1">—</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 p-4 hover:shadow-md transition">
        <p class="text-[11px] uppercase tracking-wider text-emerald-600 font-semibold">Active</p>
        <p id="stat-active" class="text-2xl font-bold text-emerald-700 mt-1">—</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 p-4 hover:shadow-md transition">
        <p class="text-[11px] uppercase tracking-wider text-amber-600 font-semibold">Maintenance Due</p>
        <p id="stat-due" class="text-2xl font-bold text-amber-700 mt-1">—</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 p-4 hover:shadow-md transition">
        <p class="text-[11px] uppercase tracking-wider text-red-600 font-semibold">Poor / Retired</p>
        <p id="stat-risk" class="text-2xl font-bold text-red-700 mt-1">—</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 p-4 hover:shadow-md transition">
        <p class="text-[11px] uppercase tracking-wider text-royal-600 font-semibold">Total Value</p>
        <p id="stat-value" class="text-xl font-bold text-royal-800 mt-1">—</p>
    </div>
</section>

<!-- ── Tabs ──────────────────────────────────────── -->
<div class="border-b border-mist-200 mb-6">
    <nav class="flex gap-1 -mb-px overflow-x-auto" id="asset-tabs">
        <button class="asset-tab active px-4 py-2.5 text-sm font-semibold border-b-2 transition whitespace-nowrap" data-tab="register">
            All Assets
        </button>
        <button class="asset-tab px-4 py-2.5 text-sm font-semibold border-b-2 transition whitespace-nowrap" data-tab="departments">
            By Department
        </button>
        <button class="asset-tab px-4 py-2.5 text-sm font-semibold border-b-2 transition whitespace-nowrap" data-tab="maintenance">
            Maintenance
        </button>
    </nav>
</div>
<style>
.asset-tab { border-color: transparent; color: #78909C; }
.asset-tab:hover { color: #1e3a5f; }
.asset-tab.active { border-color: #1e3a5f; color: #1e3a5f; }
</style>

<!-- ═══ TAB: All Assets ═══ -->
<section id="tab-register" class="tab-panel">
    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <input id="asset-search" type="text" placeholder="Search name, tag, location..."
            class="flex-1 min-w-[200px] rounded-xl border border-mist-200 px-3 py-2 text-sm focus:ring-2 focus:ring-royal-300 focus:border-royal-400 outline-none transition">
        <select id="asset-condition-filter" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
            <option value="">All conditions</option>
            <option value="excellent">Excellent</option>
            <option value="good">Good</option>
            <option value="fair">Fair</option>
            <option value="poor">Poor</option>
            <option value="retired">Retired</option>
        </select>
        <select id="asset-category-filter" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
            <option value="">All categories</option>
        </select>
        <select id="asset-dept-filter" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
            <option value="">All departments</option>
        </select>
    </div>

    <!-- Assets Grid -->
    <div id="assets-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3"></div>
    <div id="assets-empty" class="hidden bg-white rounded-2xl border border-mist-200 p-12 text-center">
        <svg class="w-16 h-16 text-mist-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
        <p class="text-mist-500 font-medium">No assets found</p>
        <p class="text-mist-400 text-sm mt-1">Register your first asset to get started</p>
    </div>
</section>

<!-- ═══ TAB: By Department ═══ -->
<section id="tab-departments" class="tab-panel hidden">
    <div id="dept-assets-container" class="space-y-4"></div>
    <div id="dept-assets-empty" class="hidden bg-white rounded-2xl border border-mist-200 p-12 text-center">
        <p class="text-mist-500 font-medium">No department assignments yet</p>
        <p class="text-mist-400 text-sm mt-1">Assign assets to departments from the asset cards</p>
    </div>
</section>

<!-- ═══ TAB: Maintenance ═══ -->
<section id="tab-maintenance" class="tab-panel hidden">
    <div class="flex flex-wrap gap-3 mb-4">
        <select id="maint-asset-select" class="flex-1 min-w-[200px] rounded-xl border border-mist-200 px-3 py-2 text-sm">
            <option value="">Select asset to view history...</option>
        </select>
        <button onclick="openMaintenanceModal()" class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold transition">
            + Log Maintenance
        </button>
    </div>
    <div id="maint-history" class="bg-white rounded-2xl border border-mist-200 overflow-x-auto">
        <div id="maint-body"></div>
        <div id="maint-empty" class="p-12 text-center text-mist-500">
            <svg class="w-12 h-12 text-mist-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.384 3.172a.5.5 0 01-.728-.528l1.027-5.994L1.69 7.26a.5.5 0 01.278-.854l6.023-.876L10.68.462a.5.5 0 01.898 0l2.69 5.068 6.023.876a.5.5 0 01.277.854l-4.644 4.56 1.027 5.994a.5.5 0 01-.728.528L11.42 15.17z"/></svg>
            Select an asset above to view its maintenance history
        </div>
    </div>
</section>

<!-- ═══ Modal: New/Edit Asset ═══ -->
<div id="modal-new-asset" class="hidden fixed inset-0 z-50 flex items-start justify-center p-4 pt-[5vh] bg-black/40 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-mist-100">
            <h3 id="asset-form-title" class="text-lg font-bold text-royal-900">Register New Asset</h3>
            <button onclick="closeModal('modal-new-asset')" class="text-mist-400 hover:text-mist-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="asset-form" class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="hidden" name="asset_id" value="">
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Asset Name *</label>
                <input name="name" required placeholder="e.g. Yamaha Mixer, Generator" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Category *</label>
                <input list="asset-cat-list" name="category" required placeholder="Sound, Furniture, IT..." class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300 outline-none">
                <datalist id="asset-cat-list">
                    <option value="Sound Equipment">
                    <option value="Furniture">
                    <option value="Musical Instrument">
                    <option value="Vehicle">
                    <option value="Electrical">
                    <option value="IT Equipment">
                    <option value="Kitchen">
                    <option value="Office Supplies">
                </datalist>
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Location *</label>
                <input name="current_location" required placeholder="Main Hall, Store, Office" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Condition</label>
                <select name="condition_status" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="excellent">Excellent</option>
                    <option value="good" selected>Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                    <option value="retired">Retired</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Purchase Date</label>
                <input type="date" name="purchase_date" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Purchase Value (TZS)</label>
                <input type="number" step="0.01" min="0" name="purchase_value" placeholder="0" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Warranty Expiry</label>
                <input type="date" name="warranty_expiry" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Status</label>
                <select name="is_active" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="1">Active</option>
                    <option value="0">Inactive / Retired</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional notes..." class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm"></textarea>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2 pt-2 border-t border-mist-100">
                <button type="button" onclick="closeModal('modal-new-asset')" class="px-4 py-2.5 rounded-xl bg-mist-100 text-mist-700 hover:bg-mist-200 text-sm font-medium transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-royal-600 text-white hover:bg-royal-700 text-sm font-semibold transition">Save Asset</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ Modal: Assign Asset ═══ -->
<div id="modal-assign" class="hidden fixed inset-0 z-50 flex items-start justify-center p-4 pt-[10vh] bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-mist-100">
            <h3 class="text-lg font-bold text-royal-900">Assign Asset</h3>
            <button onclick="closeModal('modal-assign')" class="text-mist-400 hover:text-mist-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="assign-form" class="p-6 space-y-4">
            <input type="hidden" name="assign_asset_id" value="">
            <p id="assign-asset-label" class="text-sm font-semibold text-royal-800 bg-mist-50 rounded-xl px-3 py-2"></p>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Assign To</label>
                <select id="assign-type" name="assigned_type" required class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="department">Department</option>
                    <option value="user">User</option>
                    <option value="event">Event</option>
                    <option value="location">Location</option>
                </select>
            </div>
            <div id="assign-dept-row">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Department</label>
                <select id="assign-department" name="department_id" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="">Select department...</option>
                </select>
            </div>
            <div id="assign-user-row" class="hidden">
                <label class="block text-xs font-semibold text-mist-600 mb-1">User</label>
                <select id="assign-user" name="assigned_user_id" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="">Select user...</option>
                </select>
            </div>
            <div id="assign-event-row" class="hidden">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Event</label>
                <select id="assign-event" name="assigned_event_id" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="">Select event...</option>
                </select>
            </div>
            <div id="assign-loc-row" class="hidden">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Location</label>
                <input id="assign-location" name="assigned_location" placeholder="e.g. Main Hall" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Notes</label>
                <input name="assign_notes" placeholder="Optional" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-mist-100">
                <button type="button" onclick="closeModal('modal-assign')" class="px-4 py-2.5 rounded-xl bg-mist-100 text-mist-700 hover:bg-mist-200 text-sm font-medium transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-royal-600 text-white hover:bg-royal-700 text-sm font-semibold transition">Assign</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ Modal: Maintenance Log ═══ -->
<div id="modal-maintenance" class="hidden fixed inset-0 z-50 flex items-start justify-center p-4 pt-[8vh] bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-mist-100">
            <h3 class="text-lg font-bold text-royal-900">Log Maintenance</h3>
            <button onclick="closeModal('modal-maintenance')" class="text-mist-400 hover:text-mist-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="maintenance-form" class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Asset *</label>
                <select id="maint-modal-asset" name="asset_id" required class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="">Select asset...</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Type *</label>
                <select name="maintenance_type" required class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="routine">Routine</option>
                    <option value="repair">Repair</option>
                    <option value="inspection">Inspection</option>
                    <option value="replacement">Replacement</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Date *</label>
                <input type="date" name="maintenance_date" required class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Cost (TZS)</label>
                <input type="number" min="0" step="0.01" name="maintenance_cost" value="0" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Next Due Date</label>
                <input type="date" name="next_due_date" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Service Provider</label>
                <input name="service_provider" placeholder="Vendor / Technician" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Issue</label>
                <textarea name="issue_description" rows="2" placeholder="What was the issue?" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Action Taken *</label>
                <textarea name="action_taken" required rows="2" placeholder="What was done?" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Update Condition</label>
                <select name="condition_status" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="">No change</option>
                    <option value="excellent">Excellent</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                </select>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2 pt-2 border-t border-mist-100">
                <button type="button" onclick="closeModal('modal-maintenance')" class="px-4 py-2.5 rounded-xl bg-mist-100 text-mist-700 hover:bg-mist-200 text-sm font-medium transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold transition">Save Log</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ Toast Container ═══ -->
<div id="toast-box" class="fixed top-4 right-4 z-[9999] space-y-2"></div>

<script>
const API = BASE_URL + '/api/v1';
let assets = [], departments = [], users = [], events = [];

function fmt(n) { return 'TZS ' + Number(n||0).toLocaleString('en-US', {minimumFractionDigits:0}); }

function openModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('hidden');
    m.addEventListener('click', function handler(e) { if (e.target === m) { closeModal(id); m.removeEventListener('click', handler); } });
}
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function showToast(msg, type) {
    const box = document.getElementById('toast-box');
    const d = document.createElement('div');
    d.className = 'px-4 py-3 rounded-xl text-sm font-semibold text-white shadow-lg transition-all transform ' +
        (type === 'error' ? 'bg-red-600' : type === 'info' ? 'bg-royal-600' : 'bg-emerald-600');
    d.textContent = msg;
    box.appendChild(d);
    setTimeout(() => { d.style.opacity = '0'; setTimeout(() => d.remove(), 300); }, 3500);
}

const condColors = {
    excellent:'bg-emerald-100 text-emerald-700',good:'bg-blue-100 text-blue-700',
    fair:'bg-amber-100 text-amber-700',poor:'bg-red-100 text-red-700',retired:'bg-mist-200 text-mist-600'
};

// ─── Tab Switching ───
document.querySelectorAll('.asset-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.asset-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('tab-' + btn.dataset.tab).classList.remove('hidden');
        if (btn.dataset.tab === 'departments') renderDepartmentView();
    });
});

// ─── Load Overview ───
async function loadOverview() {
    try {
        const res = await fetch(API + '/assets/overview');
        const d = (await res.json()).data || {};
        document.getElementById('stat-total').textContent = d.total_assets || 0;
        document.getElementById('stat-active').textContent = d.active_count || 0;
        document.getElementById('stat-due').textContent = d.due_maintenance || 0;
        document.getElementById('stat-risk').textContent = (+(d.conditions?.poor||0)) + (+(d.conditions?.retired||0));
        document.getElementById('stat-value').textContent = fmt(d.total_value || 0);
    } catch(e) { console.error('Overview error:', e); }
}

// ─── Load Meta (users, events, departments) ───
async function loadMeta() {
    const [uRes, eRes, dRes] = await Promise.all([
        fetch(API + '/meta/users'), fetch(API + '/events'), fetch(API + '/departments?active=1')
    ]);
    users = (await uRes.json()).data || [];
    events = (await eRes.json()).data || [];
    departments = (await dRes.json()).data || [];

    // Populate assign modal selects
    document.getElementById('assign-department').innerHTML = '<option value="">Select department...</option>' +
        departments.map(d => '<option value="'+d.id+'">'+d.name+'</option>').join('');
    document.getElementById('assign-user').innerHTML = '<option value="">Select user...</option>' +
        users.map(u => '<option value="'+u.id+'">'+u.full_name+'</option>').join('');
    document.getElementById('assign-event').innerHTML = '<option value="">Select event...</option>' +
        events.map(e => '<option value="'+e.id+'">'+e.title+'</option>').join('');

    // Department filter
    document.getElementById('asset-dept-filter').innerHTML = '<option value="">All departments</option>' +
        departments.map(d => '<option value="'+d.id+'">'+d.name+'</option>').join('');
}

// ─── Load Assets ───
async function loadAssets() {
    const search = document.getElementById('asset-search').value.trim();
    const condition = document.getElementById('asset-condition-filter').value;
    const category = document.getElementById('asset-category-filter').value;
    const deptId = document.getElementById('asset-dept-filter').value;

    const p = new URLSearchParams();
    if (search) p.set('search', search);
    if (condition) p.set('condition', condition);
    if (category) p.set('category', category);
    if (deptId) p.set('department_id', deptId);

    const res = await fetch(API + '/assets?' + p);
    const payload = await res.json();
    assets = payload.data || [];
    rebuildCategoryFilter();
    refreshAssetDropdowns();
    renderAssetsGrid();
}

function rebuildCategoryFilter() {
    const cats = [...new Set(assets.map(r => r.category).filter(Boolean))].sort();
    const sel = document.getElementById('asset-category-filter');
    const cur = sel.value;
    sel.innerHTML = '<option value="">All categories</option>' + cats.map(c => '<option value="'+c+'">'+c+'</option>').join('');
    if (cats.includes(cur)) sel.value = cur;
}

function refreshAssetDropdowns() {
    const opts = '<option value="">Select asset...</option>' + assets.map(a => '<option value="'+a.id+'">'+a.asset_tag+' — '+a.name+'</option>').join('');
    document.getElementById('maint-asset-select').innerHTML = opts;
    document.getElementById('maint-modal-asset').innerHTML = opts;
}

function renderAssetsGrid() {
    const grid = document.getElementById('assets-grid');
    const empty = document.getElementById('assets-empty');

    if (!assets.length) { grid.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');

    grid.innerHTML = assets.map(a => {
        const condCls = condColors[a.condition_status] || 'bg-mist-100 text-mist-600';
        const assigned = a.department_name
            ? '<span class="inline-flex items-center gap-1 text-[11px] text-royal-600 bg-royal-50 px-2 py-0.5 rounded-full font-medium">' + a.department_name + '</span>'
            : a.assigned_user_name
                ? '<span class="text-[11px] text-mist-500">' + a.assigned_user_name + '</span>'
                : a.assigned_event_title
                    ? '<span class="text-[11px] text-blue-600">' + a.assigned_event_title + '</span>'
                    : '<span class="text-[11px] text-mist-400 italic">Unassigned</span>';

        return '<div class="bg-white rounded-2xl border border-mist-200 p-4 hover:shadow-md transition group">' +
            '<div class="flex items-start justify-between gap-2 mb-3">' +
                '<div class="flex-1 min-w-0">' +
                    '<p class="font-semibold text-royal-800 truncate">' + a.name + '</p>' +
                    '<p class="text-[11px] text-mist-500 font-mono">' + a.asset_tag + '</p>' +
                '</div>' +
                '<span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase whitespace-nowrap ' + condCls + '">' + a.condition_status + '</span>' +
            '</div>' +
            '<div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs mb-3">' +
                '<div><span class="text-mist-400">Category</span><p class="text-mist-700 font-medium">' + (a.category || '—') + '</p></div>' +
                '<div><span class="text-mist-400">Location</span><p class="text-mist-700 font-medium">' + (a.current_location || '—') + '</p></div>' +
                '<div><span class="text-mist-400">Value</span><p class="text-mist-700 font-medium">' + fmt(a.purchase_value) + '</p></div>' +
                '<div><span class="text-mist-400">Assigned</span><div>' + assigned + '</div></div>' +
            '</div>' +
            '<div class="flex items-center gap-1.5 pt-2.5 border-t border-mist-100">' +
                '<button onclick="openEditAsset(' + a.id + ')" class="flex-1 px-2 py-1.5 rounded-lg bg-mist-50 hover:bg-mist-100 text-mist-700 text-xs font-semibold transition">Edit</button>' +
                '<button onclick="openAssignModal(' + a.id + ')" class="flex-1 px-2 py-1.5 rounded-lg bg-royal-50 hover:bg-royal-100 text-royal-700 text-xs font-semibold transition">Assign</button>' +
                '<button onclick="openMaintenanceFor(' + a.id + ')" class="flex-1 px-2 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold transition">Maint.</button>' +
                    (a.department_name || a.assigned_user_name || a.assigned_event_title
                    ? '<button onclick="unassignAsset(' + a.id + ')" class="px-2 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold transition" title="Remove assignment">'
                        + '<svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'
                        + '</button>'
                    : '') +
            '</div>' +
        '</div>';
    }).join('');
}

// ─── Department View ───
function renderDepartmentView() {
    const container = document.getElementById('dept-assets-container');
    const empty = document.getElementById('dept-assets-empty');

    // Group assets by department
    const byDept = {};
    const unassigned = [];
    departments.forEach(d => { byDept[d.id] = { name: d.name, items: [] }; });

    assets.forEach(a => {
        if (a.assigned_department_id && byDept[a.assigned_department_id]) {
            byDept[a.assigned_department_id].items.push(a);
        } else if (!a.assigned_department_id && !a.assigned_user_name && !a.assigned_event_title) {
            unassigned.push(a);
        }
    });

    const deptIds = Object.keys(byDept).filter(k => byDept[k].items.length > 0);
    if (deptIds.length === 0 && unassigned.length === 0) {
        container.innerHTML = '';
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');

    let html = '';
    deptIds.forEach(id => {
        const dept = byDept[id];
        html += '<div class="bg-white rounded-2xl border border-mist-200 overflow-hidden">' +
            '<div class="px-5 py-3 bg-royal-50 border-b border-royal-100 flex items-center justify-between">' +
                '<h3 class="font-semibold text-royal-800">' + dept.name + '</h3>' +
                '<span class="text-xs bg-royal-100 text-royal-700 px-2 py-0.5 rounded-full font-bold">' + dept.items.length + ' asset' + (dept.items.length !== 1 ? 's' : '') + '</span>' +
            '</div>' +
            '<div class="divide-y divide-mist-100">' +
            dept.items.map(a => deptAssetRow(a)).join('') +
            '</div></div>';
    });

    if (unassigned.length) {
        html += '<div class="bg-white rounded-2xl border border-mist-200 overflow-hidden">' +
            '<div class="px-5 py-3 bg-mist-50 border-b border-mist-200 flex items-center justify-between">' +
                '<h3 class="font-semibold text-mist-600">Unassigned</h3>' +
                '<span class="text-xs bg-mist-200 text-mist-600 px-2 py-0.5 rounded-full font-bold">' + unassigned.length + '</span>' +
            '</div>' +
            '<div class="divide-y divide-mist-100">' +
            unassigned.map(a => deptAssetRow(a)).join('') +
            '</div></div>';
    }

    container.innerHTML = html;
}

function deptAssetRow(a) {
    const condCls = condColors[a.condition_status] || 'bg-mist-100 text-mist-600';
    return '<div class="flex items-center gap-3 px-5 py-3 hover:bg-mist-50/40 transition">' +
        '<div class="flex-1 min-w-0">' +
            '<p class="text-sm font-semibold text-royal-800 truncate">' + a.name + '</p>' +
            '<p class="text-[11px] text-mist-500">' + a.asset_tag + ' · ' + (a.category||'') + ' · ' + (a.current_location||'') + '</p>' +
        '</div>' +
        '<span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold ' + condCls + '">' + a.condition_status + '</span>' +
        '<span class="text-xs text-mist-600 hidden md:block whitespace-nowrap">' + fmt(a.purchase_value) + '</span>' +
        '<button onclick="openAssignModal(' + a.id + ')" class="px-2 py-1 rounded-lg bg-royal-50 hover:bg-royal-100 text-royal-700 text-[11px] font-semibold transition whitespace-nowrap">Reassign</button>' +
    '</div>';
}

// ─── Edit Asset ───
function openEditAsset(id) {
    const a = assets.find(x => +x.id === id);
    if (!a) return;
    const f = document.getElementById('asset-form');
    f.querySelector('[name="asset_id"]').value = a.id;
    f.querySelector('[name="name"]').value = a.name || '';
    f.querySelector('[name="category"]').value = a.category || '';
    f.querySelector('[name="current_location"]').value = a.current_location || '';
    f.querySelector('[name="condition_status"]').value = a.condition_status || 'good';
    f.querySelector('[name="purchase_date"]').value = a.purchase_date || '';
    f.querySelector('[name="purchase_value"]').value = a.purchase_value || '';
    f.querySelector('[name="warranty_expiry"]').value = a.warranty_expiry || '';
    f.querySelector('[name="is_active"]').value = a.is_active == 1 ? '1' : '0';
    f.querySelector('[name="notes"]').value = a.notes || '';
    document.getElementById('asset-form-title').textContent = 'Edit — ' + a.asset_tag;
    openModal('modal-new-asset');
}

// ─── Submit Asset Form ───
document.getElementById('asset-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const data = Object.fromEntries(fd.entries());
    const id = data.asset_id; delete data.asset_id;
    const method = id ? 'PUT' : 'POST';
    const url = id ? API + '/assets/' + id : API + '/assets';

    try {
        const res = await fetch(url, { method, headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
        const r = await res.json();
        if (!res.ok || !r.success) throw new Error(r.message || 'Save failed');
        showToast(id ? 'Asset updated' : 'Asset registered', 'success');
        closeModal('modal-new-asset');
        this.reset(); this.querySelector('[name="asset_id"]').value = '';
        document.getElementById('asset-form-title').textContent = 'Register New Asset';
        await Promise.all([loadOverview(), loadAssets()]);
    } catch(err) { showToast(err.message, 'error'); }
});

// ─── Assign Modal ───
document.getElementById('assign-type').addEventListener('change', function() {
    document.getElementById('assign-dept-row').classList.toggle('hidden', this.value !== 'department');
    document.getElementById('assign-user-row').classList.toggle('hidden', this.value !== 'user');
    document.getElementById('assign-event-row').classList.toggle('hidden', this.value !== 'event');
    document.getElementById('assign-loc-row').classList.toggle('hidden', this.value !== 'location');
});

function openAssignModal(id) {
    const a = assets.find(x => +x.id === id);
    if (!a) return;
    document.querySelector('[name="assign_asset_id"]').value = id;
    document.getElementById('assign-asset-label').textContent = a.asset_tag + ' — ' + a.name;
    document.getElementById('assign-type').value = 'department';
    document.getElementById('assign-type').dispatchEvent(new Event('change'));
    document.querySelector('[name="assign_notes"]').value = '';
    openModal('modal-assign');
}

document.getElementById('assign-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const assetId = fd.get('assign_asset_id');
    const type = fd.get('assigned_type');
    const body = { assigned_type: type, notes: fd.get('assign_notes') || '' };

    if (type === 'department') body.department_id = fd.get('department_id');
    else if (type === 'user') body.assigned_user_id = fd.get('assigned_user_id');
    else if (type === 'event') body.assigned_event_id = fd.get('assigned_event_id');
    else if (type === 'location') body.assigned_location = fd.get('assigned_location');

    try {
        const res = await fetch(API + '/assets/' + assetId + '/assign', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body) });
        const r = await res.json();
        if (!res.ok || !r.success) throw new Error(r.message || 'Assign failed');
        showToast('Asset assigned', 'success');
        closeModal('modal-assign');
        await loadAssets();
    } catch(err) { showToast(err.message, 'error'); }
});

function showConfirmDialog(title, message, actionLabel, onConfirm, isDestructive) {
    var overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 z-[9998] bg-black/40 flex items-center justify-center p-4';
    var btnColor = isDestructive ? 'bg-red-600 hover:bg-red-700' : 'bg-royal-700 hover:bg-royal-800';
    overlay.innerHTML = '<div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">' +
        '<h3 class="text-lg font-bold text-gray-900 mb-2">' + title + '</h3>' +
        '<p class="text-sm text-gray-600 mb-6">' + message + '</p>' +
        '<div class="flex justify-end gap-3">' +
        '<button id="_cf_cancel" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</button>' +
        '<button id="_cf_ok" class="px-4 py-2 text-sm font-semibold text-white ' + btnColor + ' rounded-lg transition">' + (actionLabel||'OK') + '</button>' +
        '</div></div>';
    document.body.appendChild(overlay);
    overlay.querySelector('#_cf_cancel').onclick = function() { overlay.remove(); };
    overlay.querySelector('#_cf_ok').onclick = function() { overlay.remove(); onConfirm(); };
    overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
}

async function unassignAsset(id) {
    showConfirmDialog('Remove Assignment', 'Remove the current assignment from this asset?', 'Remove', async function() {
        try {
            const res = await fetch(API + '/assets/' + id + '/unassign', { method:'POST', headers:{'Content-Type':'application/json'} });
            const r = await res.json();
            if (!res.ok || !r.success) throw new Error(r.message || 'Failed');
            showToast('Assignment removed', 'success');
            await loadAssets();
        } catch(err) { showToast(err.message, 'error'); }
    }, true);
}

// ─── Maintenance Tab ───
document.getElementById('maint-asset-select').addEventListener('change', function() {
    loadMaintenanceHistory(+this.value || null);
});

function openMaintenanceModal() {
    const sel = document.getElementById('maint-asset-select').value;
    if (sel) document.getElementById('maint-modal-asset').value = sel;
    document.querySelector('#maintenance-form [name="maintenance_date"]').value = new Date().toISOString().slice(0,10);
    openModal('modal-maintenance');
}

function openMaintenanceFor(id) {
    document.getElementById('maint-modal-asset').value = id;
    document.querySelector('#maintenance-form [name="maintenance_date"]').value = new Date().toISOString().slice(0,10);
    openModal('modal-maintenance');
    // Also switch tab and load history
    document.querySelectorAll('.asset-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('[data-tab="maintenance"]').classList.add('active');
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById('tab-maintenance').classList.remove('hidden');
    document.getElementById('maint-asset-select').value = id;
    loadMaintenanceHistory(id);
}

async function loadMaintenanceHistory(assetId) {
    const body = document.getElementById('maint-body');
    const empty = document.getElementById('maint-empty');
    if (!assetId) { body.innerHTML = ''; empty.classList.remove('hidden'); return; }

    try {
        const res = await fetch(API + '/assets/' + assetId + '/maintenance');
        const rows = (await res.json()).data || [];
        if (!rows.length) { body.innerHTML = ''; empty.classList.remove('hidden'); return; }
        empty.classList.add('hidden');

        body.innerHTML = '<table class="w-full text-sm"><thead class="bg-mist-50"><tr>' +
            '<th class="px-4 py-2.5 text-left text-[11px] uppercase text-mist-500">Date</th>' +
            '<th class="px-4 py-2.5 text-left text-[11px] uppercase text-mist-500">Type</th>' +
            '<th class="px-4 py-2.5 text-left text-[11px] uppercase text-mist-500">Action Taken</th>' +
            '<th class="px-4 py-2.5 text-left text-[11px] uppercase text-mist-500">Provider</th>' +
            '<th class="px-4 py-2.5 text-right text-[11px] uppercase text-mist-500">Cost</th>' +
            '<th class="px-4 py-2.5 text-left text-[11px] uppercase text-mist-500">Next Due</th>' +
            '</tr></thead><tbody class="divide-y divide-mist-100">' +
            rows.map(r => '<tr class="hover:bg-mist-50/40">' +
                '<td class="px-4 py-2.5 text-mist-600 whitespace-nowrap">' + r.maintenance_date + '</td>' +
                '<td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase bg-mist-100 text-mist-700">' + r.maintenance_type + '</span></td>' +
                '<td class="px-4 py-2.5 text-mist-700 max-w-[200px] truncate" title="' + (r.action_taken||'').replace(/"/g,'&quot;') + '">' + (r.action_taken||'-') + '</td>' +
                '<td class="px-4 py-2.5 text-mist-600">' + (r.service_provider||'-') + '</td>' +
                '<td class="px-4 py-2.5 text-right text-mist-700">' + fmt(r.maintenance_cost) + '</td>' +
                '<td class="px-4 py-2.5 text-mist-600">' + (r.next_due_date||'-') + '</td>' +
            '</tr>').join('') +
            '</tbody></table>';
    } catch(e) { body.innerHTML = '<p class="p-4 text-red-500">' + e.message + '</p>'; }
}

document.getElementById('maintenance-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const data = Object.fromEntries(fd.entries());
    const assetId = data.asset_id; delete data.asset_id;
    if (!assetId) { showToast('Select an asset first', 'error'); return; }

    try {
        const res = await fetch(API + '/assets/' + assetId + '/maintenance', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
        const r = await res.json();
        if (!res.ok || !r.success) throw new Error(r.message || 'Failed');
        showToast('Maintenance logged', 'success');
        closeModal('modal-maintenance');
        this.reset();
        await Promise.all([loadOverview(), loadAssets(), loadMaintenanceHistory(+assetId)]);
    } catch(err) { showToast(err.message, 'error'); }
});

// ─── Auto-filter ───
let filterTimer;
function debounceFilter() { clearTimeout(filterTimer); filterTimer = setTimeout(() => loadAssets(), 300); }
document.getElementById('asset-search').addEventListener('input', debounceFilter);
document.getElementById('asset-condition-filter').addEventListener('change', () => loadAssets());
document.getElementById('asset-category-filter').addEventListener('change', () => loadAssets());
document.getElementById('asset-dept-filter').addEventListener('change', () => loadAssets());

// ─── Bootstrap ───
Promise.all([loadOverview(), loadMeta(), loadAssets()]).catch(err => {
    console.error('Assets init failed:', err);
    showToast('Failed to load assets page', 'error');
});
</script>
