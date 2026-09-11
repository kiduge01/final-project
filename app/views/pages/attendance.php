<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-3xl font-heading font-semibold text-royal-900">Attendance Center</h1>
        <p class="text-mist-600 text-sm mt-0.5">Capture church attendance quickly by counts: men, women, children, youth and guests.</p>
    </div>
    <div class="flex items-center gap-2">
        <label class="text-xs font-semibold uppercase tracking-wider text-mist-500">Month</label>
        <input id="month-filter" type="month" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
    </div>
</div>

<section class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-6">
    <div class="bg-white rounded-2xl border border-mist-200 p-4">
        <p class="text-xs uppercase tracking-wider text-mist-500">Attendance Sessions</p>
        <p id="stat-services" class="text-2xl font-bold text-royal-800 mt-1">0</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 p-4">
        <p class="text-xs uppercase tracking-wider text-blue-600">Men</p>
        <p id="stat-men" class="text-2xl font-bold text-blue-700 mt-1">0</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 p-4">
        <p class="text-xs uppercase tracking-wider text-pink-600">Women</p>
        <p id="stat-women" class="text-2xl font-bold text-pink-700 mt-1">0</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 p-4">
        <p class="text-xs uppercase tracking-wider text-amber-600">Children</p>
        <p id="stat-children" class="text-2xl font-bold text-amber-700 mt-1">0</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 p-4">
        <p class="text-xs uppercase tracking-wider text-purple-600">Youth</p>
        <p id="stat-youth" class="text-2xl font-bold text-purple-700 mt-1">0</p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 p-4">
        <p class="text-xs uppercase tracking-wider text-emerald-600">Guests</p>
        <p id="stat-guests" class="text-2xl font-bold text-emerald-700 mt-1">0</p>
    </div>
</section>

<section class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <article class="lg:col-span-2 bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h2 class="text-lg font-heading font-semibold text-royal-800">Record New Attendance</h2>
            <p id="live-total" class="text-sm text-royal-700 font-semibold">Total: 0</p>
        </div>

        <form id="attendance-form" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            <div class="md:col-span-2 xl:col-span-3">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Select Registered Event / Ibada <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <select id="attendance-event" name="event_id" required class="flex-1 rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                        <option value="">Loading registered events...</option>
                    </select>
                    <a href="<?= htmlspecialchars(\App\Core\Route::get('events')) ?>?action=create" class="px-3 py-2.5 rounded-xl border border-royal-200 text-royal-700 text-sm font-semibold whitespace-nowrap">+ Add Event</a>
                </div>
                <p class="text-xs text-mist-500 mt-1">Event name and date come from Events / Ibada. Use Type below to distinguish multiple sermons/sessions under the same Ibada.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Type <span class="text-red-500">*</span></label>
                <select id="attendance-session-type" name="session_type" required class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="main_service">Main / Single Service</option>
                    <option value="first_sermon">First Sermon</option>
                    <option value="second_sermon">Second Sermon</option>
                    <option value="third_sermon">Third Sermon</option>
                    <option value="other">Other Session</option>
                </select>
                <p class="text-xs text-mist-500 mt-1">Example: one Sunday Worship event can have First Sermon and Second Sermon attendance records.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-blue-700 mb-1">Men</label>
                <input id="men-count" name="men_count" type="number" min="0" value="0" class="w-full rounded-xl border border-blue-200 bg-blue-50/50 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-pink-700 mb-1">Women</label>
                <input id="women-count" name="women_count" type="number" min="0" value="0" class="w-full rounded-xl border border-pink-200 bg-pink-50/50 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-amber-700 mb-1">Children</label>
                <input id="children-count" name="children_count" type="number" min="0" value="0" class="w-full rounded-xl border border-amber-200 bg-amber-50/50 px-3 py-2.5 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-purple-700 mb-1">Youth</label>
                <input id="youth-count" name="youth_count" type="number" min="0" value="0" class="w-full rounded-xl border border-purple-200 bg-purple-50/50 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-emerald-700 mb-1">Guests</label>
                <input id="guests-count" name="guests_count" type="number" min="0" value="0" class="w-full rounded-xl border border-emerald-200 bg-emerald-50/50 px-3 py-2.5 text-sm">
                <p class="text-xs text-mist-500 mt-1">Enter the guest count, then register their details after saving.</p>
            </div>
            <div class="pt-[21px]">
                <div class="rounded-xl border border-royal-200 bg-royal-50 px-3 py-2.5 text-sm text-royal-700 flex items-center justify-between">
                <span>Computed Total</span>
                <strong id="computed-total">0</strong>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="block text-xs font-semibold text-mist-600 mb-1">Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional notes (weather, special guest preacher, combined service...)" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm"></textarea>
            </div>

            <div class="flex items-end justify-end">
                <button type="submit" class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-royal-600 text-white hover:bg-royal-700 text-sm font-semibold">Save Attendance</button>
            </div>
        </form>

        <div id="form-feedback" class="hidden mt-3 rounded-xl px-3 py-2 text-sm"></div>
    </article>

    <article class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
        <h2 class="text-lg font-heading font-semibold text-royal-800 mb-3">Latest Snapshot</h2>
        <div id="latest-box" class="rounded-xl border border-mist-200 bg-mist-50 p-4 text-sm text-mist-600">
            No attendance recorded yet.
        </div>
        <div class="mt-4">
            <p class="text-xs uppercase tracking-wider text-mist-500 mb-2">Monthly Trend</p>
            <div id="trend-bars" class="h-36 flex items-end gap-1.5"></div>
        </div>
    </article>
