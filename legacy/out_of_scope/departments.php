<?php
/**
 * Admin - Departments Overview Page
 * Shows all departments with key stats; click to drill into detail.
 */
?>

<div class="mb-6 flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-heading font-bold text-royal-800">Departments</h1>
        <p class="text-sm text-mist-500 mt-0.5">Overview of all church departments - click any card to view details</p>
    </div>
    <button onclick="openDeptModal()"
       class="inline-flex items-center gap-2 px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Department
    </button>
</div>

<div id="dept-cards-grid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
    <p class="col-span-full text-center text-sm text-mist-400 py-10">Loading...</p>
</div>
<div id="dept-cards-empty" class="hidden text-center py-16 text-mist-400">
    <svg class="w-12 h-12 mx-auto mb-3 text-mist-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z"/>
    </svg>
    <p class="font-semibold">No departments found.</p>
    <p class="text-sm mt-1">Click "New Department" to create one.</p>
</div>
<div id="dept-cards-err" class="hidden text-center py-10 text-red-500 text-sm font-semibold"></div>

<!-- Create / Edit Department Modal -->
<div id="dept-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeDeptModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 id="dept-modal-title" class="text-lg font-bold text-gray-900 mb-4">New Department</h3>
            <form id="dept-form" class="space-y-4">
                <input type="hidden" id="dept-edit-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department Name <span class="text-red-500">*</span></label>
                    <input name="name" id="dept-name" required placeholder="e.g. Vijana, Ibada, Media..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Head Name</label>
                    <input name="head_name" id="dept-head-name" placeholder="Department head's full name" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input name="description" id="dept-description" placeholder="Brief description..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeDeptModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Set Login Credentials Modal -->
<div id="cred-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeCredModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Set Department Login</h3>
            <p class="text-xs text-gray-500 mb-4">This creates a separate login for the department head portal</p>
            <form id="cred-form" class="space-y-4">
                <input type="hidden" id="cred-dept-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login Email <span class="text-red-500">*</span></label>
                    <input name="head_email" id="cred-email" type="email" required placeholder="e.g. vijana@kanisa.com" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input name="password" id="cred-password" type="password" required minlength="6" placeholder="Min 6 characters" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCredModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm">Set Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const DEPTS_API = BASE_URL + '/api/v1';

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtAmt(n) {
    const abs = Math.abs(n);
    const s = abs >= 1000000
        ? (abs/1000000).toFixed(1) + 'M'
        : abs >= 1000
            ? (abs/1000).toFixed(0) + 'K'
            : abs.toFixed(0);
    return (n < 0 ? '-' : '') + 'Tsh ' + s;
}

