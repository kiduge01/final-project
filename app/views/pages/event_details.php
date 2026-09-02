<?php
$eventId = (int) ($eventId ?? 0);
$B = $baseUrl ?? '';
?>
<section class="mb-6">
    <a href="<?= $B ?>/events" class="text-sm text-royal-600 hover:text-royal-800">&larr; Back to Events</a>
    <h1 class="mt-2 text-3xl font-heading font-semibold text-royal-900">Event Details</h1>
    <p class="text-mist-600">Complete operational view: overview, budget, tasks, attendance, communication, and report.</p>
</section>

<section class="bg-white rounded-2xl border border-mist-200 shadow-lg p-5">
    <div id="event-header" class="mb-4"></div>

    <div class="flex flex-wrap gap-2 mb-4" id="tab-buttons">
        <button class="event-tab px-4 py-2 rounded-xl bg-royal-600 text-white" data-tab="overview">Overview</button>
        <button class="event-tab px-4 py-2 rounded-xl bg-mist-100 text-mist-700" data-tab="budget">Budget</button>
        <button class="event-tab px-4 py-2 rounded-xl bg-mist-100 text-mist-700" data-tab="tasks">Tasks</button>
        <button class="event-tab px-4 py-2 rounded-xl bg-mist-100 text-mist-700" data-tab="attendance">Attendance</button>
        <button class="event-tab px-4 py-2 rounded-xl bg-mist-100 text-mist-700" data-tab="communication">Communication</button>
        <button class="event-tab px-4 py-2 rounded-xl bg-mist-100 text-mist-700" data-tab="report">Report</button>
    </div>

    <div id="tab-content"></div>
</section>

<script>
const EVENT_ID = <?= $eventId ?>;
const TAB_WHITELIST = ['overview', 'budget', 'tasks', 'attendance', 'communication', 'report'];
const requestedTab = new URLSearchParams(window.location.search).get('tab');
let detailsData = null;
let currentTab = TAB_WHITELIST.includes(requestedTab) ? requestedTab : 'overview';
let financeCategories = [];
let memberOptions = [];

function money(v) { return Number(v || 0).toLocaleString(); }

function statusBadge(status) {
    const map = {
        draft: 'bg-mist-100 text-mist-700',
        planned: 'bg-dawn-100 text-dawn-700',
        ongoing: 'bg-glory-100 text-glory-700',
        completed: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-red-100 text-red-700',
    };
    return map[status] || 'bg-mist-100 text-mist-700';
}

function renderHeader() {
    const ov = detailsData.overview;
    const dt = new Date(ov.start_datetime).toLocaleString([], { dateStyle: 'full', timeStyle: 'short' });
    document.getElementById('event-header').innerHTML = `
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-widest text-mist-500">${ov.event_code}</p>
                <h2 class="text-2xl font-heading font-semibold text-royal-800">${ov.title}</h2>
                <p class="text-sm text-mist-600 mt-1">${dt} | ${ov.location || 'Location pending'}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${statusBadge(ov.status)}">${ov.status}</span>
                <button id="delete-event-btn" class="px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold border border-red-200 transition">🗑 Delete Event</button>
            </div>
        </div>
    `;

    document.getElementById('delete-event-btn').addEventListener('click', () => {
        if (!confirm(`Delete "${ov.title}"?\n\nThis will remove all budget items, tasks, attendance and logs. Cannot be undone.`)) return;
        fetch(BASE_URL + `/api/v1/events/${EVENT_ID}`, { method: 'DELETE' })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { alert(data.message || 'Failed to delete'); return; }
                window.location.href = BASE_URL + '/events';
            })
            .catch(() => alert('Network error. Try again.'));
    });
}



function categoryLabel(category) {
    const map = { conference: 'Worship Service', seminar: 'Seminar', outreach: 'Outreach', fundraiser: 'Fundraiser', youth: 'Youth', choir: 'Choir', other: 'Meeting' };
    return map[category] || category || 'General';
}