</section>

<section class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-mist-100 flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-semibold text-royal-800">Attendance History</h2>
        <select id="type-filter" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
            <option value="">All types / sermons</option>
            <option value="main_service">Main / Single Service</option>
            <option value="first_sermon">First Sermon</option>
            <option value="second_sermon">Second Sermon</option>
            <option value="third_sermon">Third Sermon</option>
            <option value="other">Other Session</option>
        </select>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-mist-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Date</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Service</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Type</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">M</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">W</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">C</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Y</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">G</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Total</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Notes</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Actions</th>
                </tr>
            </thead>
            <tbody id="history-body" class="divide-y divide-mist-100"></tbody>
        </table>
    </div>
    <div id="history-empty" class="hidden px-5 py-10 text-center text-mist-500">No attendance snapshots for this filter.</div>
</section>

<script>
const monthEl = document.getElementById('month-filter');
const typeEl = document.getElementById('type-filter');
let attendanceRows = [];
let editingAttendanceId = null;

const now = new Date();
monthEl.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;

function typeLabel(type) {
    const map = {
        main_service: 'Main / Single Service',
        first_sermon: 'First Sermon',
        second_sermon: 'Second Sermon',
        third_sermon: 'Third Sermon',
        sunday_service: 'Sunday Service',
        midweek: 'Midweek',
        prayer: 'Prayer',
        youth_service: 'Youth Service',
        special: 'Special',
        other: 'Other Session',
    };
    return map[type] || type || 'Other';
}

function setFeedback(message, isError = false) {
    const el = document.getElementById('form-feedback');
    el.classList.remove('hidden');
    el.textContent = message;
    el.className = `mt-3 rounded-xl px-3 py-2 text-sm ${isError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}`;
}

function recomputeTotal() {
    const men = Number(document.getElementById('men-count').value || 0);
    const women = Number(document.getElementById('women-count').value || 0);
    const children = Number(document.getElementById('children-count').value || 0);
    const youth = Number(document.getElementById('youth-count').value || 0);
    const guests = Number(document.getElementById('guests-count').value || 0);
    const total = men + women + children + youth + guests;
    document.getElementById('computed-total').textContent = String(total);
    document.getElementById('live-total').textContent = `Total: ${total}`;
}

['men-count', 'women-count', 'children-count', 'youth-count', 'guests-count'].forEach((id) => {
    document.getElementById(id).addEventListener('input', recomputeTotal);
});

function renderTrend(trendRows) {
    const wrap = document.getElementById('trend-bars');
    wrap.innerHTML = '';
    if (!trendRows.length) {
        wrap.innerHTML = '<p class="text-xs text-mist-500">No trend data for this month.</p>';
        return;
    }
    const maxVal = Math.max(...trendRows.map((r) => Number(r.total_count || 0)), 1);
    wrap.innerHTML = trendRows.map((row) => {
        const value = Number(row.total_count || 0);
        const h = Math.max(10, Math.round((value / maxVal) * 120));
        const day = new Date(row.service_date).getDate();
        return `<div class="flex-1 min-w-0 flex flex-col items-center justify-end" title="${row.service_date}: ${value}">
            <div class="w-full rounded-t bg-royal-500/85" style="height:${h}px"></div>
            <span class="text-[10px] text-mist-500 mt-1">${day}</span>
        </div>`;
    }).join('');
}

