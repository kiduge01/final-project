<?php
/**
 * Admin - Zones Management Page
 * Manage zones, zone members, ushers, events and offerings
 */
?>

<div class="mb-6 flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-heading font-bold text-royal-800">Zones Management</h1>
        <p class="text-sm text-mist-500 mt-0.5">Manage zones, members, ushers, events and offerings by location</p>
    </div>
    <button onclick="openZoneModal()"
       class="inline-flex items-center gap-2 px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Zone
    </button>
</div>

<!-- Tab Navigation -->
<div class="mb-6 border-b border-mist-200">
    <div class="flex gap-1 flex-wrap">
        <button onclick="switchTab('zones')" class="zone-tab-btn active px-4 py-3 border-b-2 border-royal-600 text-royal-600 font-semibold text-sm" data-tab="zones">
             Zones
        </button>
        <button onclick="switchTab('members')" class="zone-tab-btn px-4 py-3 border-b-2 border-transparent text-mist-600 hover:text-royal-600 font-semibold text-sm" data-tab="members">
             Members
        </button>
        <button onclick="switchTab('ushers')" class="zone-tab-btn px-4 py-3 border-b-2 border-transparent text-mist-600 hover:text-royal-600 font-semibold text-sm" data-tab="ushers">
             Ushers
        </button>
        <button onclick="switchTab('events')" class="zone-tab-btn px-4 py-3 border-b-2 border-transparent text-mist-600 hover:text-royal-600 font-semibold text-sm" data-tab="events">
             Events
        </button>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- ZONES TAB -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div id="tab-zones" class="zone-tab-content">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" id="zones-grid">
        <p class="col-span-full text-center text-sm text-mist-400 py-10">Loading zones...</p>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- MEMBERS TAB -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div id="tab-members" class="zone-tab-content hidden">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm mb-4 p-4">
        <div class="flex flex-wrap gap-3">
            <select id="filter-zone" class="rounded-xl border border-mist-200 px-3 py-2 text-sm font-medium">
                <option value="">-- Select Zone --</option>
            </select>
            <input type="text" id="filter-member-search" placeholder="Search member name, phone..." class="flex-1 min-w-48 rounded-xl border border-mist-200 px-3 py-2 text-sm">
            <button onclick="openAddMemberModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl">
                + Add Member
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-mist-100 flex items-center justify-between">
            <h3 class="font-semibold text-royal-800">Zone Members</h3>
            <span id="members-count" class="text-xs text-mist-500 font-medium">0 members</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-mist-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Member</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Phone</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Zone</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Assigned Date</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="zone-members-tbody" class="divide-y divide-mist-100"></tbody>
            </table>
        </div>
        <div id="zone-members-empty" class="hidden px-5 py-14 text-center text-mist-400">
            <p class="text-3xl mb-2"></p>
            <p class="font-semibold text-mist-600">No members in selected zone</p>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- USHERS TAB -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div id="tab-ushers" class="zone-tab-content hidden">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm mb-4 p-4">
        <div class="flex flex-wrap gap-3">
            <select id="filter-usher-zone" class="rounded-xl border border-mist-200 px-3 py-2 text-sm font-medium">
                <option value="">-- Select Zone --</option>
            </select>
            <select id="filter-usher-role" class="rounded-xl border border-mist-200 px-3 py-2 text-sm font-medium">
                <option value="">All Roles</option>
                <option value="head">Head Usher</option>
                <option value="assistant">Assistant Usher</option>
            </select>
            <button onclick="openAddUsherModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl">
                + Register Usher
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-mist-100 flex items-center justify-between">
            <h3 class="font-semibold text-royal-800">Zone Ushers</h3>
            <span id="ushers-count" class="text-xs text-mist-500 font-medium">0 ushers</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-mist-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Usher Name</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Phone</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Zone</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Role</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Assigned</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="zone-ushers-tbody" class="divide-y divide-mist-100"></tbody>
            </table>
        </div>
        <div id="zone-ushers-empty" class="hidden px-5 py-14 text-center text-mist-400">
            <p class="text-3xl mb-2"></p>
            <p class="font-semibold text-mist-600">No ushers assigned</p>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- EVENTS TAB -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div id="tab-events" class="zone-tab-content hidden">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm mb-4 p-4">
        <div class="flex flex-wrap gap-3">
            <select id="filter-event-zone" class="rounded-xl border border-mist-200 px-3 py-2 text-sm font-medium">
                <option value="">-- Select Zone --</option>
            </select>
            <select id="filter-event-status" class="rounded-xl border border-mist-200 px-3 py-2 text-sm font-medium">
                <option value="">All Status</option>
                <option value="planned">Planned</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <button onclick="openAddEventModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl">
                + Register Event
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-mist-100 flex items-center justify-between">
            <h3 class="font-semibold text-royal-800">Zone Events</h3>
            <span id="events-count" class="text-xs text-mist-500 font-medium">0 events</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-mist-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Event Title</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Zone</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-mist-500">Offerings</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="zone-events-tbody" class="divide-y divide-mist-100"></tbody>
            </table>
        </div>
        <div id="zone-events-empty" class="hidden px-5 py-14 text-center text-mist-400">
            <p class="text-3xl mb-2"></p>
            <p class="font-semibold text-mist-600">No events registered</p>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- CREATE / EDIT ZONE MODAL -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div id="zone-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeZoneModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 id="zone-modal-title" class="text-lg font-bold text-gray-900 mb-4">New Zone</h3>
            <form id="zone-form" class="space-y-4">
                <input type="hidden" id="zone-edit-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zone Name <span class="text-red-500">*</span></label>
                    <input name="name" id="zone-name" required placeholder="e.g. East Zone, West Zone..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-red-500">*</span></label>
                    <input name="location" id="zone-location" required placeholder="e.g. Kariakoo, Ilala..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input name="description" id="zone-description" placeholder="Brief description..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zone Leader (Head Usher)</label>
                    <select name="zone_leader_id" id="zone-leader" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                        <option value="">-- Select Member --</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeZoneModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Save Zone</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- ADD ZONE MEMBER MODAL -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div id="member-zone-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeMemberZoneModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Add Member to Zone</h3>
            <form id="member-zone-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zone <span class="text-red-500">*</span></label>
                    <select name="zone_id" id="member-zone-id" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                        <option value="">-- Select Zone --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member <span class="text-red-500">*</span></label>
                    <select name="member_id" id="member-id" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                        <option value="">-- Select Member --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <input name="notes" placeholder="Optional notes..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeMemberZoneModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- ADD USHER MODAL -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div id="usher-zone-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeUsherZoneModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Register Usher</h3>
            <form id="usher-zone-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zone <span class="text-red-500">*</span></label>
                    <select name="zone_id" id="usher-zone-id" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                        <option value="">-- Select Zone --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member <span class="text-red-500">*</span></label>
                    <select name="member_id" id="usher-member-id" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                        <option value="">-- Select Member --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usher Role <span class="text-red-500">*</span></label>
                    <select name="usher_role" id="usher-role" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                        <option value="">-- Select Role --</option>
                        <option value="head">Head Usher</option>
                        <option value="assistant">Assistant Usher</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeUsherZoneModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Register Usher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- ADD EVENT MODAL -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div id="event-zone-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-start justify-center min-h-screen pt-10 px-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeEventZoneModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Register Zone Event</h3>
            <form id="event-zone-form" class="space-y-4 max-h-96 overflow-y-auto">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zone <span class="text-red-500">*</span></label>
                    <select name="zone_id" id="event-zone-id" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                        <option value="">-- Select Zone --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Title <span class="text-red-500">*</span></label>
                    <input name="title" required placeholder="e.g. Zone Prayer Meeting..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" placeholder="Event details..." rows="3" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Date & Time <span class="text-red-500">*</span></label>
                    <input name="event_date" type="datetime-local" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                    <input name="venue" placeholder="Event location..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expected Attendance</label>
                    <input name="expected_attendance" type="number" min="0" placeholder="Number of attendees..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEventZoneModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Register Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const ZONES_API = BASE_URL + '/api/v1/zones';

