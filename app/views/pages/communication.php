<?php $B = $baseUrl ?? ''; ?>

<div class="communication-module space-y-8">

<!-- Header -->
<div class="mb-6">
    <h1 class="text-3xl font-heading font-semibold text-royal-900">Communication</h1>
    <p class="text-mist-600 text-sm mt-0.5">Send SMS messages to members and groups, and track delivery.</p>
</div>

<!-- ═══ KPI Cards ═══ -->
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
        <p class="text-xs font-semibold text-mist-500 uppercase tracking-wide">Total Sent</p>
        <p id="kpi-total" class="text-2xl font-bold text-royal-800 mt-1">—</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
        <p class="text-xs font-semibold text-mist-500 uppercase tracking-wide">Delivered</p>
        <p id="kpi-delivered" class="text-2xl font-bold text-emerald-700 mt-1">—</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
        <p class="text-xs font-semibold text-mist-500 uppercase tracking-wide">Failed</p>
        <p id="kpi-failed" class="text-2xl font-bold text-red-600 mt-1">—</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
        <p class="text-xs font-semibold text-mist-500 uppercase tracking-wide">This Month</p>
        <p id="kpi-month" class="text-2xl font-bold text-royal-800 mt-1">—</p>
    </div>
</div>

<!-- ═══ TABS ═══ -->
<div class="border-b border-mist-200 mb-6">
    <nav class="flex gap-1 -mb-px" id="comm-tabs">
        <button data-ctab="compose" class="ctab ctab-active px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors">Compose Message</button>
        <button data-ctab="history" class="ctab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors">Message History</button>
    </nav>
</div>

<!-- ═══ TAB: COMPOSE ═══ -->
<div id="ctab-compose" class="ctab-panel">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6">
        <h2 class="font-semibold text-royal-800 mb-4">New Message</h2>
        <form id="compose-form" class="space-y-5">

            <!-- Recipient type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Send To <span class="text-red-500">*</span></label>
                <div class="flex flex-wrap gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="recipient_type" value="all" checked class="w-4 h-4 accent-royal-600">
                        <span class="text-sm font-medium text-gray-700">All Members</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="recipient_type" value="groups" class="w-4 h-4 accent-royal-600">
                        <span class="text-sm font-medium text-gray-700">Groups</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="recipient_type" value="members" class="w-4 h-4 accent-royal-600">
                        <span class="text-sm font-medium text-gray-700">Selected Members</span>
                    </label>
                </div>
            </div>

            <!-- Group selector (hidden by default) -->
            <div id="group-selector" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Groups <span class="text-red-500">*</span></label>
                <div id="group-list" class="border border-gray-200 rounded-xl p-3 max-h-48 overflow-y-auto space-y-1.5">
                    <p class="text-sm text-gray-400">Loading groups…</p>
                </div>
            </div>

            <!-- Member selector (hidden by default) -->
            <div id="member-selector" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Members <span class="text-red-500">*</span></label>
                <input id="member-search" type="text" placeholder="Search by name or phone…" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm mb-2 focus:ring-2 focus:ring-royal-400">
                <div class="flex items-center gap-3 mb-2">
                    <button type="button" onclick="toggleAllMembers(true)" class="text-xs text-royal-600 hover:underline font-semibold">Select All</button>
                    <button type="button" onclick="toggleAllMembers(false)" class="text-xs text-red-500 hover:underline font-semibold">Clear All</button>
                    <span id="member-count" class="text-xs text-mist-500 ml-auto">0 selected</span>
                </div>
                <div id="member-list" class="border border-gray-200 rounded-xl p-3 max-h-60 overflow-y-auto space-y-1">
                    <p class="text-sm text-gray-400">Loading members…</p>
                </div>
            </div>

            <!-- Message -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                <textarea id="msg-text" rows="4" maxlength="480" required placeholder="Type your message here…"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 resize-none"></textarea>
                <div class="flex justify-between mt-1">
                    <p class="text-xs text-mist-500">SMS messages &gt; 160 chars may be split into multiple parts</p>
                    <p class="text-xs text-mist-500"><span id="char-count">0</span>/480</p>
                </div>
            </div>

            <!-- Error / Success feedback -->
            <div id="compose-feedback" class="hidden text-sm px-3 py-2 rounded-lg"></div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-2">
                <p id="recipient-preview" class="text-sm text-mist-600"></p>
                <button type="submit" id="send-btn" class="inline-flex items-center gap-2 px-6 py-2.5 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ TAB: HISTORY ═══ -->
