<?php
/**
 * Admin — Department Detail Page
 * Tabs: Overview, Members, Leaders, Finance, Reports
 */
$DEPT_ID = (int) ($deptId ?? 0);
?>
<script>
const DEPT_ID  = <?= $DEPT_ID ?>;
const DEPT_API = BASE_URL + '/api/v1/departments/' + DEPT_ID;

/* ─── helpers ─── */
function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmt(n) {
    return 'Tsh ' + parseFloat(n||0).toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0});
}
function fmtDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'});
}

/* ─── Tab switching ─── */
function switchTab(tab) {
    document.querySelectorAll('.dtab').forEach(b => {
        b.classList.remove('border-royal-600','text-royal-700');
        b.classList.add('border-transparent','text-mist-500');
    });
    document.querySelectorAll('.dtab-panel').forEach(p => p.classList.add('hidden'));
    const btn = document.querySelector('.dtab[data-tab="'+tab+'"]');
    if (btn) { btn.classList.add('border-royal-600','text-royal-700'); btn.classList.remove('border-transparent','text-mist-500'); }
    const panel = document.getElementById('dtab-' + tab);
    if (panel) panel.classList.remove('hidden');

    if (tab === 'overview')       loadOverview();
    if (tab === 'members')        loadMembers();
    if (tab === 'leaders')        loadLeaders();
    if (tab === 'budgets')        loadDeptBudgets();
    if (tab === 'finance')        loadFinance();
    if (tab === 'reports')        loadReports();
    if (tab === 'contributions')  loadContributions();
    if (tab === 'assets')         loadAssets();
}

/* ─── Overview ─── */
async function loadOverview() {
    const el = document.getElementById('dtab-overview');
    el.innerHTML = '<p class="text-sm text-mist-400 py-6 text-center">Loading…</p>';
    try {
        const res  = await fetch(DEPT_API + '/overview');
        const json = await res.json();
        const d    = json.data?.department ?? {};
        const s    = json.data ?? {};
        const bal  = parseFloat(s.balance ?? 0);

        // Update breadcrumb title
        document.getElementById('dept-page-title').textContent = esc(d.name ?? 'Department');

        el.innerHTML = `
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            ${statCard('Members',  s.member_count ?? 0,    'text-royal-700',   'bg-royal-100',  'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z')}
            ${statCard('Leaders',  s.leader_count ?? 0,    'text-glory-700',   'bg-glory-100',  'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z')}
            ${statCard('Balance',  fmt(bal),               bal>=0 ? 'text-emerald-700' : 'text-red-600',   bal>=0 ? 'bg-emerald-100' : 'bg-red-100',  'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z')}
            ${statCard('Reports',  (s.submitted_reports ?? 0) + ' pending', 'text-dawn-700',    'bg-dawn-100',   'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z')}
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
                <h3 class="text-base font-bold text-royal-800 mb-4">Department Info</h3>
                <div class="space-y-3 text-sm">
                    ${infoRow('Name',        d.name)}
                    ${infoRow('Description', d.description || 'No description')}
                    ${infoRow('Head',        d.head_name || 'Not assigned')}
                    ${infoRow('Login Email', d.head_email || 'Not set')}
                    ${infoRow('Status',      d.is_active == 1 ? 'Active' : 'Inactive')}
                    ${infoRow('Created',     fmtDate(d.created_at))}
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
                <h3 class="text-base font-bold text-royal-800 mb-4">Finance Summary</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-mist-100">
                        <span class="text-mist-600 font-medium">Total Income</span>
                        <span class="font-bold text-emerald-600">${fmt(s.total_income)}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-mist-100">
                        <span class="text-mist-600 font-medium">Total Expenses</span>
                        <span class="font-bold text-red-500">${fmt(s.total_expense)}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="font-bold text-mist-700">Balance</span>
                        <span class="font-bold ${bal>=0 ? 'text-emerald-600' : 'text-red-500'}">${fmt(bal)}</span>
                    </div>
                </div>
            </div>
        </div>`;
    } catch(e) {
        el.innerHTML = `<p class="text-sm text-red-500 text-center py-6">Error: ${esc(e.message)}</p>`;
    }
}
function statCard(label, value, textCls, bgCls, icon) {
    return `<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl ${bgCls} ${textCls} flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="${icon}"/></svg>
        </div>
        <div>
            <p class="text-xl font-bold text-royal-900">${esc(String(value))}</p>
            <p class="text-xs text-mist-400 font-semibold uppercase tracking-wide">${label}</p>
        </div>
    </div>`;
}
function infoRow(label, val) {
    return `<div class="flex gap-2"><span class="text-mist-400 font-semibold w-28 shrink-0">${label}</span><span class="text-mist-800">${esc(String(val ?? '—'))}</span></div>`;
}