// Tab switching
function switchTab(tab) {
    document.querySelectorAll('.zone-tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.zone-tab-btn').forEach(el => el.classList.remove('active', 'border-royal-600', 'text-royal-600'));
    
    document.getElementById('tab-' + tab).classList.remove('hidden');
    event.target.classList.add('active', 'border-royal-600', 'text-royal-600');
    
    // Load data for the tab
    if (tab === 'zones') loadZones();
    else if (tab === 'members') loadZoneMembers();
    else if (tab === 'ushers') loadZoneUshers();
    else if (tab === 'events') loadZoneEvents();
}

function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmtDate(d) { return new Date(d).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }); }
function fmtTime(d) { return new Date(d).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }); }
function fmtAmt(n) {
    const abs = Math.abs(n);
    const s = abs >= 1000000 ? (abs/1000000).toFixed(1) + 'M' : abs >= 1000 ? (abs/1000).toFixed(0) + 'K' : abs.toFixed(0);
    return 'Tsh ' + s;
}

// ═══════════════════════════════════════════════════════════════ ZONES
async function loadZones() {
    try {
        const res = await fetch(ZONES_API);
        const data = await res.json();
        if (data.success) {
            const grid = document.getElementById('zones-grid');
            grid.innerHTML = '';
            if (data.data.length === 0) {
                grid.innerHTML = '<p class="col-span-full text-center text-sm text-mist-400 py-10">No zones found. Click "New Zone" to create one.</p>';
                return;
            }
            data.data.forEach(z => {
                grid.innerHTML += `
                    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 hover:shadow-md transition">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-lg font-bold text-royal-800">${esc(z.name)}</h3>
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold ${z.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-mist-100 text-mist-600'}">
                                ${z.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                        <p class="text-sm text-mist-600 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-mist-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4.5 8-10a8 8 0 10-16 0c0 5.5 8 10 8 10z"/></svg>
                            ${esc(z.location)}
                        </p>
                        ${z.description ? `<p class="text-xs text-mist-500 mb-3">${esc(z.description)}</p>` : ''}
                        <div class="grid grid-cols-3 gap-2 mb-4 pt-3 border-t border-mist-100">
                            <div class="text-center"><p class="text-lg font-bold text-royal-800">${z.member_count || 0}</p><p class="text-xs text-mist-500">Members</p></div>
                            <div class="text-center"><p class="text-lg font-bold text-glory-800">${z.usher_count || 0}</p><p class="text-xs text-mist-500">Ushers</p></div>
                            <div class="text-center"><p class="text-lg font-bold text-dawn-800">${z.event_count || 0}</p><p class="text-xs text-mist-500">Events</p></div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button onclick="viewZoneMembers(${z.id})" class="flex-1 px-3 py-2 text-xs font-medium text-emerald-600 border border-emerald-300 rounded-lg hover:bg-emerald-50" title="View members">Members</button>
                            <button onclick="editZone(${z.id})" class="flex-1 px-3 py-2 text-xs font-medium text-royal-600 border border-royal-300 rounded-lg hover:bg-royal-50">Edit</button>
                            <button onclick="deleteZone(${z.id})" class="flex-1 px-3 py-2 text-xs font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-50">Delete</button>
                        </div>
                    </div>
                `;
            });
            populateZoneSelects(data.data);
        }
    } catch (e) {
        console.error('Error loading zones:', e);
    }
}

