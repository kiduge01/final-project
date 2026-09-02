<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-3xl font-heading font-semibold text-royal-900">Event And Appointment Center</h1>
        <p class="text-mist-600 text-sm">Create appointments and events, track budgets, and hand over finance follow-up smoothly.</p>
    </div>
    <a href="<?= $B ?>/" class="px-4 py-2 rounded-xl bg-royal-600 text-white hover:bg-royal-700 text-sm">Open Calendar Dashboard</a>
</div>

<section class="bg-white rounded-2xl border border-mist-200 shadow-lg p-5 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="text-xl font-heading font-semibold text-royal-800">Create Event Or Appointment</h2>
        <p class="text-xs text-mist-500">If budget is entered, a budget item is auto-created and can be reviewed by accountant.</p>
    </div>

    <form id="create-event-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
        <input name="title" required placeholder="Event Title" class="rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        
        <select id="event-type" name="event_type" required class="rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            <option value="">Type</option>
            <option value="service">Service</option>
            <option value="seminar">Seminar</option>
            <option value="meeting">Meeting</option>
            <option value="appointment">Appointment</option>
        </select>
        
        <input name="date" type="date" required class="rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        <input name="time" type="time" required class="rounded-xl border border-mist-200 px-3 py-2.5 text-sm">

        <input name="location" placeholder="Location" required class="rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        <input id="event-budget" name="budget" type="number" min="0" step="0.01" placeholder="Budget (TZS)" class="rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        
        <input name="pastor_on_duty" placeholder="Pastor on Duty" class="rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        <input name="usher_on_duty" placeholder="Usher on Duty" class="rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        
        <div class="inline-flex items-center gap-3 text-sm text-mist-700 rounded-xl border border-mist-200 px-3 py-2.5">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="send_email" value="1">Email</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="send_sms" value="1">SMS</label>
        </div>

        <textarea id="event-description" name="description" rows="2" placeholder="Description / Agenda" class="md:col-span-2 lg:col-span-3 rounded-xl border border-mist-200 px-3 py-2.5 text-sm"></textarea>
        
        <div class="flex items-end justify-end">
            <button type="submit" class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-royal-600 text-white hover:bg-royal-700 text-sm font-semibold">Create</button>
        </div>
    </form>

    <div id="create-feedback" class="hidden mt-3 rounded-xl px-3 py-2 text-sm"></div>
</section>

<section class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-mist-100 flex flex-wrap items-center gap-3 justify-between">
        <h2 class="font-semibold text-royal-800">All Events</h2>
        <div class="flex flex-wrap gap-2">
            <input id="search-keyword" type="text" placeholder="Search code or title" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
            <input id="filter-month" type="month" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
            <select id="filter-type" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
                <option value="">All categories</option>
                <option value="conference">Worship service</option>
                <option value="seminar">Seminar</option>
                <option value="other">Meeting / Appointment</option>
                <option value="youth">Youth</option>
            </select>
            <select id="filter-group-list" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
                <option value="">All groups</option>
            </select>
            <button id="apply-filters" class="px-3 py-2 rounded-xl bg-mist-100 text-mist-700 hover:bg-mist-200 text-sm">Apply</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-mist-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-600">Code</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-600">Event</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-600">Date</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-600">Time</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-600">Budget</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-600">Workflow</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-600">Pastor</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-600">Usher</th>
                    <th class="px-4 py-3 text-center text-xs uppercase tracking-wider text-mist-600">Actions</th>
                </tr>
            </thead>
            <tbody id="events-body" class="divide-y divide-mist-100"></tbody>
        </table>
    </div>
    <div id="events-empty" class="hidden px-5 py-10 text-center text-mist-500">No events for selected filters.</div>
</section>

<script>
function formatMoney(value) {
    return Number(value || 0).toLocaleString();
}

function statusClass(status) {
    const map = {
        draft: 'bg-mist-100 text-mist-700',
        planned: 'bg-dawn-100 text-dawn-700',
        ongoing: 'bg-glory-100 text-glory-700',
        completed: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-red-100 text-red-700',
    };
    return map[status] || 'bg-mist-100 text-mist-700';
}

function categoryLabel(category) {
    const map = {
        conference: 'Service',
        seminar: 'Seminar',
        other: 'Meeting / Appointment',
        youth: 'Youth',
    };
    return map[category] || category || 'General';
}

function isAppointmentRow(notes) {
    return notes && String(notes).toLowerCase().includes('[event_subtype:appointment]');
}