<div id="ctab-history" class="ctab-panel hidden">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
        <div id="hist-loading" class="px-6 py-10 text-center text-sm text-gray-400">Loading…</div>
        <div id="hist-table-wrap" class="hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Message</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Recipients</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Sent</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Failed</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Sent By</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Details</th>
                    </tr>
                </thead>
                <tbody id="hist-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="hist-empty" class="hidden px-6 py-12 text-center text-gray-400">
            <p class="font-medium">No messages sent yet. Use the Compose tab to send your first message.</p>
        </div>
        <!-- Pagination -->
        <div id="hist-pagination" class="hidden flex items-center justify-between px-5 py-3 border-t border-gray-100">
            <p id="hist-page-info" class="text-xs text-gray-500"></p>
            <div class="flex gap-2">
                <button id="hist-prev" onclick="loadHistory(currentHistPage - 1)" class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold disabled:opacity-40" disabled>Prev</button>
                <button id="hist-next" onclick="loadHistory(currentHistPage + 1)" class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold disabled:opacity-40" disabled>Next</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL: Message Detail ═══ -->
<div id="detail-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeDetailModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Message Details</h3>
                <button onclick="closeDetailModal()" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="detail-content" class="space-y-4">
                <!-- Filled by JS -->
            </div>
        </div>
    </div>
</div>

</div><!-- /communication-module -->

<script>
const CAPI = BASE_URL + '/api/v1';

// ── Tab switching ──
document.querySelectorAll('.ctab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.ctab').forEach(t => {
            t.classList.remove('ctab-active','border-royal-600','text-royal-700');
            t.classList.add('border-transparent','text-mist-500');
        });
        btn.classList.add('ctab-active','border-royal-600','text-royal-700');
        btn.classList.remove('border-transparent','text-mist-500');
        document.querySelectorAll('.ctab-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('ctab-' + btn.dataset.ctab).classList.remove('hidden');
        if (btn.dataset.ctab === 'history') loadHistory();
    });
});
document.querySelector('.ctab[data-ctab="compose"]').classList.add('border-royal-600','text-royal-700');

function esc(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ═══════ COMPOSE TAB ═══════
let allMembers = [];
let allGroups = [];

// ── Character counter ──
document.getElementById('msg-text').addEventListener('input', function() {
    document.getElementById('char-count').textContent = this.value.length;
});

// ── Recipient type toggle ──
document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('group-selector').classList.toggle('hidden', this.value !== 'groups');
        document.getElementById('member-selector').classList.toggle('hidden', this.value !== 'members');
        updateRecipientPreview();
    });
});

// ── Load groups for selector ──
async function loadGroupOptions() {
    try {
        const res = await fetch(CAPI + '/messages/recipients/groups');
        const data = await res.json();
        allGroups = data.data || [];
        const container = document.getElementById('group-list');
        if (!allGroups.length) { container.innerHTML = '<p class="text-sm text-gray-400">No active groups found.</p>'; return; }
        container.innerHTML = allGroups.map(g =>
            `<label class="flex items-center gap-2 cursor-pointer p-1.5 hover:bg-gray-50 rounded-lg">
                <input type="checkbox" class="grp-check w-4 h-4 rounded accent-royal-600" value="${g.id}" onchange="updateRecipientPreview()">
                <span class="text-sm text-gray-700">${esc(g.name)}</span>
                <span class="text-xs text-mist-400 ml-auto">${g.member_count || 0} members</span>
            </label>`
        ).join('');
    } catch (e) { console.error('Failed to load groups:', e); }
}

// ── Load members for selector ──
async function loadMemberOptions() {
    try {
        const res = await fetch(CAPI + '/messages/recipients/members');
        const data = await res.json();
        allMembers = data.data || [];
        renderMemberList(allMembers);
    } catch (e) { console.error('Failed to load members:', e); }
}

function renderMemberList(members) {
    const container = document.getElementById('member-list');
    if (!members.length) { container.innerHTML = '<p class="text-sm text-gray-400">No members with phone numbers found.</p>'; return; }
    container.innerHTML = members.map(m =>
        `<label class="flex items-center gap-2 cursor-pointer p-1.5 hover:bg-gray-50 rounded-lg member-row" data-search="${esc((m.first_name + ' ' + m.last_name + ' ' + m.phone).toLowerCase())}">
            <input type="checkbox" class="mbr-check w-3.5 h-3.5 rounded accent-royal-600" value="${m.id}" onchange="updateMemberCount()">
            <span class="text-sm text-gray-700">${esc(m.first_name)} ${esc(m.last_name)}</span>
            <span class="text-xs text-mist-400 ml-auto">${esc(m.phone)}</span>
        </label>`
    ).join('');
}

// ── Member search filter ──
document.getElementById('member-search').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.member-row').forEach(row => {
        row.classList.toggle('hidden', q !== '' && !row.dataset.search.includes(q));
    });
});

function toggleAllMembers(checked) {
    document.querySelectorAll('.mbr-check').forEach(cb => { if (!cb.closest('.hidden')) cb.checked = checked; });
    updateMemberCount();
}