async function loadZoneMembers() {
    try {
        const zoneId = document.getElementById('filter-zone').value;
        const url = zoneId ? ZONES_API + '/' + zoneId + '/members' : ZONES_API + '/members';
        const res = await fetch(url);
        const data = await res.json();
        if (data.success) {
            const tbody = document.getElementById('zone-members-tbody');
            const empty = document.getElementById('zone-members-empty');
            tbody.innerHTML = '';
            if (data.data.length === 0) {
                empty.classList.remove('hidden');
                document.getElementById('members-count').textContent = '0 members';
                return;
            }
            empty.classList.add('hidden');
            document.getElementById('members-count').textContent = data.data.length + ' members';
            data.data.forEach(m => {
                tbody.innerHTML += `
                    <tr>
                        <td class="px-4 py-3 font-medium text-royal-800">${esc(m.member_name)}</td>
                        <td class="px-4 py-3 text-mist-600">${esc(m.phone)}</td>
                        <td class="px-4 py-3 text-royal-600 font-medium">${esc(m.zone_name)}</td>
                        <td class="px-4 py-3 text-mist-600 text-sm">${fmtDate(m.assigned_date)}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-lg text-xs font-semibold ${m.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-mist-100 text-mist-600'}">${m.is_active ? 'Active' : 'Inactive'}</span></td>
                        <td class="px-4 py-3 text-right"><button onclick="removeMemberFromZone(${m.id})" class="text-red-600 hover:text-red-800 text-sm font-medium">Remove</button></td>
                    </tr>
                `;
            });
        }
    } catch (e) {
        console.error('Error loading zone members:', e);
    }
}