/* ─── Members ─── */
async function loadMembers() {
    const el = document.getElementById('dtab-members');
    el.innerHTML = '<p class="text-sm text-mist-400 py-6 text-center">Loading…</p>';
    try {
        const res  = await fetch(DEPT_API + '/members');
        const json = await res.json();
        const rows = json.data || [];
        if (!rows.length) { el.innerHTML = '<p class="text-center text-mist-400 py-10">No members assigned to this department.</p>'; return; }
        el.innerHTML = `
        <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-mist-100 flex items-center justify-between">
                <span class="text-sm font-bold text-mist-700">${rows.length} Member${rows.length !== 1 ? 's' : ''}</span>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Phone</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role / Note</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Joined</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                ${rows.map((r,i) => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-xs text-gray-400">${i+1}</td>
                        <td class="px-5 py-3 font-semibold text-royal-800">${esc((r.first_name||'')+' '+(r.last_name||''))}</td>
                        <td class="px-5 py-3 text-mist-600">${esc(r.phone||'—')}</td>
                        <td class="px-5 py-3 text-mist-500">${esc(r.role_note||'—')}</td>
                        <td class="px-5 py-3 text-mist-400 text-xs">${fmtDate(r.assigned_date)}</td>
                    </tr>`).join('')}
                </tbody>
            </table></div>
        </div>`;
    } catch(e) { el.innerHTML = `<p class="text-red-500 text-sm text-center py-6">Error: ${esc(e.message)}</p>`; }
}

/* ─── Leaders ─── */
async function loadLeaders() {
    const el = document.getElementById('dtab-leaders');
    el.innerHTML = '<p class="text-sm text-mist-400 py-6 text-center">Loading…</p>';
    try {
        const res  = await fetch(DEPT_API + '/leaders');
        const json = await res.json();
        const rows = json.data || [];
        if (!rows.length) { el.innerHTML = '<p class="text-center text-mist-400 py-10">No leaders registered for this department.</p>'; return; }
        el.innerHTML = `
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        ${rows.map(r => `
            <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-4 flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-glory-100 text-glory-700 flex items-center justify-center font-bold text-sm shrink-0">
                    ${esc((r.leader_name||'?').charAt(0).toUpperCase())}
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-royal-800 text-sm truncate">${esc(r.leader_name)}</p>
                    <p class="text-xs text-mist-500 font-medium">${esc(r.leader_type)}</p>
                    ${r.phone ? `<p class="text-xs text-mist-400 mt-1">${esc(r.phone)}</p>` : ''}
                    <span class="inline-block mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold ${parseInt(r.is_active) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'}">
                        ${parseInt(r.is_active) ? 'Active' : 'Inactive'}
                    </span>
                </div>
            </div>`).join('')}
        </div>`;
    } catch(e) { el.innerHTML = `<p class="text-red-500 text-sm text-center py-6">Error: ${esc(e.message)}</p>`; }
}

/* ─── Budgets (linked to main finance budget system) ─── */
async function loadDeptBudgets() {
    const el = document.getElementById('dtab-budgets');
    el.innerHTML = '<p class="text-sm text-mist-400 py-6 text-center">Loading budgets...</p>';
    try {
        // Get department name first
        const oRes = await fetch(DEPT_API + '/overview');
        const oJson = await oRes.json();
        const deptName = oJson.data?.department?.name || '';

        // Get all budgets and filter by this department
        const bRes = await fetch(BASE_URL + '/api/v1/finance/budgets');
        const bJson = await bRes.json();
        const all = bJson.data || [];
        const rows = all.filter(b => b.department === deptName);

        const sc = {
            draft:'bg-gray-100 text-gray-600', submitted:'bg-amber-100 text-amber-800',
            approved:'bg-green-100 text-green-800', rejected:'bg-red-100 text-red-800',
            expenses_added:'bg-blue-100 text-blue-800', closed:'bg-purple-100 text-purple-700'
        };

        // Summary
        const active = rows.filter(r => r.status === 'approved' || r.status === 'expenses_added');
        const totalPlanned = active.reduce((s,r) => s + parseFloat(r.planned_amount||0), 0);
        const totalUsed = active.reduce((s,r) => s + parseFloat(r.total_used||r.actual_amount||0), 0);
        const totalReserved = active.reduce((s,r) => s + parseFloat(r.reserved_amount||0), 0);
        const totalAvail = totalPlanned - totalUsed - totalReserved;

        el.innerHTML = `
        <!-- Budget KPIs -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold text-blue-700">${fmt(totalPlanned)}</p>
                <p class="text-[10px] font-bold uppercase text-blue-400">Approved Budgets</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold text-amber-700">${fmt(totalReserved)}</p>
                <p class="text-[10px] font-bold uppercase text-amber-400">Reserved (PR)</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold text-red-600">${fmt(totalUsed)}</p>
                <p class="text-[10px] font-bold uppercase text-red-400">Spent</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold ${totalAvail >= 0 ? 'text-emerald-700' : 'text-red-600'}">${fmt(totalAvail)}</p>
                <p class="text-[10px] font-bold uppercase text-emerald-400">Available</p>
            </div>
        </div>

        ${!rows.length ? '<p class="text-center text-mist-400 py-8">No budgets found for this department.<br><span class="text-xs">Go to Finance > Budgets to create one.</span></p>' : `
        <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Month</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Planned</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Reserved</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Spent</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Available</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                ${rows.map(r => {
                    const p = parseFloat(r.planned_amount||0);
                    const u = parseFloat(r.total_used||r.actual_amount||0);
                    const rv = parseFloat(r.reserved_amount||0);
                    const av = p - u - rv;
                    return `<tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs font-semibold text-gray-700">${esc(r.fiscal_month)}${r.event_title ? ' <span class="text-gray-400">(' + esc(r.event_title) + ')</span>' : ''}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">${fmt(p)}</td>
                        <td class="px-4 py-3 text-right text-amber-600">${fmt(rv)}</td>
                        <td class="px-4 py-3 text-right text-red-600">${fmt(u)}</td>
                        <td class="px-4 py-3 text-right font-semibold ${av >= 0 ? 'text-emerald-600' : 'text-red-600'}">${fmt(av)}</td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-[150px] truncate">${esc(r.description||r.notes||'')}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ${sc[r.status]||''}">${esc(r.status)}</span></td>
                    </tr>`;
                }).join('')}
                </tbody>
            </table></div>
        </div>`}`;
    } catch(e) { el.innerHTML = `<p class="text-red-500 text-sm text-center py-6">Error: ${esc(e.message)}</p>`; }
}

/* ─── Finance ─── */
async function loadFinance(typeFilter) {
    const el = document.getElementById('dtab-finance');
    const url = DEPT_API + '/finance' + (typeFilter ? '?type=' + typeFilter : '');
    el.innerHTML = '<p class="text-sm text-mist-400 py-6 text-center">Loading…</p>';
    try {
        const res  = await fetch(url);
        const json = await res.json();
        const rows = json.data || [];
        const sum  = json.summary || {};

        el.innerHTML = `
        <!-- Summary bar -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold text-emerald-700">${fmt(sum.income)}</p>
                <p class="text-[10px] font-bold uppercase text-emerald-400 tracking-wide">Total Income</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold text-red-500">${fmt(sum.expense)}</p>
                <p class="text-[10px] font-bold uppercase text-red-300 tracking-wide">Total Expense</p>
            </div>
            <div class="bg-royal-50 border border-royal-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold ${parseFloat(sum.balance||0)>=0 ? 'text-royal-700' : 'text-red-600'}">${fmt(sum.balance)}</p>
                <p class="text-[10px] font-bold uppercase text-royal-300 tracking-wide">Balance</p>
            </div>
        </div>
        <!-- Filter -->
        <div class="flex gap-2 mb-4">
            <button onclick="loadFinance()" class="px-3 py-1.5 text-xs rounded-lg font-semibold ${!typeFilter ? 'bg-royal-600 text-white' : 'bg-mist-100 text-mist-700 hover:bg-mist-200'}">All</button>
            <button onclick="loadFinance('income')" class="px-3 py-1.5 text-xs rounded-lg font-semibold ${'income'===typeFilter ? 'bg-emerald-500 text-white' : 'bg-mist-100 text-mist-700 hover:bg-mist-200'}">Income</button>
            <button onclick="loadFinance('expense')" class="px-3 py-1.5 text-xs rounded-lg font-semibold ${'expense'===typeFilter ? 'bg-red-400 text-white' : 'bg-mist-100 text-mist-700 hover:bg-mist-200'}">Expense</button>
        </div>
        ${rows.length === 0 ? '<p class="text-center text-mist-400 py-8">No finance records found.</p>' : `
        <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                ${rows.map(r => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-xs text-mist-400">${fmtDate(r.created_at)}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${r.type==='income' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600'}">${esc(r.type)}</span>
                        </td>
                        <td class="px-5 py-3 text-mist-600">${esc(r.category||'—')}</td>
                        <td class="px-5 py-3 text-mist-500 max-w-xs truncate">${esc(r.description||'—')}</td>
                        <td class="px-5 py-3 text-right font-bold ${r.type==='income' ? 'text-emerald-600' : 'text-red-500'}">${fmt(r.amount)}</td>
                    </tr>`).join('')}
                </tbody>
            </table></div>
        </div>`}`;
    } catch(e) { el.innerHTML = `<p class="text-red-500 text-sm text-center py-6">Error: ${esc(e.message)}</p>`; }
}

