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
        <p class="text-xs uppercase tracking-wider text-mist-500">Services</p>
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
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Service Date</label>
                <input name="service_date" type="date" required class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Service Name</label>
                <input name="service_name" required placeholder="e.g. Sunday Worship" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-mist-600 mb-1">Service Type</label>
                <select name="service_type" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                    <option value="sunday_service">Sunday Service</option>
                    <option value="midweek">Midweek Service</option>
                    <option value="prayer">Prayer Meeting</option>
                    <option value="youth_service">Youth Service</option>
                    <option value="special">Special Service</option>
                    <option value="other">Other</option>
                </select>
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

<section class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <i class="fas fa-user-plus text-emerald-600 text-lg"></i>
        <h2 class="text-lg font-heading font-semibold text-royal-800">Guest Registration</h2>
    </div>
    <p class="text-sm text-mist-600 mb-4">Register visiting guests who attended the service with their contact information for follow-up.</p>

    <form id="guest-registration-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-semibold text-mist-600 mb-1">First Name <span class="text-red-500">*</span></label>
            <input name="first_name" type="text" required placeholder="John" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-mist-600 mb-1">Last Name <span class="text-red-500">*</span></label>
            <input name="last_name" type="text" required placeholder="Doe" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-mist-600 mb-1">Phone Number <span class="text-red-500">*</span></label>
            <input name="phone" type="tel" required placeholder="+255 700 000000" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-mist-600 mb-1">Location/Area <span class="text-red-500">*</span></label>
            <input name="location" type="text" required placeholder="e.g. Kinondoni, Ilala" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-mist-600 mb-1">Email</label>
            <input name="email" type="email" placeholder="john@example.com" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-mist-600 mb-1">Age Group</label>
            <select name="age_group" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                <option value="">Select age group</option>
                <option value="child">Child (0-12)</option>
                <option value="teen">Teen (13-19)</option>
                <option value="youth">Youth (20-35)</option>
                <option value="adult">Adult (36-60)</option>
                <option value="senior">Senior (60+)</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-mist-600 mb-1">Visit Type <span class="text-red-500">*</span></label>
            <select name="visit_type" required class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
                <option value="first_time">First Time Visitor</option>
                <option value="returning">Returning Visitor</option>
                <option value="referred">Referred by Member</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-mist-600 mb-1">Who Invited?</label>
            <input name="invited_by_name" type="text" placeholder="Name of member (if referred)" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-mist-600 mb-1">Service Date <span class="text-red-500">*</span></label>
            <input name="service_date" type="date" required class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        </div>

        <div class="md:col-span-2 lg:col-span-1">
            <label class="block text-xs font-semibold text-mist-600 mb-1">Follow-up Date</label>
            <input name="follow_up_date" type="date" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm">
        </div>

        <div class="md:col-span-2 lg:col-span-2">
            <label class="block text-xs font-semibold text-mist-600 mb-1">Notes</label>
            <textarea name="notes" rows="2" placeholder="Additional comments about the guest..." class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm"></textarea>
        </div>

        <div class="flex items-end justify-end md:col-span-2 lg:col-span-1">
            <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold">Register Guest</button>
        </div>
    </form>

    <div id="guest-feedback" class="hidden mt-3 rounded-xl px-3 py-2 text-sm"></div>
</section>

<section class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-mist-100 flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-semibold text-royal-800">Attendance History</h2>
        <select id="type-filter" class="rounded-xl border border-mist-200 px-3 py-2 text-sm">
            <option value="">All service types</option>
            <option value="sunday_service">Sunday Service</option>
            <option value="midweek">Midweek</option>
            <option value="prayer">Prayer</option>
            <option value="youth_service">Youth Service</option>
            <option value="special">Special</option>
            <option value="other">Other</option>
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

const now = new Date();
monthEl.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;