async function loadZoneUshers() {
    try {
        const res = await fetch(ZONES_API + '/ushers');
        const data = await res.json();
        if (data.success) {
            const tbody = document.getElementById('zone-ushers-tbody');
            const empty = document.getElementById('zone-ushers-empty');
            tbody.innerHTML = '';
            if (data.data.length === 0) {
                empty.classList.remove('hidden');
                document.getElementById('ushers-count').textContent = '0 ushers';
                return;
            }
            empty.classList.add('hidden');
            document.getElementById('ushers-count').textContent = data.data.length + ' ushers';
            data.data.forEach(u => {
                const roleLabel = u.usher_role === 'head' ? '👑 Head Usher' : '🎩 Assistant';
                tbody.innerHTML += `
                    <tr>
                        <td class="px-4 py-3 font-medium text-royal-800">${esc(u.member_name)}</td>
                        <td class="px-4 py-3 text-mist-600">${esc(u.phone)}</td>
                        <td class="px-4 py-3 text-royal-600 font-medium">${esc(u.zone_name)}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-lg text-xs font-semibold ${u.usher_role === 'head' ? 'bg-glory-100 text-glory-700' : 'bg-blue-100 text-blue-700'}">${roleLabel}</span></td>
                        <td class="px-4 py-3 text-mist-600 text-sm">${fmtDate(u.assigned_date)}</td>
                        <td class="px-4 py-3 text-right"><button onclick="removeUsher(${u.id})" class="text-red-600 hover:text-red-800 text-sm font-medium">Remove</button></td>
                    </tr>
                `;
            });
        }
    } catch (e) {
        console.error('Error loading zone ushers:', e);
    }
}

async function loadZoneEvents() {
    try {
        const res = await fetch(ZONES_API + '/events');
        const data = await res.json();
        if (data.success) {
            const tbody = document.getElementById('zone-events-tbody');
            const empty = document.getElementById('zone-events-empty');
            tbody.innerHTML = '';
            if (data.data.length === 0) {
                empty.classList.remove('hidden');
                document.getElementById('events-count').textContent = '0 events';
                return;
            }
            empty.classList.add('hidden');
            document.getElementById('events-count').textContent = data.data.length + ' events';
            data.data.forEach(e => {
                const statusColors = { planned: 'blue', ongoing: 'orange', completed: 'emerald', cancelled: 'red' };
                const color = statusColors[e.status] || 'mist';
                tbody.innerHTML += `
                    <tr>
                        <td class="px-4 py-3 font-medium text-royal-800">${esc(e.title)}</td>
                        <td class="px-4 py-3 text-royal-600">${esc(e.zone_name)}</td>
                        <td class="px-4 py-3 text-mist-600 text-sm">${fmtDate(e.event_date)}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-lg text-xs font-semibold bg-${color}-100 text-${color}-700">${esc(e.status)}</span></td>
                        <td class="px-4 py-3 font-semibold text-emerald-700">${fmtAmt(e.total_offerings || 0)}</td>
                        <td class="px-4 py-3 text-right"><button onclick="viewEventDetails(${e.id})" class="text-royal-600 hover:text-royal-800 text-sm font-medium">View</button></td>
                    </tr>
                `;
            });
        }
    } catch (e) {
        console.error('Error loading zone events:', e);
    }
}

function populateZoneSelects(zones) {
    ['filter-zone', 'filter-usher-zone', 'filter-event-zone', 'member-zone-id', 'usher-zone-id', 'event-zone-id', 'zone-leader'].forEach(id => {
        const select = document.getElementById(id);
        if (!select) return;
        const currentValue = select.value;
        select.innerHTML = '<option value="">-- Select Zone --</option>' + zones.map(z => `<option value="${z.id}">${esc(z.name)}</option>`).join('');
        if (currentValue) select.value = currentValue;
    });
}

// ═════════════════════════════════════════════════════════════════ MODALS
function openZoneModal() {
    document.getElementById('zone-edit-id').value = '';
    document.getElementById('zone-form').reset();
    document.getElementById('zone-modal-title').textContent = 'New Zone';
    document.getElementById('zone-modal').classList.remove('hidden');
}

function closeZoneModal() {
    document.getElementById('zone-modal').classList.add('hidden');
}

function openAddMemberModal() {
    document.getElementById('member-zone-modal').classList.remove('hidden');
}

function closeMemberZoneModal() {
    document.getElementById('member-zone-modal').classList.add('hidden');
    document.getElementById('member-zone-form').reset();
}

function openAddUsherModal() {
    document.getElementById('usher-zone-modal').classList.remove('hidden');
}

function closeUsherZoneModal() {
    document.getElementById('usher-zone-modal').classList.add('hidden');
    document.getElementById('usher-zone-form').reset();
}

function openAddEventModal() {
    document.getElementById('event-zone-modal').classList.remove('hidden');
}