function updateMemberCount() {
    const count = document.querySelectorAll('.mbr-check:checked').length;
    document.getElementById('member-count').textContent = count + ' selected';
    updateRecipientPreview();
}

function updateRecipientPreview() {
    const type = document.querySelector('input[name="recipient_type"]:checked').value;
    const el = document.getElementById('recipient-preview');
    if (type === 'all') {
        el.textContent = `Will send to all active members with phone numbers`;
    } else if (type === 'groups') {
        const count = document.querySelectorAll('.grp-check:checked').length;
        el.textContent = count > 0 ? `${count} group(s) selected` : 'No groups selected';
    } else {
        const count = document.querySelectorAll('.mbr-check:checked').length;
        el.textContent = count > 0 ? `${count} member(s) selected` : 'No members selected';
    }
}

// ── Send message ──
document.getElementById('compose-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const feedback = document.getElementById('compose-feedback');
    feedback.classList.add('hidden');
    const btn = document.getElementById('send-btn');

    const type = document.querySelector('input[name="recipient_type"]:checked').value;
    const message = document.getElementById('msg-text').value.trim();

    if (!message) { showFeedback('error', 'Please enter a message'); return; }

    let recipientIds = [];
    if (type === 'groups') {
        recipientIds = Array.from(document.querySelectorAll('.grp-check:checked')).map(c => parseInt(c.value));
        if (!recipientIds.length) { showFeedback('error', 'Please select at least one group'); return; }
    } else if (type === 'members') {
        recipientIds = Array.from(document.querySelectorAll('.mbr-check:checked')).map(c => parseInt(c.value));
        if (!recipientIds.length) { showFeedback('error', 'Please select at least one member'); return; }
    }

    if (!confirm(`Send this message${type === 'all' ? ' to ALL members' : ''}?`)) return;

    btn.disabled = true;
    btn.textContent = 'Sending…';

    try {
        const res = await fetch(CAPI + '/messages/send', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ message, recipient_type: type, recipient_ids: recipientIds })
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to send');

        showFeedback('success', data.message);
        document.getElementById('msg-text').value = '';
        document.getElementById('char-count').textContent = '0';
        loadKPIs();
    } catch (err) {
        showFeedback('error', err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg> Send Message';
    }
});

function showFeedback(type, msg) {
    const el = document.getElementById('compose-feedback');
    el.textContent = msg;
    el.className = type === 'success'
        ? 'text-sm px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700'
        : 'text-sm px-3 py-2 rounded-lg bg-red-50 text-red-600';
    el.classList.remove('hidden');
}

// ═══════ MESSAGE HISTORY ═══════
let currentHistPage = 1;

async function loadHistory(page = 1) {
    currentHistPage = page;
    document.getElementById('hist-loading').classList.remove('hidden');
    document.getElementById('hist-table-wrap').classList.add('hidden');
    document.getElementById('hist-empty').classList.add('hidden');
    document.getElementById('hist-pagination').classList.add('hidden');

    try {
        const res = await fetch(CAPI + '/messages?page=' + page);
        const data = await res.json();
        const rows = data.data || [];
        const meta = data.meta || {};
        document.getElementById('hist-loading').classList.add('hidden');

        if (!rows.length && page === 1) {
            document.getElementById('hist-empty').classList.remove('hidden');
            return;
        }

        document.getElementById('hist-table-wrap').classList.remove('hidden');

        const typeLabels = { all: 'All Members', members: 'Selected Members', groups: 'Groups' };
        const statusClasses = {
            sent: 'bg-emerald-100 text-emerald-700',
            partial: 'bg-yellow-100 text-yellow-700',
            failed: 'bg-red-100 text-red-700',
            sending: 'bg-blue-100 text-blue-700',
            queued: 'bg-gray-100 text-gray-500',
        };

        document.getElementById('hist-tbody').innerHTML = rows.map((r, i) =>
            `<tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 text-xs text-gray-400">${(page - 1) * 30 + i + 1}</td>
                <td class="px-5 py-3 text-sm text-gray-800 max-w-xs truncate" title="${esc(r.message_text)}">${esc(r.message_text.length > 60 ? r.message_text.substring(0, 60) + '…' : r.message_text)}</td>
                <td class="px-5 py-3 text-sm text-gray-600">${typeLabels[r.recipient_type] || r.recipient_type} (${r.recipient_count})</td>
                <td class="px-5 py-3 text-center text-sm font-semibold text-emerald-700">${r.sent_count}</td>
                <td class="px-5 py-3 text-center text-sm font-semibold text-red-600">${r.failed_count}</td>
                <td class="px-5 py-3 text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ${statusClasses[r.status] || 'bg-gray-100 text-gray-500'}">${r.status}</span>
                </td>
                <td class="px-5 py-3 text-sm text-gray-500">${formatDate(r.created_at)}</td>
                <td class="px-5 py-3 text-sm text-gray-500">${esc(r.sent_by_name || '—')}</td>
                <td class="px-5 py-3 text-center">
                    <button onclick="viewMessageDetail(${r.id})" class="px-3 py-1.5 text-xs bg-royal-50 hover:bg-royal-100 text-royal-700 rounded-lg font-semibold transition">View</button>
                </td>
            </tr>`
        ).join('');

        // Pagination
        if (meta.pages > 1) {
            document.getElementById('hist-pagination').classList.remove('hidden');
            document.getElementById('hist-page-info').textContent = `Page ${meta.page} of ${meta.pages} (${meta.total} messages)`;
            document.getElementById('hist-prev').disabled = meta.page <= 1;
            document.getElementById('hist-next').disabled = meta.page >= meta.pages;
        }
    } catch (e) {
        document.getElementById('hist-loading').classList.add('hidden');
        document.getElementById('hist-empty').innerHTML = '<p class="font-medium text-red-500">Failed to load message history.</p>';
        document.getElementById('hist-empty').classList.remove('hidden');
        console.error('History load failed:', e);
    }
}