function typeLabel(type) {
    const map = {
        sunday_service: 'Sunday Service',
        midweek: 'Midweek',
        prayer: 'Prayer',
        youth_service: 'Youth Service',
        special: 'Special',
        other: 'Other',
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
        <p class="text-xs mt-0.5">${latest.service_date} • ${typeLabel(latest.service_type)}</p>
        <p class="mt-2 text-sm">Total: <strong>${latest.total_count}</strong></p>
        <p class="text-xs mt-1 text-royal-700">Men ${latest.men_count} • Women ${latest.women_count} • Children ${latest.children_count} • Youth ${latest.youth_count} • Guests ${latest.guests_count}</p>
    `;
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
            <td class="px-4 py-3 text-xs text-mist-600">${typeLabel(r.service_type)}</td>
            <td class="px-4 py-3 text-blue-700 font-semibold">${r.men_count}</td>
            <td class="px-4 py-3 text-pink-700 font-semibold">${r.women_count}</td>
            <td class="px-4 py-3 text-amber-700 font-semibold">${r.children_count}</td>
            <td class="px-4 py-3 text-purple-700 font-semibold">${r.youth_count}</td>
            <td class="px-4 py-3 text-emerald-700 font-semibold">${r.guests_count}</td>
            <td class="px-4 py-3 font-bold text-royal-800">${r.total_count}</td>
            <td class="px-4 py-3 text-xs text-mist-500 max-w-72 truncate" title="${r.notes || ''}">${r.notes || '-'}</td>
        </tr>
    `).join('');
}

document.getElementById('attendance-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = Object.fromEntries(fd.entries());

    try {
        const res = await fetch(BASE_URL + '/api/v1/attendance/snapshots', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            setFeedback(data.message || 'Failed to record attendance', true);
            return;
        }

        setFeedback(`Attendance saved successfully. Total captured: ${data.data?.total_count ?? ''}`);
        e.target.reset();
        document.querySelector('[name="service_type"]').value = 'sunday_service';
        document.querySelector('[name="service_date"]').value = new Date().toISOString().slice(0, 10);
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

document.querySelector('[name="service_date"]').value = new Date().toISOString().slice(0, 10);
recomputeTotal();

// Guest registration form handler
const guestForm = document.getElementById('guest-registration-form');
guestForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    function setGuestFeedback(message, isError = false) {
        const el = document.getElementById('guest-feedback');
        el.classList.remove('hidden');
        el.textContent = message;
        el.className = `mt-3 rounded-xl px-3 py-2 text-sm ${isError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}`;
    }

    try {
        const payload = {
            first_name: (e.target.querySelector('[name="first_name"]').value || '').trim(),
            last_name: (e.target.querySelector('[name="last_name"]').value || '').trim(),
            phone: (e.target.querySelector('[name="phone"]').value || '').trim(),
            location: (e.target.querySelector('[name="location"]').value || '').trim(),
            email: (e.target.querySelector('[name="email"]').value || '').trim(),
            age_group: (e.target.querySelector('[name="age_group"]').value || '').trim(),
            visit_type: (e.target.querySelector('[name="visit_type"]').value || 'first_time').trim(),
            invited_by_name: (e.target.querySelector('[name="invited_by_name"]').value || '').trim(),
            service_date: (e.target.querySelector('[name="service_date"]').value || '').trim(),
            follow_up_date: (e.target.querySelector('[name="follow_up_date"]').value || '').trim() || null,
            notes: (e.target.querySelector('[name="notes"]').value || '').trim(),
        };

        // Validate required fields
        if (!payload.first_name || !payload.last_name || !payload.phone || !payload.location || !payload.service_date) {
            setGuestFeedback('Please fill in all required fields (marked with *)', true);
            return;
        }

        const res = await fetch(BASE_URL + '/api/v1/attendance/register-guest', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            setGuestFeedback(data.message || 'Failed to register guest', true);
            return;
        }

        setGuestFeedback(`Guest registered successfully! Guest Code: ${data.data?.guest_code ?? ''}`);
        e.target.reset();
        document.querySelector('[name="service_date"]').value = new Date().toISOString().slice(0, 10);
        document.querySelector('[name="visit_type"]').value = 'first_time';
    } catch (error) {
        setGuestFeedback('Network error while registering guest', true);
        console.error('Guest registration error:', error);
    }
});

Promise.all([loadOverview(), loadHistory()]).catch((error) => {
    console.error('Attendance page bootstrap failed', error);
    setFeedback(error.message || 'Failed to load attendance data', true);
});
</script>