function closeEventZoneModal() {
    document.getElementById('event-zone-modal').classList.add('hidden');
    document.getElementById('event-zone-form').reset();
}

// ═════════════════════════════════════════════════════════════════ CRUD
document.getElementById('zone-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('zone-edit-id').value;
    const url = id ? ZONES_API + '/' + id : ZONES_API;
    const method = id ? 'PUT' : 'POST';
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            closeZoneModal();
            loadZones();
        } else alert(result.message || 'Error saving zone');
    } catch (e) {
        console.error('Error:', e);
        alert('Failed to save zone');
    }
});

document.getElementById('member-zone-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
        const res = await fetch(ZONES_API + '/members', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        const data = await res.json();
        if (data.success) {
            closeMemberZoneModal();
            loadZoneMembers();
        } else alert(data.message || 'Error adding member');
    } catch (e) {
        console.error('Error:', e);
        alert('Failed to add member');
    }
});

document.getElementById('usher-zone-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
        const res = await fetch(ZONES_API + '/ushers', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        const data = await res.json();
        if (data.success) {
            closeUsherZoneModal();
            loadZoneUshers();
        } else alert(data.message || 'Error registering usher');
    } catch (e) {
        console.error('Error:', e);
        alert('Failed to register usher');
    }
});

document.getElementById('event-zone-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
        const res = await fetch(ZONES_API + '/events', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        const data = await res.json();
        if (data.success) {
            closeEventZoneModal();
            loadZoneEvents();
        } else alert(data.message || 'Error registering event');
    } catch (e) {
        console.error('Error:', e);
        alert('Failed to register event');
    }
});

async function editZone(id) {
    try {
        const res = await fetch(ZONES_API + '/' + id);
        const data = await res.json();
        if (data.success) {
            const z = data.data;
            document.getElementById('zone-edit-id').value = z.id;
            document.getElementById('zone-name').value = z.name;
            document.getElementById('zone-location').value = z.location;
            document.getElementById('zone-description').value = z.description || '';
            document.getElementById('zone-leader').value = z.zone_leader_id || '';
            document.getElementById('zone-modal-title').textContent = 'Edit Zone';
            document.getElementById('zone-modal').classList.remove('hidden');
        }
    } catch (e) {
        console.error('Error:', e);
    }
}

async function viewZoneMembers(id) {
    // Switch to members tab
    switchTab('members');
    
    // Set the zone filter to the selected zone
    const zoneSelect = document.getElementById('filter-zone');
    zoneSelect.value = id;
    
    // Load members for this zone
    loadZoneMembers();
}

async function deleteZone(id) {
    if (!confirm('Delete this zone?')) return;
    try {
        const res = await fetch(ZONES_API + '/' + id, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) loadZones();
        else alert(data.message || 'Error deleting zone');
    } catch (e) {
        console.error('Error:', e);
        alert('Failed to delete zone');
    }
}

async function removeMemberFromZone(id) {
    if (!confirm('Remove member from zone?')) return;
    try {
        const res = await fetch(ZONES_API + '/members/' + id, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) loadZoneMembers();
        else alert(data.message || 'Error removing member');
    } catch (e) {
        console.error('Error:', e);
    }
}

async function removeUsher(id) {
    if (!confirm('Remove usher?')) return;
    try {
        const res = await fetch(ZONES_API + '/ushers/' + id, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) loadZoneUshers();
        else alert(data.message || 'Error removing usher');
    } catch (e) {
        console.error('Error:', e);
    }
}

function viewEventDetails(id) {
    // Redirect to event details page or show modal
    window.location.href = BASE_URL + '/events/zone/' + id;
}

// Load members on modal open
document.getElementById('member-zone-modal')?.addEventListener('focus', async () => {
    if (document.getElementById('member-id').innerHTML === '<option value="">-- Select Member --</option>') {
        try {
            const res = await fetch(BASE_URL + '/api/v1/members');
            const data = await res.json();
            if (data.success) {
                document.getElementById('member-id').innerHTML = '<option value="">-- Select Member --</option>' + 
                    data.data.map(m => `<option value="${m.id}">${esc(m.first_name + ' ' + m.last_name)}</option>`).join('');
            }
        } catch (e) { console.error('Error loading members:', e); }
    }
}, true);

// Add event listener for zone filter to reload members when zone selection changes
document.getElementById('filter-zone')?.addEventListener('change', () => {
    loadZoneMembers();
});

// Initial load
loadZones();
</script>