function renderOverview() {
    const ov = detailsData.overview;

    return `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl border border-mist-200 p-4">
                <p class="text-xs uppercase text-mist-500 tracking-wider">Description / Agenda</p>
                <p class="text-sm text-mist-700 mt-2">${ov.description || 'No description provided.'}</p>
            </div>
            <div class="rounded-xl border border-mist-200 p-4">
                <p class="text-xs uppercase text-mist-500 tracking-wider">Event Details</p>
                <div class="mt-2 space-y-1.5 text-sm text-mist-700">
                    <p><strong>Type:</strong> ${categoryLabel(ov.event_type)}</p>
                    <p><strong>Pastor on Duty:</strong> ${ov.pastor_on_duty || 'N/A'}</p>
                    <p><strong>Usher on Duty:</strong> ${ov.usher_on_duty || 'N/A'}</p>
                </div>
            </div>
        </div>
    `;
}

function renderBudget() {
    const budget = detailsData.budget;
    const isLocked = budget.locked;
    const status = budget.status || 'draft';
    
        const statusBadge = {
        draft: '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Draft</span>',
        pending_approval: '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Pending Approval</span>',
        approved: '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Approved</span>',
        rejected: '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Rejected</span>',
    }[status] || '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Draft</span>';

    const disabledAttr = isLocked ? 'disabled class="w-full rounded-lg border border-mist-200 px-2 py-1 text-sm bg-gray-50 cursor-not-allowed opacity-60"' : 'class="w-full rounded-lg border border-mist-200 px-2 py-1 text-sm"';
    const numDisabledAttr = isLocked ? 'disabled class="w-32 rounded-lg border border-mist-200 px-2 py-1 text-sm bg-gray-50 cursor-not-allowed opacity-60"' : 'class="w-32 rounded-lg border border-mist-200 px-2 py-1 text-sm"';

    const categoryOptions = (type) => {
        return financeCategories
            .filter((c) => c.category_type === type)
            .map((c) => `<option value="${c.id}">${c.name}</option>`)
            .join('');
    };

    const rows = budget.items.map((item) => `
        <tr class="border-t border-mist-100">
            <td class="px-3 py-2 text-sm">
                <input data-item-id="${item.id}" data-field="item_name" value="${item.item_name}" ${disabledAttr}>
            </td>
            <td class="px-3 py-2 text-sm">
                <select data-item-id="${item.id}" data-field="item_type" ${isLocked ? 'disabled class="rounded-lg border border-mist-200 px-2 py-1 text-sm bg-gray-50 cursor-not-allowed opacity-60"' : 'class="rounded-lg border border-mist-200 px-2 py-1 text-sm"'}>
                    <option value="income" ${item.item_type === 'income' ? 'selected' : ''}>income</option>
                    <option value="expense" ${item.item_type === 'expense' ? 'selected' : ''}>expense</option>
                </select>
            </td>
            <td class="px-3 py-2 text-sm">
                <input data-item-id="${item.id}" data-field="planned_amount" type="number" min="0" step="0.01" value="${item.planned_amount}" ${numDisabledAttr}>
            </td>
            <td class="px-3 py-2 text-sm">
                <input data-item-id="${item.id}" data-field="actual_amount" type="number" min="0" step="0.01" value="${item.actual_amount}" ${numDisabledAttr}>
            </td>
            <td class="px-3 py-2 text-sm">
                ${isLocked ? '<span class="text-xs text-mist-400">Locked</span>' : `<div class="flex flex-wrap items-center gap-2">
                    <select id="budget-cat-${item.id}" class="rounded-lg border border-mist-200 px-2 py-1 text-xs">
                        ${categoryOptions(item.item_type)}
                    </select>
                    <button data-budget-save="${item.id}" class="px-2.5 py-1 rounded-lg bg-royal-600 text-white text-xs">Save</button>
                    <button data-budget-post="${item.id}" class="px-2.5 py-1 rounded-lg border border-royal-300 text-royal-700 text-xs">Post Finance</button>
                </div>`}
            </td>
        </tr>
    `).join('') || '<tr><td colspan="5" class="px-3 py-3 text-sm text-mist-500">No budget items available.</td></tr>';

    // Build action buttons based on status
    let actionButtons = '';
    if (status === 'draft' || status === 'rejected') {
        actionButtons = `<button id="send-budget-to-finance-btn" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition">Send to Finance for Approval</button>`;
        if (status === 'rejected') {
            actionButtons += `<span class="text-xs text-red-500 font-medium">Previously rejected — you can edit and resubmit</span>`;
        }
    } else if (status === 'pending_approval') {
        actionButtons = `<span class="text-sm text-amber-600 font-medium">Budget is with the accountant for review. Fields are locked.</span>`;
    } else if (status === 'approved') {
        actionButtons = `<button id="print-budget-receipt-btn" class="px-4 py-2 rounded-xl bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold transition">Print Budget Receipt</button>
        <span class="text-sm text-emerald-600 font-medium">Approved${budget.approved_by ? ' by ' + budget.approved_by : ''}${budget.approved_at ? ' on ' + new Date(budget.approved_at).toLocaleDateString('en-US', {year:'numeric',month:'short',day:'numeric'}) : ''}</span>`;
    }

    return `
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-royal-800">Event Budget</h3>
                ${statusBadge}
            </div>
            <div class="flex items-center gap-3">${actionButtons}</div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            <div class="rounded-xl bg-mist-50 border border-mist-200 p-3"><p class="text-xs text-mist-500">Planned Budget</p><p class="text-xl font-bold text-royal-800">TZS ${money(budget.planned_budget)}</p></div>
            <div class="rounded-xl bg-mist-50 border border-mist-200 p-3"><p class="text-xs text-mist-500">Actual Expenses</p><p class="text-xl font-bold text-royal-800">TZS ${money(budget.actual_expenses)}</p></div>
            <div class="rounded-xl bg-mist-50 border border-mist-200 p-3"><p class="text-xs text-mist-500">Remaining</p><p class="text-xl font-bold ${budget.remaining_balance >= 0 ? 'text-emerald-700' : 'text-red-600'}">TZS ${money(budget.remaining_balance)}</p></div>
        </div>
        ${!isLocked ? `<form id="budget-item-form" class="rounded-xl border border-mist-200 p-3 mb-4 grid grid-cols-1 md:grid-cols-5 gap-2">
            <select name="item_type" class="rounded-lg border border-mist-200 px-3 py-2 text-sm" required>
                <option value="expense">expense</option>
                <option value="income">income</option>
            </select>
            <input name="item_name" required placeholder="Budget item name" class="rounded-lg border border-mist-200 px-3 py-2 text-sm md:col-span-2">
            <input name="planned_amount" required type="number" min="0" step="0.01" placeholder="Planned amount" class="rounded-lg border border-mist-200 px-3 py-2 text-sm">
            <button type="submit" class="rounded-lg bg-royal-600 text-white px-4 py-2 text-sm font-semibold">Add Item</button>
            <input name="notes" placeholder="Notes (optional)" class="rounded-lg border border-mist-200 px-3 py-2 text-sm md:col-span-5">
        </form>` : ''}
        <div class="overflow-x-auto border border-mist-200 rounded-xl">
            <table class="w-full">
                <thead class="bg-mist-50"><tr><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Item</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Type</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Planned</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Actual</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Actions</th></tr></thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
        ${!isLocked ? '<p class="text-xs text-mist-500 mt-2">Post Finance sends individual items to Finance as pending approval.</p>' : ''}
    `;
}