function formatDate(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' +
           d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}

// ═══════ MESSAGE DETAIL MODAL ═══════
async function viewMessageDetail(id) {
    document.getElementById('detail-modal').classList.remove('hidden');
    document.getElementById('detail-content').innerHTML = '<p class="text-sm text-gray-400 text-center py-6">Loading…</p>';

    try {
        const res = await fetch(CAPI + '/messages/' + id);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        const m = data.data;
        const recipients = m.recipients || [];

        const statusClasses = {
            sent: 'bg-emerald-100 text-emerald-700',
            delivered: 'bg-emerald-100 text-emerald-700',
            failed: 'bg-red-100 text-red-700',
            queued: 'bg-gray-100 text-gray-500',
        };

        let html = `
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-sm text-gray-800 whitespace-pre-wrap">${esc(m.message_text)}</p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500">Sent by:</span> <span class="font-medium">${esc(m.sent_by_name)}</span></div>
                <div><span class="text-gray-500">Date:</span> <span class="font-medium">${formatDate(m.created_at)}</span></div>
                <div><span class="text-gray-500">Recipients:</span> <span class="font-medium">${m.recipient_count}</span></div>
                <div><span class="text-gray-500">Status:</span> <span class="font-medium">${m.sent_count} sent, ${m.failed_count} failed</span></div>
            </div>`;

        if (recipients.length) {
            html += `
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Delivery Details</h4>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-gray-500">Recipient</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-500">Phone</th>
                                <th class="px-3 py-2 text-center font-semibold text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">` +
                recipients.map(r => `
                            <tr>
                                <td class="px-3 py-2 text-gray-700">${esc(r.first_name || '')} ${esc(r.last_name || '')}${r.group_name ? ' <span class="text-gray-400">(' + esc(r.group_name) + ')</span>' : ''}</td>
                                <td class="px-3 py-2 text-gray-500">${esc(r.phone)}</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ${statusClasses[r.delivery_status] || 'bg-gray-100 text-gray-500'}">${r.delivery_status}</span></td>
                            </tr>`
                ).join('') + `
                        </tbody>
                    </table>
                </div>
            </div>`;
        }

        document.getElementById('detail-content').innerHTML = html;
    } catch (e) {
        document.getElementById('detail-content').innerHTML = `<p class="text-sm text-red-500 text-center py-6">Failed to load details: ${esc(e.message)}</p>`;
    }
}

function closeDetailModal() {
    document.getElementById('detail-modal').classList.add('hidden');
}

// ═══════ KPIs ═══════
async function loadKPIs() {
    try {
        const res = await fetch(CAPI + '/messages?page=1');
        const data = await res.json();
        const rows = data.data || [];
        const meta = data.meta || {};

        let totalSent = 0, totalFailed = 0, monthCount = 0;
        const now = new Date();
        const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);

        rows.forEach(r => {
            totalSent += parseInt(r.sent_count) || 0;
            totalFailed += parseInt(r.failed_count) || 0;
            if (new Date(r.created_at) >= monthStart) monthCount++;
        });

        document.getElementById('kpi-total').textContent = meta.total || 0;
        document.getElementById('kpi-delivered').textContent = totalSent;
        document.getElementById('kpi-failed').textContent = totalFailed;
        document.getElementById('kpi-month').textContent = monthCount;
    } catch (e) { console.error('KPI load failed:', e); }
}

// ═══════ INIT ═══════
loadGroupOptions();
loadMemberOptions();
loadKPIs();
updateRecipientPreview();
</script>

<style>
.communication-module .ctab-active { border-color: #3344a5; color: #3344a5; }
</style>