function extractApptWith(notes) {
    if (!notes) return '';
    const m = String(notes).match(/\[appointment_with:([^\]]+)\]/i);
    return m ? m[1].trim() : '';
}

// ──── TOAST & FEEDBACK SYSTEM ────
function showCreateFeedback(message, isError = false) {
    const el = document.getElementById('create-feedback');
    el.classList.remove('hidden');
    el.textContent = message;
    el.className = `mt-3 rounded-xl px-3 py-2 text-sm ${isError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}`;
    setTimeout(() => { el.classList.add('hidden'); }, 5000);
}

function showToast(message, type = 'success', duration = 4000) {
    // type: 'success', 'error', 'info'
    const container = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    const bgClass = type === 'error' ? 'bg-red-600' : type === 'info' ? 'bg-royal-600' : 'bg-emerald-600';
    
    toast.className = `${bgClass} text-white px-4 py-3 rounded-xl shadow-lg text-sm font-medium animate-in fade-in slide-in-from-top-2 mb-2`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('animate-out', 'fade-out', 'slide-out-to-top-2');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed top-6 right-6 z-50 flex flex-col gap-2';
    document.body.appendChild(container);
    return container;
}

// ──── CONFIRMATION DIALOG ────
function showConfirmDialog(title, message, onConfirm, onCancel = null) {
    let dialog = document.getElementById('confirm-dialog');
    if (!dialog) {
        dialog = document.createElement('div');
        dialog.id = 'confirm-dialog';
        dialog.className = 'hidden fixed inset-0 z-50 overflow-y-auto';
        dialog.innerHTML = `
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50" onclick="closeConfirmDialog()"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 id="confirm-title" class="text-lg font-semibold text-royal-900 mb-2"></h3>
                    <p id="confirm-message" class="text-mist-600 text-sm mb-6"></p>
                    <div class="flex gap-3 justify-end">
                        <button onclick="closeConfirmDialog()" class="px-4 py-2.5 rounded-xl bg-mist-100 text-mist-700 hover:bg-mist-200 text-sm font-semibold transition">Cancel</button>
                        <button id="confirm-btn" onclick="executeConfirmDialog()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition flex items-center gap-2">
                            <span id="confirm-btn-text">Confirm</span>
                            <span id="confirm-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(dialog);
    }
    
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-message').textContent = message;
    dialog.classList.remove('hidden');
    
    window.confirmDialogCallbacks = { onConfirm, onCancel };
}

function closeConfirmDialog() {
    const dialog = document.getElementById('confirm-dialog');
    if (dialog) dialog.classList.add('hidden');
    const btn = document.getElementById('confirm-btn');
    btn.disabled = false;
    document.getElementById('confirm-spinner').classList.add('hidden');
    document.getElementById('confirm-btn-text').classList.remove('hidden');
    if (window.confirmDialogCallbacks?.onCancel) window.confirmDialogCallbacks.onCancel();
}

async function executeConfirmDialog() {
    const btn = document.getElementById('confirm-btn');
    btn.disabled = true;
    document.getElementById('confirm-spinner').classList.remove('hidden');
    document.getElementById('confirm-btn-text').classList.add('hidden');
    
    if (window.confirmDialogCallbacks?.onConfirm) {
        try {
            await window.confirmDialogCallbacks.onConfirm();
        } catch (e) {
            console.error('Dialog action failed:', e);
        }
    }
    
    closeConfirmDialog();
}

async function loadGroups() {
    const res = await fetch(BASE_URL + '/api/v1/meta/groups');
    const payload = await res.json();
    const groups = payload.data || [];
    const select = document.getElementById('filter-group-list');
    if (select) {
        select.innerHTML = '<option value="">All groups</option>' + groups.map((g) => `<option value="${g.id}">${g.name}</option>`).join('');
    }
}

async function loadUsers() {
    // Users list can be used for other purposes if needed
    const res = await fetch(BASE_URL + '/api/v1/meta/users');
    const payload = await res.json();
    // Pastor and Usher fields are now manual text inputs
}

function buildBudgetActions(row) {
    if (Number(row.budget_total || 0) <= 0) {
        return '<span class="text-xs text-mist-500">No budget linked</span>';
    }

    const isPending = row.budget_status === 'pending_approval';
    const isApproved = row.budget_status === 'approved';
    const isRejected = row.budget_status === 'rejected';

    let html = `<div class="flex flex-wrap items-center gap-2">`;
    
    if (isPending) {
        html += `<span class="text-xs text-amber-600 font-semibold">Awaiting Approval</span>`;
    } else if (isApproved) {
            html += `<button type="button" data-download-receipt="${row.id}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 hover:underline transition"><svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h6l2 2v8a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/></svg>Receipt</button>`;
        html += `<span class="text-mist-300">|</span>`;
    } else if (isRejected) {
        html += `<span class="text-xs text-red-600 font-semibold">Rejected</span>`;
    }
    
    html += `<a class="text-xs font-semibold text-royal-700 hover:text-royal-900 hover:underline transition" href="${BASE_URL}/events/${row.id}?tab=budget">Edit</a>`;
    
    if (!isPending && !isApproved && !isRejected) {
        html += `<span class="text-mist-300">|</span>`;
        html += `<button type="button" data-send-budget="${row.id}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 hover:underline transition">Send To Finance</button>`;
    }
    
    html += `</div>`;
    return html;
}

async function sendEventBudgetToFinance(eventId) {
    const body = document.getElementById('events-body');
    const row = Array.from(body.querySelectorAll('tr')).find(tr => {
        const cell = tr.querySelector('[data-send-budget]');
        return cell && Number(cell.getAttribute('data-send-budget')) === eventId;
    });
    
    if (!row) return;
    
    const eventTitle = row.querySelector('td:nth-child(2) p')?.textContent || 'Event';
    const budgetCell = row.querySelector('td:nth-child(4)')?.textContent || 'TZS 0';
    
    showConfirmDialog(
        'Send Budget to Finance?',
        `Event: ${eventTitle}\n${budgetCell}\n\nThe accountant will review and approve/reject this budget.`,
        async () => {
            try {
                const res = await fetch(BASE_URL + `/api/v1/events/${eventId}/send-budget`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({}),
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Failed to send budget');
                }

                    showToast('Success: Budget sent to Finance. Accountant will review it.', 'success');
                await loadEvents();
            } catch (err) {
                    showToast('Error: ' + (err.message || 'Failed to send budget'), 'error');
            }
        }
    );
}

function downloadBudgetReceipt(eventId) {
    // Fetch event details and generate a printable receipt
    fetch(BASE_URL + `/api/v1/events/${eventId}/details`)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.data) {
                showToast('Error: Unable to load event details', 'error');
                return;
            }
            
            const overview = data.data.overview;
            const budget = data.data.budget;
            const budgetTotal = Number(overview.budget_total || budget.planned_budget || 0);
            
            // Budget item rows
            const itemRows = (budget.items || []).map(i => `
                <div class="detail-row">
                    <strong>${i.item_name} <span style="font-weight:normal;color:#888;text-transform:capitalize">(${i.item_type})</span></strong>
                    <span>TZS ${parseFloat(i.planned_amount || 0).toLocaleString()} ${i.actual_amount > 0 ? '/ Actual: TZS ' + parseFloat(i.actual_amount).toLocaleString() : ''}</span>
                </div>
            `).join('');
            
            // Create printable HTML
            const html = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Budget Receipt - ${overview.title}</title>
                    <style>
                        body { font-family: 'Segoe UI', Tahoma, Geneva, sans-serif; line-height: 1.6; color: #333; margin: 20px; }
                        .receipt { max-width: 600px; margin: 0 auto; border: 2px solid #1e3a8a; padding: 30px; background: #f9fafb; }
                        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 30px; }
                        .header h1 { margin: 0; color: #1e3a8a; font-size: 28px; }
                        .header p { margin: 5px 0 0; color: #666; font-size: 12px; }
                        .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #e5e7eb; }
                        .detail-row strong { flex: 1; }
                        .detail-row span { flex: 1; text-align: right; font-weight: 600; }
                        .total-row { display: flex; justify-content: space-between; padding: 16px; font-size: 18px; font-weight: bold; background: #f0fdf4; margin: 20px 0; border-radius: 4px; }
                        .total-row span:last-child { color: #10b981; }
                        .items-section { margin: 20px 0; }
                        .items-section h3 { font-size: 14px; color: #1e3a8a; margin-bottom: 8px; }
                        .summary { display: flex; gap: 16px; margin: 16px 0; }
                        .summary div { flex: 1; padding: 12px; background: #f8fafc; border-radius: 8px; text-align: center; }
                        .summary div strong { display: block; font-size: 16px; }
                        .summary div span { font-size: 11px; color: #666; }
                        .meta { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #666; }
                        .approval-stamp { padding: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; margin-top: 16px; text-align: center; }
                        .approval-stamp strong { color: #166534; }
                        .footer { text-align: center; margin-top: 30px; font-size: 11px; color: #999; }
                        .no-print { margin-top: 20px; text-align: center; }
                        @media print { body { margin: 0; } .receipt { border: 1px solid #ccc; } .no-print { display: none; } }
                    </style>
                </head>
                <body>
                    <div class="receipt">
                        <div class="header">
                            <h1>📋 BUDGET RECEIPT</h1>
                            <p>Approved Event Budget</p>
                        </div>
                        
                        <div class="detail-row">
                            <strong>Event Title:</strong>
                            <span>${overview.title}</span>
                        </div>
                        
                        <div class="detail-row">
                            <strong>Event Code:</strong>
                            <span>${overview.event_code || 'N/A'}</span>
                        </div>
                        
                        <div class="detail-row">
                            <strong>Date:</strong>
                            <span>${overview.start_datetime ? new Date(overview.start_datetime).toLocaleDateString([], { year: 'numeric', month: 'long', day: 'numeric' }) : (overview.event_date || 'N/A')}</span>
                        </div>
                        
                        <div class="detail-row">
                            <strong>Category:</strong>
                            <span>${overview.category || 'General'}</span>
                        </div>

                        ${itemRows ? '<div class="items-section"><h3>Budget Items</h3>' + itemRows + '</div>' : ''}

                        <div class="summary">
                            <div><strong>TZS ${parseFloat(budget.planned_budget || 0).toLocaleString()}</strong><span>Planned</span></div>
                            <div><strong>TZS ${parseFloat(budget.actual_expenses || 0).toLocaleString()}</strong><span>Actual</span></div>
                            <div><strong>TZS ${parseFloat(budget.remaining_balance || 0).toLocaleString()}</strong><span>Remaining</span></div>
                        </div>
                        
                        <div class="total-row">
                            <span>Budget Total:</span>
                            <span>TZS ${formatMoney(budgetTotal)}</span>
                        </div>
                        
                        ${budget.status === 'approved' ? '<div class="approval-stamp"><strong>APPROVED</strong><br>' + (budget.approved_by ? 'By: ' + budget.approved_by + '<br>' : '') + (budget.approved_at ? 'Date: ' + new Date(budget.approved_at).toLocaleDateString([], {year:'numeric',month:'long',day:'numeric'}) : '') + '</div>' : ''}
                        
                        <div class="footer">
                            <p>This receipt was generated automatically. For inquiries, contact the Finance Department.</p>
                            <p>Receipt ID: EVT-${overview.id || eventId}-${new Date().getTime()}</p>
                        </div>

                        <div class="no-print">
                            <button onclick="window.print()" style="padding:8px 24px;border:none;background:#1e3a5f;color:#fff;border-radius:6px;cursor:pointer;font-size:14px">Print</button>
                        </div>
                    </div>
                </body>
                </html>
            `;
            
            const win = window.open('', '_blank');
            win.document.write(html);
            win.document.close();
        })
        .catch(err => {
            showToast('Error: ' + (err.message || 'Failed to download receipt'), 'error');
        });
}