function renderLatest(latest) {
    const box = document.getElementById('latest-box');
    if (!latest) {
        box.className = 'rounded-xl border border-mist-200 bg-mist-50 p-4 text-sm text-mist-600';
        box.textContent = 'No attendance recorded yet.';
        return;
    }

    box.className = 'rounded-xl border border-royal-200 bg-royal-50 p-4 text-sm text-royal-800';
    box.innerHTML = `
        <p class="font-semibold text-royal-900">${latest.service_name}</p>
        <p class="text-xs mt-0.5">${latest.service_date} • ${typeLabel(latest.session_type || latest.service_type)}</p>
        <p class="mt-2 text-sm">Total: <strong>${latest.total_count}</strong></p>
        <p class="text-xs mt-1 text-royal-700">Men ${latest.men_count} • Women ${latest.women_count} • Children ${latest.children_count} • Youth ${latest.youth_count} • Guests ${latest.guests_count}</p>
    `;
}

async function loadAttendanceEvents() {
    const res = await fetch(BASE_URL + '/api/v1/events');
    const payload = await res.json();
    const rows = (payload.data || []).filter(e => e.status !== 'cancelled');
    const select = document.getElementById('attendance-event');
    select.innerHTML = '<option value="">Select Event / Ibada</option>' + rows.map(e => `<option value="${e.id}">${e.title} — ${String(e.start_datetime).slice(0,16)}</option>`).join('');
}