/* ─── Reports ─── */
let currentReportFilter = '';
async function loadReports(statusFilter) {
    currentReportFilter = statusFilter || '';
    const el  = document.getElementById('dtab-reports');
    const url = DEPT_API + '/reports' + (statusFilter ? '?status=' + statusFilter : '');
    el.innerHTML = '<p class="text-sm text-mist-400 py-6 text-center">Loading…</p>';
    try {
        const res  = await fetch(url);
        const json = await res.json();
        const rows = json.data || [];

        const badges = {
            draft:     'bg-gray-100 text-gray-500',
            submitted: 'bg-dawn-100 text-dawn-700',
            approved:  'bg-emerald-100 text-emerald-700',
            rejected:  'bg-red-100 text-red-600',
        };

        el.innerHTML = `
        <div class="flex gap-2 mb-5 flex-wrap">
            ${['','draft','submitted','approved','rejected'].map(s =>
                `<button onclick="loadReports('${s}')" class="px-3 py-1.5 text-xs rounded-lg font-semibold ${s===currentReportFilter ? 'bg-royal-600 text-white' : 'bg-mist-100 text-mist-700 hover:bg-mist-200'}">
                    ${s === '' ? 'All' : s.charAt(0).toUpperCase()+s.slice(1)}
                 </button>`
            ).join('')}
        </div>
        ${rows.length === 0 ? '<p class="text-center text-mist-400 py-8">No reports found.</p>' : rows.map(r => `
        <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 mb-3">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div>
                    <h4 class="font-bold text-royal-800 text-sm">${esc(r.title)}</h4>
                    <p class="text-xs text-mist-400 mt-0.5">${esc(r.category)} &bull; ${fmtDate(r.report_date)}</p>
                </div>
                <span class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-bold ${badges[r.status]||'bg-gray-100 text-gray-500'}">
                    ${esc(r.status)}
                </span>
            </div>
            <p class="text-sm text-mist-600 line-clamp-2 mb-3">${esc(r.description)}</p>
            ${r.status === 'submitted' ? `
            <div class="flex items-center gap-2 pt-2 border-t border-mist-100">
                <span class="text-xs text-mist-400">Review:</span>
                <button onclick="reviewReport(${r.id},'approve')" class="px-3 py-1 text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg font-semibold transition">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Approve
                </button>
                <button onclick="reviewReport(${r.id},'reject')" class="px-3 py-1 text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded-lg font-semibold transition">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reject
                </button>
            </div>` : r.reviewed_at ? `
            <p class="text-xs text-mist-400 pt-2 border-t border-mist-100">
                Reviewed by ${esc(r.reviewed_by_name||'Admin')} on ${fmtDate(r.reviewed_at)}
                ${r.review_notes ? ' &mdash; ' + esc(r.review_notes) : ''}
            </p>` : ''}
        </div>`).join('')}`;
    } catch(e) { el.innerHTML = `<p class="text-red-500 text-sm text-center py-6">Error: ${esc(e.message)}</p>`; }
}