async function loadEvents() {
    const month = document.getElementById('filter-month').value;
    const type = document.getElementById('filter-type').value;
    const group = document.getElementById('filter-group-list').value;
    const keyword = document.getElementById('search-keyword').value.trim().toLowerCase();

    const params = new URLSearchParams();
    if (month) params.set('month', month);
    if (type) params.set('type', type);
    if (group) params.set('group', group);

    const res = await fetch(BASE_URL + '/api/v1/events?' + params.toString());
    const payload = await res.json();
    let rows = payload.data || [];

    if (keyword !== '') {
        rows = rows.filter((row) => {
            const haystack = `${row.event_code || ''} ${row.title || ''}`.toLowerCase();
            return haystack.includes(keyword);
        });
    }

    const body = document.getElementById('events-body');
    const empty = document.getElementById('events-empty');

    if (rows.length === 0) {
        body.innerHTML = '';
        empty.classList.remove('hidden');
        return;
    }

    empty.classList.add('hidden');
    body.innerHTML = rows.map((row) => {
        const startDate = new Date(row.start_datetime);
        const date = startDate.toLocaleDateString([], { dateStyle: 'medium' });
        const time = startDate.toLocaleTimeString([], { timeStyle: 'short' });
        const budget = Number(row.budget_total || 0);
        const pastor = row.pastor_on_duty || '-';
        const usher = row.usher_on_duty || '-';
        
        // Build workflow badge
        const statusMap = {
            'draft': { label: 'Draft', color: 'bg-slate-100 text-slate-700' },
            'planned': { label: 'Planned', color: 'bg-blue-100 text-blue-700' },
            'ongoing': { label: 'Ongoing', color: 'bg-amber-100 text-amber-700' },
            'completed': { label: 'Completed', color: 'bg-emerald-100 text-emerald-700' },
            'cancelled': { label: 'Cancelled', color: 'bg-red-100 text-red-700' }
        };
        const statusInfo = statusMap[row.status] || statusMap['draft'];
        const workflowBadge = `<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ${statusInfo.color}">${statusInfo.emoji} ${statusInfo.label}</span>`;
        
        return `
            <tr class="hover:bg-mist-50/70">
                <td class="px-4 py-3 text-xs text-mist-600 font-mono font-bold">${row.event_code}</td>
                <td class="px-4 py-3">
                    <p class="font-semibold text-royal-800">${row.title}</p>
                    <p class="text-xs text-mist-500 mt-0.5">${row.event_type || 'Event'}</p>
                </td>
                <td class="px-4 py-3 text-sm text-mist-700">${date}</td>
                <td class="px-4 py-3 text-sm text-mist-700">${time}</td>
                <td class="px-4 py-3 text-sm text-mist-700">
                    <div class="font-semibold">TZS ${formatMoney(budget)}</div>
                    ${budget > 0 ? '<div class="text-xs text-emerald-600">Linked</div>' : '<div class="text-xs text-mist-400">None</div>'}
                </td>
                <td class="px-4 py-3">${workflowBadge}</td>
                <td class="px-4 py-3 text-sm text-mist-700">${pastor}</td>
                <td class="px-4 py-3 text-sm text-mist-700">${usher}</td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <a href="${BASE_URL}/events/${row.id}" class="px-2.5 py-1 rounded-lg bg-royal-50 hover:bg-royal-100 text-royal-700 text-xs font-semibold">View</a>
                        <button data-edit-event="${row.id}" class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold">Edit</button>
                        <button data-delete-event="${row.id}" class="px-2.5 py-1 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold">Delete</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

document.getElementById('apply-filters').addEventListener('click', loadEvents);
document.getElementById('search-keyword').addEventListener('input', loadEvents);
document.getElementById('events-body').addEventListener('click', async (e) => {
    const deleteBtn = e.target.closest('[data-delete-event]');
    if (deleteBtn) {
        const eventId = Number(deleteBtn.getAttribute('data-delete-event'));
        if (!eventId) return;
        const row = deleteBtn.closest('tr');
        const title = row?.querySelector('td:nth-child(2) p')?.textContent?.trim() || 'this event';
        showConfirmDialog(
            'Delete Event',
            `Are you sure you want to delete "${title}"? This will also remove all budget items, tasks, attendance records and communication logs. This action cannot be undone.`,
            async () => {
                const res = await fetch(BASE_URL + `/api/v1/events/${eventId}`, { method: 'DELETE' });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    showToast('Error: ' + (data.message || 'Failed to delete event'), 'error');
                    return;
                }
                showToast('Success: Event deleted successfully', 'success');
                await loadEvents();
            }
        );
        return;
    }
    
    const editBtn = e.target.closest('[data-edit-event]');
    if (editBtn) {
        const eventId = Number(editBtn.getAttribute('data-edit-event'));
        if (eventId) {
            window.location.href = BASE_URL + `/events/${eventId}`;
        }
        return;
    }
    
    const sendBtn = e.target.closest('[data-send-budget]');
    if (sendBtn) {
        const eventId = Number(sendBtn.getAttribute('data-send-budget'));
        if (eventId) await sendEventBudgetToFinance(eventId);
        return;
    }
    
    const receiptBtn = e.target.closest('[data-download-receipt]');
    if (receiptBtn) {
        const eventId = Number(receiptBtn.getAttribute('data-download-receipt'));
        if (eventId) downloadBudgetReceipt(eventId);
        return;
    }
});



document.getElementById('create-event-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = Object.fromEntries(fd.entries());

    payload.send_email = fd.get('send_email') ? 1 : 0;
    payload.send_sms = fd.get('send_sms') ? 1 : 0;

    const res = await fetch(BASE_URL + '/api/v1/events', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    });

    const data = await res.json();
    if (!res.ok || !data.success) {
        showCreateFeedback(data.message || 'Failed to create event', true);
        return;
    }

    e.target.reset();
    showCreateFeedback('Event created successfully. If budget was entered, it is now available under Budget tab for accountant follow-up.');
    await loadEvents();
});

const now = new Date();
document.getElementById('filter-month').value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;

Promise.all([loadGroups(), loadUsers(), loadEvents()]).catch((error) => {
    console.error('Events page bootstrap failed', error);
});
</script>