function renderTasks() {
    const rows = detailsData.tasks.map((task) => `
        <div class="rounded-xl border border-mist-200 p-3">
            <div class="flex items-center justify-between gap-2">
                <p class="font-semibold text-royal-800">${task.title}</p>
                <span class="text-xs px-2 py-1 rounded-full bg-mist-100 text-mist-700">${task.task_status}</span>
            </div>
            <p class="text-sm text-mist-600 mt-1">${task.details || 'No details'}</p>
            <p class="text-xs text-mist-500 mt-2">Assigned: ${task.assigned_to} | Priority: ${task.priority}</p>
        </div>
    `).join('') || '<p class="text-sm text-mist-500">No tasks linked to this event.</p>';

    return `<div class="space-y-3">${rows}</div>`;
}

function renderAttendance() {
    const attendance = detailsData.attendance;
    const members = attendance.members.map((m) => `
        <tr class="border-t border-mist-100">
            <td class="px-3 py-2 text-sm">${m.member_name}</td>
            <td class="px-3 py-2 text-sm">${m.phone || '-'}</td>
            <td class="px-3 py-2 text-sm">${m.status}</td>
            <td class="px-3 py-2 text-sm">
                <div class="flex items-center gap-2">
                    <select id="attendance-status-${m.id}" class="rounded-lg border border-mist-200 px-2 py-1 text-xs">
                        <option value="registered" ${m.status === 'registered' ? 'selected' : ''}>registered</option>
                        <option value="present" ${m.status === 'present' ? 'selected' : ''}>present</option>
                        <option value="absent" ${m.status === 'absent' ? 'selected' : ''}>absent</option>
                    </select>
                    <button data-attendance-save="${m.id}" class="px-2.5 py-1 rounded-lg bg-royal-600 text-white text-xs">Save</button>
                </div>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="4" class="px-3 py-3 text-sm text-mist-500">No attendance records yet.</td></tr>';

    const registerOptions = memberOptions
        .map((m) => `<option value="${m.id}">${m.first_name} ${m.last_name} (${m.member_code})</option>`)
        .join('');

    return `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            <div class="rounded-xl bg-mist-50 border border-mist-200 p-3"><p class="text-xs text-mist-500">Registered</p><p class="text-2xl font-bold text-royal-800">${attendance.registered_count}</p></div>
            <div class="rounded-xl bg-mist-50 border border-mist-200 p-3"><p class="text-xs text-mist-500">Present</p><p class="text-2xl font-bold text-royal-800">${attendance.present_count}</p></div>
            <div class="rounded-xl bg-mist-50 border border-mist-200 p-3"><p class="text-xs text-mist-500">Absent</p><p class="text-2xl font-bold text-royal-800">${attendance.absent_count}</p></div>
        </div>
        <form id="attendance-register-form" class="rounded-xl border border-mist-200 p-3 mb-4 flex flex-wrap items-center gap-2">
            <select name="member_id" required class="rounded-lg border border-mist-200 px-3 py-2 text-sm w-full sm:w-auto sm:min-w-[280px]">
                <option value="">Select participant</option>
                ${registerOptions}
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg bg-royal-600 text-white text-sm font-semibold">Register Participant</button>
        </form>
        <div class="overflow-x-auto border border-mist-200 rounded-xl">
            <table class="w-full"><thead class="bg-mist-50"><tr><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Member</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Phone</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Status</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Update</th></tr></thead><tbody>${members}</tbody></table>
        </div>
    `;
}

function renderCommunication() {
    const rows = detailsData.communication.sms_logs.map((log) => `
        <tr class="border-t border-mist-100">
            <td class="px-3 py-2 text-xs">${log.sent_at || '-'}</td>
            <td class="px-3 py-2 text-xs">${log.phone || '-'}</td>
            <td class="px-3 py-2 text-xs">${log.delivery_status}</td>
            <td class="px-3 py-2 text-xs">${log.message_text}</td>
        </tr>
    `).join('') || '<tr><td colspan="4" class="px-3 py-3 text-sm text-mist-500">No communication logs yet.</td></tr>';

    return `
        <form id="communication-form" class="space-y-3 mb-4">
            <textarea name="message" required rows="3" placeholder="Write reminder message..." class="w-full rounded-xl border border-mist-200 px-3 py-2 text-sm"></textarea>
            <div class="flex items-center gap-4 text-sm">
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="send_email" value="1">Send email</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="send_sms" value="1" checked>Send SMS</label>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-royal-600 text-white hover:bg-royal-700">Send Reminder</button>
        </form>
        <div class="overflow-x-auto border border-mist-200 rounded-xl">
            <table class="w-full"><thead class="bg-mist-50"><tr><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Sent At</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Phone</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Status</th><th class="px-3 py-2 text-left text-xs text-mist-500 uppercase">Message</th></tr></thead><tbody>${rows}</tbody></table>
        </div>
    `;
}

function renderReport() {
    const report = detailsData.report;
    return `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            <div class="rounded-xl border border-mist-200 p-3"><p class="text-xs text-mist-500">Total Income</p><p class="text-xl font-bold text-royal-800">TZS ${money(report.income_total)}</p></div>
            <div class="rounded-xl border border-mist-200 p-3"><p class="text-xs text-mist-500">Total Expenses</p><p class="text-xl font-bold text-royal-800">TZS ${money(report.expense_total)}</p></div>
            <div class="rounded-xl border border-mist-200 p-3"><p class="text-xs text-mist-500">Final Balance</p><p class="text-xl font-bold text-royal-800">TZS ${money(report.net_total)}</p></div>
        </div>
        <div class="rounded-xl border border-mist-200 p-4">
            <p class="text-xs text-mist-500 uppercase tracking-wider">Final Summary</p>
            <p class="text-sm text-mist-700 mt-2">${report.final_summary}</p>
        </div>
    `;
}

function syncTabButtons() {
    document.querySelectorAll('.event-tab').forEach((btn) => {
        const active = btn.dataset.tab === currentTab;
        btn.classList.toggle('bg-royal-600', active);
        btn.classList.toggle('text-white', active);
        btn.classList.toggle('bg-mist-100', !active);
        btn.classList.toggle('text-mist-700', !active);
    });
}

function renderTab() {
    const content = document.getElementById('tab-content');
    if (!detailsData) {
        content.innerHTML = '<p class="text-sm text-mist-500">Loading...</p>';
        return;
    }

    if (currentTab === 'overview') content.innerHTML = renderOverview();
    if (currentTab === 'budget') content.innerHTML = renderBudget();
    if (currentTab === 'tasks') content.innerHTML = renderTasks();
    if (currentTab === 'attendance') content.innerHTML = renderAttendance();
    if (currentTab === 'communication') content.innerHTML = renderCommunication();
    if (currentTab === 'report') content.innerHTML = renderReport();

    const form = document.getElementById('communication-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            const payload = Object.fromEntries(fd.entries());
            payload.send_email = fd.get('send_email') ? 1 : 0;
            payload.send_sms = fd.get('send_sms') ? 1 : 0;

            const res = await fetch(BASE_URL + `/api/v1/events/${EVENT_ID}/communicate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                alert(data.message || 'Failed to send communication');
                return;
            }
            alert(`Queued. SMS: ${data.data.sms_queued}, Email prepared: ${data.data.email_prepared}`);
            await loadDetails();
            currentTab = 'communication';
            syncTabButtons();
            renderTab();
        });
    }

    const budgetItemForm = document.getElementById('budget-item-form');
    if (budgetItemForm) {
        budgetItemForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = Object.fromEntries(new FormData(budgetItemForm).entries());
            const res = await fetch(BASE_URL + `/api/v1/events/${EVENT_ID}/budget-items`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                alert(data.message || 'Failed to add budget item');
                return;
            }
            await loadDetails();
            currentTab = 'budget';
            syncTabButtons();
            renderTab();
        });
    }

    document.querySelectorAll('[data-budget-save]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const itemId = btn.getAttribute('data-budget-save');
            const payload = {
                item_name: document.querySelector(`[data-item-id="${itemId}"][data-field="item_name"]`)?.value || '',
                item_type: document.querySelector(`[data-item-id="${itemId}"][data-field="item_type"]`)?.value || 'expense',
                planned_amount: document.querySelector(`[data-item-id="${itemId}"][data-field="planned_amount"]`)?.value || 0,
                actual_amount: document.querySelector(`[data-item-id="${itemId}"][data-field="actual_amount"]`)?.value || 0,
            };
            const res = await fetch(BASE_URL + `/api/v1/events/${EVENT_ID}/budget-items/${itemId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                alert(data.message || 'Failed to update budget item');
                return;
            }
            await loadDetails();
            currentTab = 'budget';
            syncTabButtons();
            renderTab();
        });
    });

    document.querySelectorAll('[data-budget-post]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const itemId = btn.getAttribute('data-budget-post');
            const amount = prompt('Amount to post to Finance (TZS):');
            if (amount === null) return;
            const payload = {
                category_id: document.getElementById(`budget-cat-${itemId}`)?.value || '',
                amount,
                payment_method: 'cash',
            };
            const res = await fetch(BASE_URL + `/api/v1/events/${EVENT_ID}/budget-items/${itemId}/post-finance`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                alert(data.message || 'Failed to post budget item to finance');
                return;
            }
            alert('Posted to Finance as pending accountant approval.');
            await loadDetails();
            currentTab = 'budget';
            syncTabButtons();
            renderTab();
        });
    });

    // "Send to Finance" button — sends entire event budget
    const sendBudgetBtn = document.getElementById('send-budget-to-finance-btn');
    if (sendBudgetBtn) {
        sendBudgetBtn.addEventListener('click', async () => {
            if (!confirm('Send this entire budget to Finance for accountant approval?')) return;
            sendBudgetBtn.disabled = true;
            sendBudgetBtn.textContent = 'Sending…';
            try {
                const res = await fetch(BASE_URL + `/api/v1/events/${EVENT_ID}/send-budget`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    alert(data.message || 'Failed to send budget to finance');
                    return;
                }
                alert('Budget sent to Finance. Awaiting accountant approval.');
                await loadDetails();
                currentTab = 'budget';
                syncTabButtons();
                renderTab();
            } catch (err) {
                alert('Network error. Please try again.');
            } finally {
                sendBudgetBtn.disabled = false;
                sendBudgetBtn.textContent = 'Send to Finance for Approval';
            }
        });
    }

    // Print Budget Receipt — for approved budgets
    const printReceiptBtn = document.getElementById('print-budget-receipt-btn');
    if (printReceiptBtn) {
        printReceiptBtn.addEventListener('click', () => {
            const budget = detailsData.budget;
            const overview = detailsData.overview;
            const itemRows = budget.items.map(i => `
                <tr>
                    <td style="padding:6px 10px;border-bottom:1px solid #ddd">${i.item_name}</td>
                    <td style="padding:6px 10px;border-bottom:1px solid #ddd;text-transform:capitalize">${i.item_type}</td>
                    <td style="padding:6px 10px;border-bottom:1px solid #ddd;text-align:right">TZS ${parseFloat(i.planned_amount || 0).toLocaleString()}</td>
                    <td style="padding:6px 10px;border-bottom:1px solid #ddd;text-align:right">TZS ${parseFloat(i.actual_amount || 0).toLocaleString()}</td>
                </tr>
            `).join('');

            const receiptHtml = `<!DOCTYPE html><html><head><title>Budget Receipt - ${overview.title}</title>
            <style>body{font-family:Arial,sans-serif;max-width:700px;margin:auto;padding:20px}
            h1{font-size:18px;margin-bottom:4px}h2{font-size:14px;color:#555;margin-bottom:20px}
            table{width:100%;border-collapse:collapse;margin-top:12px}
            th{background:#f5f5f5;text-align:left;padding:8px 10px;border-bottom:2px solid #ccc;font-size:13px}
            td{font-size:13px}
            .meta{display:flex;justify-content:space-between;font-size:13px;color:#444;margin-bottom:16px}
            .totals{margin-top:16px;font-size:14px}
            .totals div{display:flex;justify-content:space-between;padding:4px 0}
            .approval{margin-top:24px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:13px}
            @media print{body{margin:0;padding:10px}.no-print{display:none}}</style></head>
            <body>
            <h1>Budget Receipt</h1>
            <h2>${overview.title}</h2>
            <div class="meta">
                <span><strong>Date:</strong> ${overview.event_date || 'N/A'}</span>
                <span><strong>Location:</strong> ${overview.location || 'N/A'}</span>
            </div>
            <table>
                <thead><tr><th>Item</th><th>Type</th><th style="text-align:right">Planned</th><th style="text-align:right">Actual</th></tr></thead>
                <tbody>${itemRows}</tbody>
            </table>
            <div class="totals">
                <div><span><strong>Planned Budget:</strong></span><span>TZS ${parseFloat(budget.planned_budget || 0).toLocaleString()}</span></div>
                <div><span><strong>Actual Expenses:</strong></span><span>TZS ${parseFloat(budget.actual_expenses || 0).toLocaleString()}</span></div>
                <div><span><strong>Remaining:</strong></span><span>TZS ${parseFloat(budget.remaining_balance || 0).toLocaleString()}</span></div>
            </div>
            <div class="approval">
                <strong>Status:</strong> Approved<br>
                ${budget.approved_by ? '<strong>Approved by:</strong> ' + budget.approved_by + '<br>' : ''}
                ${budget.approved_at ? '<strong>Date:</strong> ' + new Date(budget.approved_at).toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'}) : ''}
            </div>
            <div class="no-print" style="margin-top:20px;text-align:center">
                <button onclick="window.print()" style="padding:8px 24px;border:none;background:#1e3a5f;color:#fff;border-radius:6px;cursor:pointer;font-size:14px">Print</button>
            </div>
            </body></html>`;

            const w = window.open('', '_blank');
            w.document.write(receiptHtml);
            w.document.close();
        });
    }

    const attendanceRegisterForm = document.getElementById('attendance-register-form');
    if (attendanceRegisterForm) {
        attendanceRegisterForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = Object.fromEntries(new FormData(attendanceRegisterForm).entries());
            const res = await fetch(BASE_URL + `/api/v1/events/${EVENT_ID}/attendance/register`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                alert(data.message || 'Failed to register participant');
                return;
            }
            await loadDetails();
            currentTab = 'attendance';
            syncTabButtons();
            renderTab();
        });
    }

    document.querySelectorAll('[data-attendance-save]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const attendanceId = btn.getAttribute('data-attendance-save');
            const status = document.getElementById(`attendance-status-${attendanceId}`)?.value || 'registered';
            const res = await fetch(BASE_URL + `/api/v1/events/${EVENT_ID}/attendance/${attendanceId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status }),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                alert(data.message || 'Failed to update attendance');
                return;
            }
            await loadDetails();
            currentTab = 'attendance';
            syncTabButtons();
            renderTab();
        });
    });
}

async function loadDetails() {
    const res = await fetch(BASE_URL + `/api/v1/events/${EVENT_ID}/details`);
    const payload = await res.json();
    if (!res.ok || !payload.success) {
        throw new Error(payload.message || 'Unable to load event details');
    }

    detailsData = payload.data;
    renderHeader();
    syncTabButtons();
    renderTab();
}

async function loadFinanceCategories() {
    const res = await fetch(BASE_URL + '/api/v1/finance/categories');
    const payload = await res.json();
    if (res.ok && payload.success) {
        financeCategories = payload.data || [];
    }
}

async function loadMembersForAttendance() {
    const res = await fetch(BASE_URL + '/api/v1/members?status=active');
    const payload = await res.json();
    if (res.ok && payload.success) {
        memberOptions = payload.data || [];
    }
}

document.querySelectorAll('.event-tab').forEach((btn) => {
    btn.addEventListener('click', () => {
        currentTab = btn.dataset.tab;
        syncTabButtons();
        renderTab();
    });
});

Promise.all([loadFinanceCategories(), loadMembersForAttendance(), loadDetails()]).catch((error) => {
    console.error(error);
    document.getElementById('tab-content').innerHTML = '<p class="text-sm text-red-600">Unable to load event details.</p>';
});
</script>