function selectedAttendanceEventDate() {
    const selected = document.getElementById('attendance-event').selectedOptions[0];
    const label = selected ? selected.textContent : '';
    const match = label.match(/(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : '';
}

function editAttendance(id) {
    const r = attendanceRows.find(x => Number(x.id) === Number(id));
    if (!r) return;
    editingAttendanceId = Number(id);
    document.getElementById('attendance-event').value = r.event_id || '';
    document.getElementById('attendance-session-type').value = r.session_type || 'main_service';
    document.getElementById('men-count').value = r.men_count;
    document.getElementById('women-count').value = r.women_count;
    document.getElementById('children-count').value = r.children_count;
    document.getElementById('youth-count').value = r.youth_count;
    document.getElementById('guests-count').value = r.guests_count;
    document.querySelector('#attendance-form [name="notes"]').value = r.notes || '';
    document.querySelector('#attendance-form button[type="submit"]').textContent = 'Update Attendance';
    recomputeTotal();
    document.getElementById('attendance-form').scrollIntoView({behavior:'smooth',block:'start'});
}
async function deleteAttendance(id) {
    if (!confirm('Delete this attendance record?')) return;
    const res = await fetch(BASE_URL + '/api/v1/attendance/snapshots/' + id, {method:'DELETE'});
    const payload = await res.json();
    setFeedback(payload.message || 'Done', !payload.success);
    if (payload.success) await Promise.all([loadOverview(), loadHistory()]);
}

async function loadOverview() {
    const month = monthEl.value;
    const res = await fetch(BASE_URL + '/api/v1/attendance/overview?month=' + encodeURIComponent(month));
    const payload = await res.json();
    if (!res.ok || !payload.success) {
        throw new Error(payload.message || 'Failed to load attendance overview');
    }

    const s = payload.data.summary || {};
    document.getElementById('stat-services').textContent = String(s.services_count || 0);
    document.getElementById('stat-men').textContent = String(s.men_total || 0);
    document.getElementById('stat-women').textContent = String(s.women_total || 0);
    document.getElementById('stat-children').textContent = String(s.children_total || 0);
    document.getElementById('stat-youth').textContent = String(s.youth_total || 0);
    document.getElementById('stat-guests').textContent = String(s.guests_total || 0);

    renderLatest(payload.data.latest || null);
    renderTrend(payload.data.trend || []);
}

async function loadHistory() {
    const month = monthEl.value;
    const type = typeEl.value;
    const params = new URLSearchParams();
    if (month) params.set('month', month);
    if (type) params.set('type', type);

    const res = await fetch(BASE_URL + '/api/v1/attendance/snapshots?' + params.toString());
    const payload = await res.json();
    if (!res.ok || !payload.success) {
        throw new Error(payload.message || 'Failed to load attendance history');
    }

    const rows = payload.data || [];
    attendanceRows = rows;
    const tbody = document.getElementById('history-body');
    const empty = document.getElementById('history-empty');

    if (!rows.length) {
        tbody.innerHTML = '';
        empty.classList.remove('hidden');
        return;
    }

    empty.classList.add('hidden');
    tbody.innerHTML = rows.map((r) => `
        <tr class="hover:bg-mist-50/60">
            <td class="px-4 py-3 text-mist-700">${r.service_date}</td>
            <td class="px-4 py-3 font-semibold text-royal-800">${r.service_name}</td>
            <td class="px-4 py-3 text-xs text-mist-600">${typeLabel(r.session_type || r.service_type)}</td>
            <td class="px-4 py-3 text-blue-700 font-semibold">${r.men_count}</td>
            <td class="px-4 py-3 text-pink-700 font-semibold">${r.women_count}</td>
            <td class="px-4 py-3 text-amber-700 font-semibold">${r.children_count}</td>
            <td class="px-4 py-3 text-purple-700 font-semibold">${r.youth_count}</td>
            <td class="px-4 py-3 text-emerald-700 font-semibold">${r.guests_count}</td>
            <td class="px-4 py-3 font-bold text-royal-800">${r.total_count}</td>
            <td class="px-4 py-3 text-xs text-mist-500 max-w-72 truncate" title="${r.notes || ''}">${r.notes || '-'}</td>
            <td class="px-4 py-3"><div class="flex gap-2"><button type="button" onclick='alert(JSON.stringify(attendanceRows.find(x=>Number(x.id)===Number(${r.id})),null,2))' class="text-xs font-semibold text-emerald-700">View</button><button type="button" onclick="editAttendance(${r.id})" class="text-xs font-semibold text-royal-700">Update</button><button type="button" onclick="deleteAttendance(${r.id})" class="text-xs font-semibold text-red-600">Delete</button></div></td>
        </tr>
    `).join('');
}

document.getElementById('attendance-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = Object.fromEntries(fd.entries());
    const isEdit = Boolean(editingAttendanceId);
    const serviceDate = selectedAttendanceEventDate();
    const expectedGuestCount = Number(payload.guests_count || 0);

    try {
        const url = BASE_URL + '/api/v1/attendance/snapshots' + (editingAttendanceId ? '/' + editingAttendanceId : '');
        const res = await fetch(url, {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            setFeedback(data.message || 'Failed to record attendance', true);
            return;
        }

        if (!isEdit) {
            const redirectParams = new URLSearchParams({
                tab: 'guests',
                action: 'create-guest',
                service_date: serviceDate,
                expected_guests: String(expectedGuestCount)
            });
            window.location.href = BASE_URL + '/members?' + redirectParams.toString();
            return;
        }

        setFeedback('Attendance updated successfully.');
        e.target.reset();
        editingAttendanceId = null;
        document.querySelector('#attendance-form button[type="submit"]').textContent = 'Save Attendance';
        document.getElementById('attendance-session-type').value = 'main_service';
        await loadAttendanceEvents();
        ['men-count', 'women-count', 'children-count', 'youth-count', 'guests-count'].forEach((id) => { document.getElementById(id).value = '0'; });
        recomputeTotal();
        await Promise.all([loadOverview(), loadHistory()]);
    } catch (error) {
        setFeedback('Network error while saving attendance', true);
    }
});

monthEl.addEventListener('change', () => {
    Promise.all([loadOverview(), loadHistory()]).catch((error) => {
        console.error('Attendance reload failed', error);
    });
});

typeEl.addEventListener('change', () => {
    loadHistory().catch((error) => {
        console.error('Attendance history load failed', error);
    });
});

recomputeTotal();
loadAttendanceEvents();

Promise.all([loadOverview(), loadHistory()]).catch((error) => {
    console.error('Attendance page bootstrap failed', error);
    setFeedback(error.message || 'Failed to load attendance data', true);
});
</script>