async function reviewReport(reportId, action) {
    const notes = action === 'reject' ? (prompt('Reason for rejection (optional):') ?? '') : '';
    try {
        const res  = await fetch(`${DEPT_API}/reports/${reportId}/review`, {
            method: 'PUT',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action, notes })
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        loadReports(currentReportFilter);
    } catch(e) { alert('Error: ' + e.message); }
}

/* ─── Contributions ─── */
let currentContribFilter = '';
async function loadContributions(methodFilter) {
    currentContribFilter = methodFilter || '';
    const el  = document.getElementById('dtab-contributions');
    const url = DEPT_API + '/contributions' + (methodFilter ? '?method=' + methodFilter : '');
    el.innerHTML = '<p class="text-sm text-mist-400 py-6 text-center">Loading…</p>';
    try {
        const res  = await fetch(url);
        const json = await res.json();
        const rows = json.data    || [];
        const sum  = json.summary || {};

        if (!rows.length && !sum.total) {
            el.innerHTML = '<p class="text-center text-mist-400 py-10">No contributions recorded yet.</p>'; return;
        }

        const methodColors = {cash:'bg-emerald-100 text-emerald-700',mpesa:'bg-blue-100 text-blue-700',
            cheque:'bg-purple-100 text-purple-700',bank_transfer:'bg-dawn-100 text-dawn-700',other:'bg-mist-100 text-mist-600'};
        const methodLabels = {cash:'Cash',mpesa:'M-Pesa',cheque:'Cheque',bank_transfer:'Bank Transfer',other:'Other'};
        const methods = ['','cash','mpesa','cheque','bank_transfer','other'];

        el.innerHTML = `
        <!-- Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold text-emerald-700">${fmt(sum.total)}</p>
                <p class="text-[10px] font-bold uppercase text-emerald-400 tracking-wide">Total</p>
            </div>
            <div class="bg-royal-50 border border-royal-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold text-royal-700">${esc(sum.count ?? 0)}</p>
                <p class="text-[10px] font-bold uppercase text-royal-300 tracking-wide">Records</p>
            </div>
            <div class="bg-dawn-50 border border-dawn-200 rounded-xl p-3 text-center">
                <p class="text-base font-bold text-dawn-700">${fmt(sum.mpesa)}</p>
                <p class="text-[10px] font-bold uppercase text-dawn-300 tracking-wide">Via M-Pesa</p>
            </div>
        </div>
        <!-- Filter -->
        <div class="flex gap-2 mb-4 flex-wrap">
            ${methods.map(m => `
                <button onclick="loadContributions('${m}')" class="px-3 py-1.5 text-xs rounded-lg font-semibold ${m===currentContribFilter ? 'bg-royal-600 text-white' : 'bg-mist-100 text-mist-700 hover:bg-mist-200'}">
                    ${m === '' ? 'All' : (methodLabels[m]||m)}
                </button>`).join('')}
        </div>
        ${rows.length === 0 ? '<p class="text-center text-mist-400 py-8">No records with this filter.</p>' : `
        <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contributor</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                ${rows.map((r,i) => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-xs text-mist-400">${i+1}</td>
                        <td class="px-5 py-3 text-mist-600 whitespace-nowrap text-xs">${fmtDate(r.contribution_date)}</td>
                        <td class="px-5 py-3">
                            <span class="font-semibold text-royal-800">${esc(r.display_name)}</span>
                            <span class="ml-1.5 text-[10px] px-1.5 py-0.5 rounded-full ${r.is_member ? 'bg-royal-100 text-royal-600' : 'bg-mist-100 text-mist-400'} font-semibold">
                                ${r.is_member ? 'Member' : 'External'}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${methodColors[r.payment_method]||'bg-mist-100 text-mist-500'}">
                                ${methodLabels[r.payment_method]||esc(r.payment_method)}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-mist-500 text-xs max-w-[160px] truncate">${esc(r.purpose||'—')}</td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-600">${fmt(r.amount)}</td>
                    </tr>`).join('')}
                </tbody>
                <tfoot class="bg-gray-50 border-t border-mist-200">
                    <tr>
                        <td colspan="5" class="px-5 py-3 text-sm font-bold text-mist-700">Total</td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-600">${fmt(rows.reduce((s,r)=>s+parseFloat(r.amount||0),0))}</td>
                    </tr>
                </tfoot>
            </table></div>
        </div>`}`;
    } catch(e) { el.innerHTML = `<p class="text-red-500 text-sm text-center py-6">Error: ${esc(e.message)}</p>`; }
}

/* ─── Assets ─── */
async function loadAssets() {
    const el = document.getElementById('dtab-assets');
    el.innerHTML = '<p class="text-sm text-mist-400 py-6 text-center">Loading…</p>';
    try {
        const res  = await fetch(DEPT_API + '/assets');
        const json = await res.json();
        const rows = json.data || [];

        if (!rows.length) {
            el.innerHTML = '<p class="text-center text-mist-400 py-10">No assets assigned to this department yet.</p>'; return;
        }

        const condColors = {
            excellent:'bg-emerald-100 text-emerald-700', good:'bg-dawn-100 text-dawn-700',
            fair:'bg-glory-100 text-glory-700', poor:'bg-red-100 text-red-600', retired:'bg-gray-100 text-gray-500'
        };

        // Group by category
        const grouped = {};
        rows.forEach(r => { if (!grouped[r.category]) grouped[r.category] = []; grouped[r.category].push(r); });

        el.innerHTML = `
        <div class="mb-4 flex items-center gap-3">
            <span class="text-sm font-bold text-mist-700">${rows.length} item${rows.length!==1?'s':''} assigned</span>
        </div>
        ${Object.entries(grouped).map(([cat, items]) => `
        <div class="mb-6">
            <h3 class="text-xs font-bold text-mist-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                ${esc(cat)}
                <span class="px-1.5 py-0.5 bg-mist-100 text-mist-500 rounded-full text-[10px]">${items.length}</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            ${items.map(a => `
                <div class="bg-white rounded-xl border border-mist-200 shadow-sm p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="w-9 h-9 rounded-lg bg-dawn-100 text-dawn-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                            </svg>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${condColors[a.condition_status]||'bg-mist-100 text-mist-500'}">
                            ${esc(a.condition_status)}
                        </span>
                    </div>
                    <p class="font-bold text-royal-800 text-sm">${esc(a.name)}</p>
                    <p class="text-xs font-mono text-mist-400 mt-0.5">${esc(a.asset_tag)}</p>
                    ${a.assignment_notes ? `<p class="text-xs text-mist-400 mt-1 italic">${esc(a.assignment_notes)}</p>` : ''}
                    <p class="text-[10px] text-mist-300 mt-2">Assigned ${fmtDate(a.assigned_from)}</p>
                </div>`).join('')}
            </div>
        </div>`).join('')}`;
    } catch(e) { el.innerHTML = `<p class="text-red-500 text-sm text-center py-6">Error: ${esc(e.message)}</p>`; }
}

/* ─── Init ─── */
document.addEventListener('DOMContentLoaded', () => switchTab('overview'));
</script>

<!-- Back link + title -->
<div class="mb-4 flex items-center gap-3">
    <a href="<?= $baseUrl ?>/departments" class="inline-flex items-center gap-1 text-sm text-mist-500 hover:text-royal-700 font-semibold transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        All Departments
    </a>
    <span class="text-mist-300">/</span>
    <h1 id="dept-page-title" class="text-xl font-heading font-bold text-royal-800">Loading…</h1>
</div>

<!-- Tabs -->
<div class="border-b border-mist-200 mb-6">
    <nav class="flex gap-0 -mb-px overflow-x-auto">
        <?php
        $tabs = [
            'overview'      => 'Overview',
            'members'       => 'Members',
            'leaders'       => 'Leaders',
            'budgets'       => 'Budgets',
            'finance'       => 'Finance',
            'reports'       => 'Reports',
            'contributions' => 'Contributions',
            'assets'        => 'Assets',
        ];
        foreach ($tabs as $key => $label):
        ?>
        <button data-tab="<?= $key ?>" onclick="switchTab('<?= $key ?>')"
            class="dtab border-b-2 border-transparent text-mist-500 px-5 py-3 text-sm font-semibold whitespace-nowrap hover:text-royal-700 hover:border-royal-300 transition">
            <?= $label ?>
        </button>
        <?php endforeach; ?>
    </nav>
</div>

<!-- Tab panels -->
<?php foreach (array_keys($tabs) as $key): ?>
<div id="dtab-<?= $key ?>" class="dtab-panel <?= $key !== 'overview' ? 'hidden' : '' ?>">
    <p class="text-sm text-mist-400 py-6 text-center">Loading…</p>
</div>
<?php endforeach; ?>