async function loadDeptCards() {
    const grid  = document.getElementById('dept-cards-grid');
    const empty = document.getElementById('dept-cards-empty');
    const err   = document.getElementById('dept-cards-err');
    grid.innerHTML = '<p class="col-span-full text-center text-sm text-mist-400 py-10">Loading...</p>';
    empty.classList.add('hidden');
    err.classList.add('hidden');

    try {
        const res  = await fetch(DEPTS_API + '/departments');
        const json = await res.json();
        const rows = json.data || [];

        if (!rows.length) {
            grid.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }

        const withStats = await Promise.all(rows.map(async d => {
            try {
                const r = await fetch(`${DEPTS_API}/departments/${d.id}/overview`);
                const j = await r.json();
                if (!r.ok) console.warn(`Dept ${d.id} overview failed:`, j);
                return { ...d, stats: j.data ?? {} };
            } catch(err) { console.error(`Dept ${d.id} overview error:`, err); return { ...d, stats: {} }; }
        }));

        grid.innerHTML = withStats.map(d => {
            const s       = d.stats;
            const balance = parseFloat(s.balance ?? 0);
            const balCls  = balance >= 0 ? 'text-emerald-600' : 'text-red-500';
            const active  = parseInt(d.is_active ?? 1);
            const initial = (d.name || 'D').charAt(0).toUpperCase();

            return `
            <a href="${BASE_URL}/departments/${d.id}"
               class="bg-white rounded-2xl border border-mist-200 shadow-sm hover:shadow-md hover:border-royal-200 transition-all duration-150 p-5 flex flex-col gap-4 cursor-pointer">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-royal-100 text-royal-700 flex items-center justify-center font-bold font-heading text-xl shrink-0">
                            ${esc(initial)}
                        </div>
                        <div>
                            <h3 class="font-bold text-royal-800 text-base leading-tight">${esc(d.name)}</h3>
                            <p class="text-xs text-mist-400 mt-0.5">${esc(d.head_name || 'No head assigned')}</p>
                        </div>
                    </div>
                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide ${active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'}">
                        ${active ? 'Active' : 'Inactive'}
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-mist-50 rounded-xl py-2 px-1">
                        <p class="text-lg font-bold text-royal-800">${s.member_count ?? 0}</p>
                        <p class="text-[10px] text-mist-400 font-semibold uppercase">Members</p>
                    </div>
                    <div class="bg-mist-50 rounded-xl py-2 px-1">
                        <p class="text-lg font-bold text-glory-600">${s.leader_count ?? 0}</p>
                        <p class="text-[10px] text-mist-400 font-semibold uppercase">Leaders</p>
                    </div>
                    <div class="bg-mist-50 rounded-xl py-2 px-1">
                        <p class="text-lg font-bold ${balCls}">${fmtAmt(balance)}</p>
                        <p class="text-[10px] text-mist-400 font-semibold uppercase">Balance</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs">
                    ${d.head_email
                        ? '<span class="inline-flex items-center gap-1 text-emerald-600 font-semibold"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Login Set</span>'
                        : '<span class="inline-flex items-center gap-1 text-amber-500 font-semibold"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg> No Login</span>'
                    }
                    ${(s.submitted_reports ?? 0) > 0
                        ? '<span class="inline-flex items-center gap-1 bg-dawn-100 text-dawn-700 px-2 py-0.5 rounded-full font-semibold">' + s.submitted_reports + ' pending report' + (s.submitted_reports > 1 ? 's' : '') + '</span>'
                        : '<span class="text-mist-300">No pending reports</span>'
                    }
                </div>
                <div class="flex items-center gap-2 mt-1 pt-3 border-t border-mist-100">
                    <button onclick="event.preventDefault();event.stopPropagation();openDeptModal(${esc(JSON.stringify({id:d.id,name:d.name,head_name:d.head_name,description:d.description}))})"
                        class="flex-1 px-2 py-1.5 text-xs font-semibold text-royal-600 bg-royal-50 hover:bg-royal-100 rounded-lg text-center transition">
                        Edit
                    </button>
                    <button onclick="event.preventDefault();event.stopPropagation();openCredModal(${d.id}, '${esc(d.head_email || '')}')"
                        class="flex-1 px-2 py-1.5 text-xs font-semibold ${d.head_email ? 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' : 'text-amber-600 bg-amber-50 hover:bg-amber-100'} rounded-lg text-center transition">
                        ${d.head_email ? 'Update Login' : 'Set Login'}
                    </button>
                </div>
            </a>`;
        }).join('');

    } catch(e) {
        grid.innerHTML = '';
        err.textContent = 'Failed to load: ' + e.message;
        err.classList.remove('hidden');
    }
}

function openDeptModal(dept) {
    document.getElementById('dept-modal-title').textContent = dept ? 'Edit Department' : 'New Department';
    document.getElementById('dept-edit-id').value = dept ? dept.id : '';
    document.getElementById('dept-name').value    = dept ? (dept.name || '') : '';
    document.getElementById('dept-head-name').value = dept ? (dept.head_name || '') : '';
    document.getElementById('dept-description').value = dept ? (dept.description || '') : '';
    document.getElementById('dept-modal').classList.remove('hidden');
}
function closeDeptModal() { document.getElementById('dept-modal').classList.add('hidden'); }

document.getElementById('dept-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('dept-edit-id').value;
    const payload = {
        name: document.getElementById('dept-name').value,
        head_name: document.getElementById('dept-head-name').value,
        description: document.getElementById('dept-description').value,
    };
    try {
        const url    = id ? DEPTS_API + '/departments/' + id : DEPTS_API + '/departments';
        const method = id ? 'PUT' : 'POST';
        const res  = await fetch(url, { method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed');
        closeDeptModal();
        loadDeptCards();
    } catch(err) { alert(err.message); }
});

function openCredModal(deptId, currentEmail) {
    document.getElementById('cred-dept-id').value = deptId;
    document.getElementById('cred-email').value   = currentEmail || '';
    document.getElementById('cred-password').value = '';
    document.getElementById('cred-modal').classList.remove('hidden');
}
function closeCredModal() { document.getElementById('cred-modal').classList.add('hidden'); }

document.getElementById('cred-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('cred-dept-id').value;
    const payload = {
        head_email: document.getElementById('cred-email').value,
        head_password: document.getElementById('cred-password').value,
        head_password_confirm: document.getElementById('cred-password').value,
    };
    try {
        const res  = await fetch(DEPTS_API + '/department-credentials/' + id, {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed');
        closeCredModal();
        loadDeptCards();
    } catch(err) { alert(err.message); }
});

loadDeptCards();
</script>