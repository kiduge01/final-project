<?php $B = $baseUrl ?? ''; ?>

<div class="settings-module space-y-8">

<!-- Header -->
<div class="mb-6">
    <h1 class="text-3xl font-heading font-semibold text-royal-900">Settings</h1>
    <p class="text-mist-600 text-sm mt-0.5">Configure church profile, departments, and system behaviour.</p>
</div>

<!-- ═══ TABS ═══ -->
<div class="border-b border-mist-200 mb-6">
    <nav class="flex gap-1 -mb-px" id="settings-tabs">
        <button data-stab="kanisa" class="stab stab-active px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors">Church Profile</button>
        <button data-stab="idara"  class="stab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors">Departments</button>
        <button data-stab="users"  class="stab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors">Users &amp; Roles</button>
        <button data-stab="approval" class="stab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors">Approval Settings</button>
    </nav>
</div>

<!-- ═══ TAB: KANISA ═══ -->
<div id="stab-kanisa" class="stab-panel">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-royal-800 text-lg">Church Profile</h2>
                <p class="text-xs text-mist-500 mt-0.5">Manage your church identity and contact information</p>
            </div>
            <button onclick="saveChurchProfile()" id="profile-save-btn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Save Profile
            </button>
        </div>

        <!-- ── Logo Section ── -->
        <div class="border border-mist-200 rounded-xl p-5 bg-mist-50/50">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <svg class="w-4 h-4 inline -mt-0.5 mr-1 text-royal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.41a2.25 2.25 0 013.182 0l2.909 2.91M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                Church Logo
            </label>
            <div class="flex flex-col sm:flex-row items-start gap-5">
                <!-- Preview box -->
                <div id="logo-preview" class="relative w-28 h-28 rounded-2xl bg-white border-2 border-dashed border-mist-300 flex items-center justify-center overflow-hidden shrink-0 transition-colors group"
                     ondragover="event.preventDefault(); this.classList.add('border-royal-400','bg-royal-50')"
                     ondragleave="this.classList.remove('border-royal-400','bg-royal-50')"
                     ondrop="handleLogoDrop(event)">
                    <svg class="w-10 h-10 text-mist-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.41a2.25 2.25 0 013.182 0l2.909 2.91M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                </div>
                <!-- Upload controls -->
                <div class="flex flex-col gap-2.5">
                    <label class="inline-flex items-center gap-2 px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl cursor-pointer transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        Upload Logo
                        <input type="file" id="cp-logo-file" accept="image/png,image/jpeg,image/gif,image/webp,image/svg+xml" class="hidden" onchange="uploadChurchLogo(this)">
                    </label>
                    <button type="button" id="logo-remove-btn" onclick="removeChurchLogo()" class="hidden inline-flex items-center gap-1.5 text-xs text-red-500 hover:text-red-700 font-medium transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        Remove logo
                    </button>
                    <p class="text-xs text-mist-400 leading-relaxed">Max 2 MB &middot; JPG, PNG, GIF, WebP, or SVG<br>Drag and drop or click to upload</p>
                </div>
            </div>
        </div>

        <!-- ── Identity Fields ── -->
        <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-royal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
                Church Identity
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Church Name <span class="text-red-500">*</span></label>
                    <input id="cp-church_name" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 transition" placeholder="Enter church name" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input id="cp-location" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 transition" placeholder="City, Country">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pastor / Leader</label>
                    <input id="cp-pastor_name" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 transition" placeholder="Full name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Founded Year</label>
                    <input id="cp-founded_year" type="number" min="1800" max="2099" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 transition" placeholder="e.g. 1995">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input id="cp-address" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 transition" placeholder="Physical address">
                </div>
            </div>
        </div>

        <!-- ── Contact Fields ── -->
        <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-royal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                Contact Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">System Email <span class="text-red-500">*</span></label>
                    <input id="cp-email" type="email" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 transition" placeholder="info@church.org">
                    <p class="text-xs text-mist-400 mt-1">Used as sender for system notifications and password resets</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-mist-400 text-xs font-normal">(optional)</span></label>
                    <input id="cp-phone" type="tel" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 transition" placeholder="+255...">
                </div>
            </div>
        </div>

        <div id="profile-msg" class="hidden text-sm px-4 py-2.5 rounded-xl font-medium transition-all"></div>
    </div>
</div>

<!-- ═══ TAB: IDARA (Departments) ═══ -->
<div id="stab-idara" class="stab-panel hidden">

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-royal-800">Departments</h2>
        <button onclick="openDeptModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Department
        </button>
    </div>

    <!-- Department list -->
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
        <div id="dept-loading" class="px-6 py-10 text-center text-sm text-gray-400">Loading…</div>
        <div id="dept-table-wrap" class="hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Department Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Head of Department</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Login Access</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody id="dept-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="dept-empty" class="hidden px-6 py-12 text-center text-gray-400">
            <p class="font-medium">No departments yet. Click "Add Department" to start.</p>
        </div>
    </div>
</div>

<!-- ═══ TAB: USERS ═══ -->
<div id="stab-users" class="stab-panel hidden">

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-royal-800">Users &amp; Roles</h2>
        <button onclick="openUserModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add User
        </button>
    </div>

    <!-- Users list -->
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
        <div id="users-loading" class="px-6 py-10 text-center text-sm text-gray-400">Loading…</div>
        <div id="users-table-wrap" class="hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Full Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Phone</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="users-empty" class="hidden px-6 py-12 text-center text-gray-400">
            <p class="font-medium">No users found. Click "Add User" to create one.</p>
        </div>
    </div>
</div>

<!-- ═══ MODAL: Add / Edit User ═══ -->
<div id="user-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeUserModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 id="user-modal-title" class="text-lg font-bold text-gray-900">New User</h3>
                <button onclick="closeUserModal()" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="user-form" class="space-y-4">
                <input type="hidden" id="user-edit-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input id="user-name" required placeholder="John Doe" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input id="user-email" type="email" placeholder="john@example.com" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                    <input id="user-phone" required placeholder="0712345678" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span id="pwd-req" class="text-red-500">*</span></label>
                    <input id="user-password" type="password" placeholder="Min 8 characters" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                    <p id="pwd-hint" class="hidden text-xs text-mist-500 mt-1">Leave blank to keep current password</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select id="user-role" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                        <option value="">— Select Role —</option>
                    </select>
                </div>
                <div id="user-active-row" class="hidden">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" id="user-active" class="w-4 h-4 rounded accent-royal-600" checked>
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
                <div id="user-form-error" class="hidden text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeUserModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" id="user-submit-btn" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══ TAB: APPROVAL SETTINGS ═══ -->
<div id="stab-approval" class="stab-panel hidden">

    <!-- ─── Approval Workflows ─── -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-royal-800">Approval Workflows</h2>
                <p class="text-xs text-gray-500 mt-0.5">Define multi-level approval chains for budgets and procurement</p>
            </div>
            <button onclick="openWorkflowModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add Step
            </button>
        </div>
        <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Workflow Type</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Level</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Approver Role</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr></thead>
                    <tbody id="wf-tbody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
            <div id="wf-empty" class="hidden px-5 py-10 text-center text-gray-400">No approval workflows configured</div>
        </div>
    </div>

    <!-- ─── Role Permissions ─── -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-royal-800">Role Permissions</h2>
                <p class="text-xs text-gray-500 mt-0.5">Click toggles to grant or revoke access per role, then save.</p>
            </div>
        </div>
        <!-- Role tabs -->
        <div class="flex flex-wrap gap-2 mb-5" id="rp-role-tabs"></div>
        <!-- Permission panel -->
        <div id="rp-panel" class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6">
            <div id="rp-loading" class="text-sm text-gray-400 text-center py-8">Loading…</div>
        </div>
    </div>
</div>

<!-- ═══ MODAL: Workflow Step ═══ -->
<div id="wf-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeWorkflowModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Approval Workflow Step</h3>
            <form id="wf-form" class="space-y-4">
                <input type="hidden" id="wf-edit-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Workflow Type <span class="text-red-500">*</span></label>
                    <select id="wf-type" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                        <option value="">— Select —</option>
                        <option value="budget">Budget Approval</option>
                        <option value="procurement">Procurement Approval</option>
                        <option value="finance_entry">Finance Entry Approval</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Approval Level <span class="text-red-500">*</span></label>
                    <input type="number" id="wf-level" min="1" max="10" value="1" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Approver Role <span class="text-red-500">*</span></label>
                    <select id="wf-role" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                        <option value="">Loading roles...</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeWorkflowModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══ MODAL: Department Login Credentials ═══ -->
<div id="cred-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeCredModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Department Login</h3>
                    <p id="cred-modal-dept" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <button onclick="closeCredModal()" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Current email banner (when already configured) -->
            <div id="cred-current-wrap" class="hidden mb-4 flex items-center gap-2 px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-sm">
                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-emerald-700 font-medium">Currently: </span>
                <span id="cred-current-email" class="text-emerald-600 truncate"></span>
            </div>

            <form id="cred-form" class="space-y-4">
                <input type="hidden" id="cred-dept-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login Email <span class="text-red-500">*</span></label>
                    <input id="cred-email" type="email" required placeholder="e.g. ibada@church.local"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="cred-password" type="password" required placeholder="Min 6 characters"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 outline-none pr-10">
                        <button type="button" onclick="toggleCredPwd('cred-password', this)"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="cred-confirm" type="password" required placeholder="Repeat password"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400 focus:border-royal-400 outline-none pr-10">
                        <button type="button" onclick="toggleCredPwd('cred-confirm', this)"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Password strength indicator -->
                <div id="cred-strength-wrap" class="hidden">
                    <div class="flex gap-1 mb-1">
                        <div id="cs-bar-1" class="h-1 flex-1 rounded-full bg-gray-200 transition-all"></div>
                        <div id="cs-bar-2" class="h-1 flex-1 rounded-full bg-gray-200 transition-all"></div>
                        <div id="cs-bar-3" class="h-1 flex-1 rounded-full bg-gray-200 transition-all"></div>
                        <div id="cs-bar-4" class="h-1 flex-1 rounded-full bg-gray-200 transition-all"></div>
                    </div>
                    <p id="cs-label" class="text-xs text-gray-400"></p>
                </div>

                <div id="cred-form-error" class="hidden text-sm text-red-600 bg-red-50 px-3 py-2 rounded-xl border border-red-200"></div>
                <div id="cred-form-success" class="hidden text-sm text-emerald-700 bg-emerald-50 px-3 py-2 rounded-xl border border-emerald-200"></div>

                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" onclick="closeCredModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Cancel</button>
                    <button type="submit" id="cred-submit-btn" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        Save Credentials
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══ MODAL: Add / Edit Department ═══ -->
<div id="dept-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeDeptModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 id="dept-modal-title" class="text-lg font-bold text-gray-900">New Department</h3>
                <button onclick="closeDeptModal()" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="dept-form" class="space-y-4">
                <input type="hidden" id="dept-edit-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department Name <span class="text-red-500">*</span></label>
                    <input id="dept-name" name="name" required placeholder="e.g. Vijana" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input id="dept-desc" name="description" placeholder="Brief description..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Head of Department</label>
                    <input id="dept-head" type="text" name="head_name" placeholder="e.g. John Philip" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                    <p class="text-xs text-gray-500 mt-1">Full name of the department head</p>
                </div>
                
                <div id="dept-active-row" class="hidden">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" id="dept-active" class="w-4 h-4 rounded accent-royal-600" checked>
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
                <div id="dept-form-error" class="hidden text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeDeptModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" id="dept-submit-btn" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div><!-- /settings-module -->

<script>
const SAPI = BASE_URL + '/api/v1';

// ── Tab switching ──
document.querySelectorAll('.stab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.stab').forEach(t => {
            t.classList.remove('stab-active','border-royal-600','text-royal-700');
            t.classList.add('border-transparent','text-mist-500');
        });
        btn.classList.add('stab-active','border-royal-600','text-royal-700');
        btn.classList.remove('border-transparent','text-mist-500');
        document.querySelectorAll('.stab-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('stab-' + btn.dataset.stab).classList.remove('hidden');
        if (btn.dataset.stab === 'kanisa') loadChurchProfile();
        if (btn.dataset.stab === 'idara') loadDepartments();
        if (btn.dataset.stab === 'users') loadUsers();
        if (btn.dataset.stab === 'approval') loadApprovalSettings();
    });
});

// ── Load Departments ──
async function loadDepartments() {
    document.getElementById('dept-loading').classList.remove('hidden');
    document.getElementById('dept-table-wrap').classList.add('hidden');
    document.getElementById('dept-empty').classList.add('hidden');
    try {
        const res = await fetch(SAPI + '/departments');
        const data = await res.json();
        const rows = data.data || [];
        document.getElementById('dept-loading').classList.add('hidden');
        if (rows.length === 0) {
            document.getElementById('dept-empty').classList.remove('hidden');
            return;
        }
        document.getElementById('dept-table-wrap').classList.remove('hidden');
        document.getElementById('dept-tbody').innerHTML = rows.map((r, i) =>
            `<tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 text-xs text-gray-400">${i + 1}</td>
                <td class="px-5 py-3 font-semibold text-royal-800">${esc(r.name)}</td>
                <td class="px-5 py-3 text-sm text-gray-500">${esc(r.description || '—')}</td>
                <td class="px-5 py-3 text-sm text-gray-500">${esc(r.head_name || '—')}</td>
                <td class="px-5 py-3">
                    ${r.head_email
                        ? `<div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Configured
                                </span>
                                <span class="text-xs text-gray-400 truncate max-w-[130px]" title="${esc(r.head_email)}">${esc(r.head_email)}</span>
                           </div>`
                        : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                Not Set
                           </span>`
                    }
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ${parseInt(r.is_active) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'}">
                        ${parseInt(r.is_active) ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <button onclick="editDept(${r.id}, ${JSON.stringify(r).replace(/"/g, '&quot;')})"
                            class="px-3 py-1.5 text-xs bg-royal-50 hover:bg-royal-100 text-royal-700 rounded-lg font-semibold transition">Edit</button>
                        <button onclick="openCredModal(${r.id}, '${esc(r.name)}', '${esc(r.head_email || '')}')"
                            class="px-3 py-1.5 text-xs ${r.head_email ? 'bg-violet-50 hover:bg-violet-100 text-violet-700' : 'bg-amber-50 hover:bg-amber-100 text-amber-700'} rounded-lg font-semibold transition">${r.head_email ? 'Login' : 'Set Login'}</button>
                        ${parseInt(r.is_active)
                            ? `<button onclick="deactivateDept(${r.id}, '${esc(r.name)}')"
                                class="px-3 py-1.5 text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded-lg font-semibold transition">Deactivate</button>`
                            : `<button onclick="reactivateDept(${r.id})"
                                class="px-3 py-1.5 text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg font-semibold transition">Reactivate</button>`}
                    </div>
                </td>
            </tr>`
        ).join('');
    } catch (e) {
        document.getElementById('dept-loading').classList.add('hidden');
        document.getElementById('dept-empty').textContent = 'Failed to load departments.';
        document.getElementById('dept-empty').classList.remove('hidden');
    }
}

function esc(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Load users for department head dropdown ──
// (Removed - now using manual text input instead of dropdown)

// ── Modal: Open/Close ──
function openDeptModal(id = null, data = null) {
    document.getElementById('dept-modal-title').textContent = id ? 'Edit Department' : 'New Department';
    document.getElementById('dept-edit-id').value = id || '';
    document.getElementById('dept-name').value = data ? data.name : '';
    document.getElementById('dept-desc').value = data ? (data.description || '') : '';
    document.getElementById('dept-head').value = data ? (data.head_name || '') : '';
    document.getElementById('dept-active-row').classList.toggle('hidden', !id);
    if (id) document.getElementById('dept-active').checked = parseInt(data.is_active) === 1;
    document.getElementById('dept-form-error').classList.add('hidden');
    document.getElementById('dept-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('dept-name').focus(), 100);
}

function closeDeptModal() {
    document.getElementById('dept-modal').classList.add('hidden');
    document.getElementById('dept-form').reset();
}

function editDept(id, data) {
    openDeptModal(id, typeof data === 'string' ? JSON.parse(data) : data);
}

// ── Form submit ──
document.getElementById('dept-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errEl = document.getElementById('dept-form-error');
    errEl.classList.add('hidden');
    const btn = document.getElementById('dept-submit-btn');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const id = document.getElementById('dept-edit-id').value;
    const payload = {
        name: document.getElementById('dept-name').value.trim(),
        description: document.getElementById('dept-desc').value.trim(),
        head_name: document.getElementById('dept-head').value.trim() || null,
    };
    if (id) {
        payload.is_active = document.getElementById('dept-active').checked ? 1 : 0;
    }

    try {
        // Step 1: Save/Update Department
        const res = await fetch(
            id ? `${SAPI}/departments/${id}` : `${SAPI}/departments`,
            { method: id ? 'PUT' : 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) }
        );
        
        // Read response as text first (can only read body once)
        const responseText = await res.text();
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonErr) {
            console.error('Department API returned invalid JSON.');
            console.error('HTTP Status:', res.status);
            console.error('Response:', responseText.substring(0, 300));
            throw new Error(`Server returned invalid JSON (HTTP ${res.status})`);
        }
        
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to save department');
        
        closeDeptModal();
        loadDepartments();
    } catch (err) {
        console.error('Department save error:', err.message);
        errEl.textContent = err.message || 'An unexpected error occurred';
        errEl.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save';
    }
});

// ── Deactivate / Reactivate ──
// ── Credentials Modal ──
function openCredModal(id, name, currentEmail) {
    document.getElementById('cred-dept-id').value = id;
    document.getElementById('cred-modal-dept').textContent = name;
    document.getElementById('cred-email').value = currentEmail || '';
    document.getElementById('cred-password').value = '';
    document.getElementById('cred-confirm').value = '';
    document.getElementById('cred-form-error').classList.add('hidden');
    document.getElementById('cred-form-success').classList.add('hidden');
    document.getElementById('cred-strength-wrap').classList.add('hidden');
    const curWrap = document.getElementById('cred-current-wrap');
    if (currentEmail) {
        document.getElementById('cred-current-email').textContent = currentEmail;
        curWrap.classList.remove('hidden');
    } else {
        curWrap.classList.add('hidden');
    }
    const btn = document.getElementById('cred-submit-btn');
    btn.disabled = false;
    btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg> Save Credentials`;
    document.getElementById('cred-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('cred-email').focus(), 100);
}

function closeCredModal() {
    document.getElementById('cred-modal').classList.add('hidden');
    document.getElementById('cred-form').reset();
}

function toggleCredPwd(inputId, btn) {
    const inp = document.getElementById(inputId);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.querySelector('svg path:last-child').style.display = isText ? '' : 'none';
}

// Password strength meter
document.getElementById('cred-password').addEventListener('input', function() {
    const val = this.value;
    const wrap = document.getElementById('cred-strength-wrap');
    if (!val) { wrap.classList.add('hidden'); return; }
    wrap.classList.remove('hidden');
    let score = 0;
    if (val.length >= 6) score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['bg-red-400', 'bg-amber-400', 'bg-yellow-400', 'bg-emerald-500'];
    const labels = ['Weak', 'Fair', 'Good', 'Strong'];
    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById('cs-bar-' + i);
        bar.className = 'h-1 flex-1 rounded-full transition-all ' + (i <= score ? colors[score - 1] : 'bg-gray-200');
    }
    document.getElementById('cs-label').textContent = labels[score - 1] || '';
});

document.getElementById('cred-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errEl = document.getElementById('cred-form-error');
    const okEl  = document.getElementById('cred-form-success');
    const btn   = document.getElementById('cred-submit-btn');
    errEl.classList.add('hidden');
    okEl.classList.add('hidden');

    const id      = document.getElementById('cred-dept-id').value;
    const email   = document.getElementById('cred-email').value.trim();
    const pwd     = document.getElementById('cred-password').value;
    const confirm = document.getElementById('cred-confirm').value;

    if (pwd !== confirm) {
        errEl.textContent = 'Passwords do not match';
        errEl.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Saving…';

    try {
        const res = await fetch(`${SAPI}/department-credentials/${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ head_email: email, head_password: pwd, head_password_confirm: confirm })
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to save');
        okEl.textContent = 'Login credentials saved successfully!';
        okEl.classList.remove('hidden');
        document.getElementById('cred-password').value = '';
        document.getElementById('cred-confirm').value = '';
        document.getElementById('cred-strength-wrap').classList.add('hidden');
        // Update current email display
        document.getElementById('cred-current-email').textContent = email;
        document.getElementById('cred-current-wrap').classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Saved!`;
        setTimeout(() => { closeCredModal(); loadDepartments(); }, 1200);
    } catch (err) {
        errEl.textContent = err.message || 'An unexpected error occurred';
        errEl.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg> Save Credentials`;
    }
});

// ── Deactivate / Reactivate ──
async function deactivateDept(id, name) {
    if (!confirm(`Deactivate "${name}"? It will be hidden from budget forms.`)) return;
    try {
        const res = await fetch(`${SAPI}/departments/${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        loadDepartments();
    } catch (e) { alert('Error: ' + e.message); }
}

async function reactivateDept(id) {
    try {
        const res = await fetch(`${SAPI}/departments/${id}`, {
            method: 'PUT', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ is_active: 1 })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        loadDepartments();
    } catch (e) { alert('Error: ' + e.message); }
}

// ── Load Church Profile ──
async function loadChurchProfile() {
    try {
        const res = await fetch(SAPI + '/settings/church-profile');
        
        // Read response as text first (can only read body once)
        const responseText = await res.text();
        
        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonErr) {
            // If JSON parsing fails, log the response for debugging
            console.error('Church profile API returned invalid JSON.');
            console.error('HTTP Status:', res.status);
            console.error('Response:', responseText.substring(0, 300));
            throw new Error(`API returned invalid JSON (HTTP ${res.status})`);
        }
        
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Failed to load church profile');
        }
        
        const p = data.data || {};
        const fields = ['church_name','location','phone','email','address','pastor_name','founded_year'];
        fields.forEach(f => {
            const el = document.getElementById('cp-' + f);
            if (el) el.value = p[f] || '';
        });
        // Show logo preview
        const preview = document.getElementById('logo-preview');
        const removeBtn = document.getElementById('logo-remove-btn');
        if (p.church_logo) {
            preview.innerHTML = `<img src="${BASE_URL}${p.church_logo}" alt="Church Logo" class="w-full h-full object-contain p-1">`;
            preview.classList.remove('border-dashed','border-mist-300');
            preview.classList.add('border-solid','border-royal-200');
            removeBtn.classList.remove('hidden');
        } else {
            preview.innerHTML = '<svg class="w-10 h-10 text-mist-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.41a2.25 2.25 0 013.182 0l2.909 2.91M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>';
            preview.classList.add('border-dashed','border-mist-300');
            preview.classList.remove('border-solid','border-royal-200');
            removeBtn.classList.add('hidden');
        }
    } catch (e) { 
        console.error('Failed to load church profile:', e.message);
    }
}

// Drag-and-drop handler for logo
function handleLogoDrop(e) {
    e.preventDefault();
    const preview = e.currentTarget;
    preview.classList.remove('border-royal-400','bg-royal-50');
    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith('image/')) { alert('Please drop an image file'); return; }
    if (file.size > 2 * 1024 * 1024) { alert('File too large (max 2 MB)'); return; }
    // Reuse the upload function with a synthetic input
    const dt = new DataTransfer();
    dt.items.add(file);
    const fileInput = document.getElementById('cp-logo-file');
    fileInput.files = dt.files;
    uploadChurchLogo(fileInput);
}

async function uploadChurchLogo(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 2 * 1024 * 1024) { alert('File too large (max 2 MB)'); input.value = ''; return; }
    const formData = new FormData();
    formData.append('logo', file);
    try {
        const res = await fetch(SAPI + '/settings/church-logo', { method: 'POST', body: formData });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        
        // Update header logo immediately
        if (data.data && data.data.logo_url) {
            const headerLogoImg = document.querySelector('[mh-logo]');
            const fallbackIcon = document.querySelector('[mh-logo-fallback]');
            if (headerLogoImg) {
                headerLogoImg.src = BASE_URL + data.data.logo_url;
                headerLogoImg.classList.remove('hidden');
                headerLogoImg.style.display = '';
            }
            if (fallbackIcon) {
                fallbackIcon.classList.add('hidden');
                fallbackIcon.style.display = 'none';
            }
        }
        
        loadChurchProfile();
        const msgEl = document.getElementById('profile-msg');
        msgEl.textContent = 'Logo uploaded successfully!';
        msgEl.className = 'text-sm px-4 py-2.5 rounded-xl font-medium bg-emerald-50 text-emerald-700';
        msgEl.classList.remove('hidden');
    } catch (e) { alert('Upload failed: ' + e.message); }
    input.value = '';
}

async function removeChurchLogo() {
    if (!confirm('Remove church logo? This will delete the file.')) return;
    try {
        const res = await fetch(SAPI + '/settings/church-logo', { method: 'DELETE' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        
        // Hide header logo and show fallback icon
        const headerLogoImg = document.querySelector('[mh-logo]');
        const fallbackIcon = document.querySelector('[mh-logo-fallback]');
        if (headerLogoImg) {
            headerLogoImg.src = '';
            headerLogoImg.classList.add('hidden');
            headerLogoImg.style.display = 'none';
        }
        if (fallbackIcon) {
            fallbackIcon.classList.remove('hidden');
            fallbackIcon.style.display = '';
        }
        
        loadChurchProfile();
        const msgEl = document.getElementById('profile-msg');
        msgEl.textContent = 'Logo removed successfully';
        msgEl.className = 'text-sm px-4 py-2.5 rounded-xl font-medium bg-emerald-50 text-emerald-700';
        msgEl.classList.remove('hidden');
    } catch (e) { alert('Error: ' + e.message); }
}

async function saveChurchProfile() {
    const btn = document.getElementById('profile-save-btn');
    const msgEl = document.getElementById('profile-msg');
    btn.disabled = true;
    btn.textContent = 'Saving…';
    msgEl.classList.add('hidden');

    const fields = ['church_name','location','phone','email','address','pastor_name','founded_year'];
    const payload = {};
    fields.forEach(f => {
        const el = document.getElementById('cp-' + f);
        if (el) payload[f] = el.value.trim();
    });

    try {
        const res = await fetch(SAPI + '/settings/church-profile', {
            method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed');
        msgEl.textContent = 'Profile saved successfully!';
        msgEl.className = 'text-sm px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700';
        msgEl.classList.remove('hidden');
    } catch (err) {
        msgEl.textContent = 'Error: ' + err.message;
        msgEl.className = 'text-sm px-3 py-2 rounded-lg bg-red-50 text-red-600';
        msgEl.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Save Profile';
    }
}

// Load profile on first render
loadChurchProfile();

// ── Load Users ──
let userRoles = [];

async function loadUsers() {
    document.getElementById('users-loading').classList.remove('hidden');
    document.getElementById('users-table-wrap').classList.add('hidden');
    document.getElementById('users-empty').classList.add('hidden');
    try {
        const [usersRes, rolesRes] = await Promise.all([
            fetch(SAPI + '/settings/users'),
            fetch(SAPI + '/settings/roles')
        ]);
        const usersData = await usersRes.json();
        const rolesData = await rolesRes.json();
        userRoles = rolesData.data || [];
        const rows = usersData.data || [];

        // Populate role select in user modal
        const roleSel = document.getElementById('user-role');
        roleSel.innerHTML = '<option value="">— Select Role —</option>';
        userRoles.forEach(r => { roleSel.innerHTML += '<option value="' + r.id + '">' + esc(r.name) + '</option>'; });

        document.getElementById('users-loading').classList.add('hidden');
        if (rows.length === 0) {
            document.getElementById('users-empty').classList.remove('hidden');
            return;
        }
        document.getElementById('users-table-wrap').classList.remove('hidden');
        document.getElementById('users-tbody').innerHTML = rows.map((r, i) =>
            `<tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 text-xs text-gray-400">${i + 1}</td>
                <td class="px-5 py-3 font-semibold text-royal-800">${esc(r.full_name)}</td>
                <td class="px-5 py-3 text-sm text-gray-500">${esc(r.email || '—')}</td>
                <td class="px-5 py-3 text-sm text-gray-500">${esc(r.phone || '—')}</td>
                <td class="px-5 py-3 text-sm text-gray-600">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-royal-50 text-royal-700">${esc(r.role_name || '—')}</span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ${parseInt(r.is_active) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'}">
                        ${parseInt(r.is_active) ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <button onclick='editUser(${r.id}, ${JSON.stringify(r).replace(/'/g, "&#39;")})'
                            class="px-3 py-1.5 text-xs bg-royal-50 hover:bg-royal-100 text-royal-700 rounded-lg font-semibold transition">Edit</button>
                        ${parseInt(r.is_active)
                            ? `<button onclick="deactivateUser(${r.id}, '${esc(r.full_name)}')"
                                class="px-3 py-1.5 text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded-lg font-semibold transition">Deactivate</button>`
                            : `<button onclick="reactivateUser(${r.id})"
                                class="px-3 py-1.5 text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg font-semibold transition">Reactivate</button>`}
                    </div>
                </td>
            </tr>`
        ).join('');
    } catch (e) {
        document.getElementById('users-loading').classList.add('hidden');
        document.getElementById('users-empty').innerHTML = '<p class="font-medium text-red-500">Failed to load users.</p>';
        document.getElementById('users-empty').classList.remove('hidden');
        console.error('Users load failed:', e);
    }
}

// ── User Modal ──
function openUserModal(id = null, data = null) {
    document.getElementById('user-modal-title').textContent = id ? 'Edit User' : 'New User';
    document.getElementById('user-edit-id').value = id || '';
    document.getElementById('user-name').value = data ? (data.full_name || '') : '';
    document.getElementById('user-email').value = data ? (data.email || '') : '';
    document.getElementById('user-phone').value = data ? (data.phone || '') : '';
    document.getElementById('user-password').value = '';
    document.getElementById('user-role').value = data ? (data.role_id || '') : '';

    // Password: required for new, optional for edit
    const pwdInput = document.getElementById('user-password');
    const pwdReq = document.getElementById('pwd-req');
    const pwdHint = document.getElementById('pwd-hint');
    if (id) {
        pwdInput.removeAttribute('required');
        pwdReq.classList.add('hidden');
        pwdHint.classList.remove('hidden');
    } else {
        pwdInput.setAttribute('required', 'required');
        pwdReq.classList.remove('hidden');
        pwdHint.classList.add('hidden');
    }

    // Active checkbox: show for edit only
    document.getElementById('user-active-row').classList.toggle('hidden', !id);
    if (id && data) document.getElementById('user-active').checked = parseInt(data.is_active) === 1;

    document.getElementById('user-form-error').classList.add('hidden');
    document.getElementById('user-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('user-name').focus(), 100);
}

function closeUserModal() {
    document.getElementById('user-modal').classList.add('hidden');
    document.getElementById('user-form').reset();
}

function editUser(id, data) {
    openUserModal(id, typeof data === 'string' ? JSON.parse(data) : data);
}

// ── User form submit ──
document.getElementById('user-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errEl = document.getElementById('user-form-error');
    errEl.classList.add('hidden');
    const btn = document.getElementById('user-submit-btn');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const id = document.getElementById('user-edit-id').value;
    const payload = {
        full_name: document.getElementById('user-name').value.trim(),
        email: document.getElementById('user-email').value.trim(),
        phone: document.getElementById('user-phone').value.trim(),
        role_id: parseInt(document.getElementById('user-role').value),
    };
    const pwd = document.getElementById('user-password').value;
    if (pwd) payload.password = pwd;
    if (id) {
        payload.is_active = document.getElementById('user-active').checked ? 1 : 0;
    }

    try {
        const res = await fetch(
            id ? `${SAPI}/settings/users/${id}` : `${SAPI}/settings/users`,
            { method: id ? 'PUT' : 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) }
        );
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed');
        closeUserModal();
        loadUsers();
    } catch (err) {
        errEl.textContent = err.message;
        errEl.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save';
    }
});

// ── Deactivate / Reactivate User ──
async function deactivateUser(id, name) {
    if (!confirm(`Deactivate user "${name}"? They will no longer be able to log in.`)) return;
    try {
        const res = await fetch(`${SAPI}/settings/users/${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        loadUsers();
    } catch (e) { alert('Error: ' + e.message); }
}

async function reactivateUser(id) {
    try {
        const res = await fetch(`${SAPI}/settings/users/${id}`, {
            method: 'PUT', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ is_active: 1 })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        loadUsers();
    } catch (e) { alert('Error: ' + e.message); }
}

// Init: styles for active tab
document.querySelector('.stab[data-stab="kanisa"]').classList.add('border-royal-600','text-royal-700');

// ═══════ APPROVAL SETTINGS ═══════
let allRoles = [];
let allPermissions = [];

async function loadApprovalSettings() {
    await Promise.all([loadWorkflows(), loadRolePermissions()]);
}

// ── Workflows ──
async function loadWorkflows() {
    try {
        const [wfRes, rolesRes] = await Promise.all([
            fetch(SAPI + '/settings/approval-workflows'),
            fetch(SAPI + '/settings/roles')
        ]);
        const wfData = await wfRes.json();
        const rolesData = await rolesRes.json();
        allRoles = rolesData.data || [];
        const workflows = wfData.data || [];

        // Populate role select in modal
        const roleSel = document.getElementById('wf-role');
        roleSel.innerHTML = '<option value="">— Select Role —</option>';
        allRoles.forEach(r => { roleSel.innerHTML += '<option value="' + r.id + '">' + r.name + '</option>'; });

        const tbody = document.getElementById('wf-tbody');
        const empty = document.getElementById('wf-empty');
        if (!workflows.length) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
        empty.classList.add('hidden');

        const typeLabels = { budget: 'Budget Approval', procurement: 'Procurement Approval', finance_entry: 'Finance Entry Approval' };
        tbody.innerHTML = workflows.map(w =>
            '<tr class="hover:bg-gray-50">' +
            '<td class="px-5 py-3 text-sm font-medium text-gray-800">' + (typeLabels[w.workflow_type] || w.workflow_type) + '</td>' +
            '<td class="px-5 py-3 text-center"><span class="inline-flex items-center justify-center w-7 h-7 bg-royal-100 text-royal-700 rounded-full text-xs font-bold">' + w.level_no + '</span></td>' +
            '<td class="px-5 py-3 text-sm text-gray-700">' + (w.role_name || 'Role #' + w.role_id) + '</td>' +
            '<td class="px-5 py-3 text-center">' +
                '<button onclick="deleteWorkflow(' + w.id + ')" class="px-3 py-1.5 text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded-lg font-semibold">Delete</button></td></tr>'
        ).join('');
    } catch (e) { console.error('Workflow load failed:', e); }
}

function openWorkflowModal() { document.getElementById('wf-modal').classList.remove('hidden'); }
function closeWorkflowModal() { document.getElementById('wf-modal').classList.add('hidden'); document.getElementById('wf-form').reset(); }

document.getElementById('wf-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const payload = {
        workflow_type: document.getElementById('wf-type').value,
        level_no: parseInt(document.getElementById('wf-level').value),
        role_id: parseInt(document.getElementById('wf-role').value)
    };
    try {
        const res = await fetch(SAPI + '/settings/approval-workflows', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed');
        closeWorkflowModal();
        loadWorkflows();
    } catch (err) { alert('Error: ' + err.message); }
});

async function deleteWorkflow(id) {
    if (!confirm('Delete this workflow step?')) return;
    try {
        const res = await fetch(SAPI + '/settings/approval-workflows/' + id, { method: 'DELETE' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        loadWorkflows();
    } catch (e) { alert('Error: ' + e.message); }
}

// ── Role Permissions (redesigned) ──
const MODULE_META = {
    members:       { icon: '', color: 'bg-blue-50 text-blue-700 border-blue-200' },
    departments:   { icon: '', color: 'bg-purple-50 text-purple-700 border-purple-200' },
    events:        { icon: '', color: 'bg-orange-50 text-orange-700 border-orange-200' },
    attendance:    { icon: '', color: 'bg-green-50 text-green-700 border-green-200' },
    finance:       { icon: '', color: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    procurement:   { icon: '', color: 'bg-yellow-50 text-yellow-700 border-yellow-200' },
    assets:        { icon: '', color: 'bg-slate-50 text-slate-700 border-slate-200' },
    communication: { icon: '', color: 'bg-pink-50 text-pink-700 border-pink-200' },
    reports:       { icon: '', color: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
    settings:      { icon: '', color: 'bg-gray-50 text-gray-700 border-gray-200' },
};
const ROLE_COLORS = [
    'from-royal-600 to-royal-800',
    'from-emerald-600 to-emerald-800',
    'from-orange-500 to-orange-700',
    'from-purple-600 to-purple-800',
    'from-pink-600 to-pink-800',
    'from-slate-600 to-slate-800',
    'from-teal-600 to-teal-800',
];
let rpActiveRole = null;

async function loadRolePermissions() {
    try {
        const [rolesRes, permsRes] = await Promise.all([
            fetch(SAPI + '/settings/roles'),
            fetch(SAPI + '/settings/permissions')
        ]);
        const rolesData = await rolesRes.json();
        const permsData = await permsRes.json();
        allRoles = rolesData.data || [];
        allPermissions = permsData.data || [];

        // Build role tab buttons
        const tabsEl = document.getElementById('rp-role-tabs');
        tabsEl.innerHTML = allRoles.map((role, i) => {
            const grad = ROLE_COLORS[i % ROLE_COLORS.length];
            return `<button onclick="rpSelectRole(${role.id})" id="rp-tab-${role.id}"
                class="rp-tab px-4 py-2 rounded-xl text-sm font-semibold border border-transparent text-gray-600 hover:bg-gray-100 transition">
                ${esc(role.name)}
            </button>`;
        }).join('');

        // Select first role by default
        if (allRoles.length) rpSelectRole(allRoles[0].id);

    } catch (e) {
        document.getElementById('rp-loading').textContent = 'Failed to load permissions.';
        console.error('Role perms load failed:', e);
    }
}

function rpSelectRole(roleId) {
    rpActiveRole = roleId;
    // Update tab styles
    document.querySelectorAll('.rp-tab').forEach(btn => {
        btn.classList.remove('bg-royal-600','text-white','border-royal-600','shadow-sm');
        btn.classList.add('text-gray-600','border-transparent');
    });
    const activeTab = document.getElementById('rp-tab-' + roleId);
    if (activeTab) {
        activeTab.classList.add('bg-royal-600','text-white','border-royal-600','shadow-sm');
        activeTab.classList.remove('text-gray-600','border-transparent');
    }

    const role = allRoles.find(r => r.id == roleId);
    if (!role) return;

    const rolePerms = role.permissions || [];
    const rolePermNames = new Set(rolePerms.map(rp => rp.name));
    const isAdmin = role.name === 'Admin';

    // Group permissions by module
    const groups = {};
    allPermissions.forEach(p => {
        const mod = p.module || p.name.split('.')[0] || 'other';
        if (!groups[mod]) groups[mod] = [];
        groups[mod].push(p);
    });

    // Count totals for header
    const total = allPermissions.length;
    const granted = rolePermNames.size;

    let modulesHtml = Object.entries(groups).map(([mod, perms]) => {
        const meta = MODULE_META[mod] || { icon: '🔹', color: 'bg-gray-50 text-gray-700 border-gray-200' };
        const modGranted = perms.filter(p => rolePermNames.has(p.name)).length;
        const allChecked = modGranted === perms.length;

        const toggles = perms.map(p => {
            const on = isAdmin || rolePermNames.has(p.name);
            const action = p.name.split('.').slice(1).join('.');
            return `<div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <span class="text-sm text-gray-700 capitalize">${action}</span>
                <button type="button" onclick="rpToggle(${role.id}, ${p.id}, this, '${p.name}')" ${isAdmin ? 'disabled' : ''}
                    data-on="${on ? '1' : '0'}"
                    class="rp-toggle relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none ${on ? 'bg-royal-600' : 'bg-gray-200'} ${isAdmin ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer'}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform ${on ? 'translate-x-6' : 'translate-x-1'}"></span>
                </button>
            </div>`;
        }).join('');

        return `<div class="rounded-xl border ${meta.color} overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2.5 border-b ${meta.color}">
                <div class="flex items-center gap-2">
                    <span class="text-base">${meta.icon}</span>
                    <span class="text-xs font-bold uppercase tracking-wide">${mod}</span>
                    <span class="text-xs opacity-60">(${modGranted}/${perms.length})</span>
                </div>
                ${!isAdmin ? `<button type="button" onclick="rpToggleModule(${role.id}, ${JSON.stringify(perms.map(p=>p.id))}, ${JSON.stringify(perms.map(p=>p.name))}, this, ${allChecked ? 1 : 0})"
                    class="text-xs font-semibold underline opacity-70 hover:opacity-100 transition">
                    ${allChecked ? 'Revoke all' : 'Grant all'}
                </button>` : ''}
            </div>
            <div class="px-4 bg-white">${toggles}</div>
        </div>`;
    }).join('');

    document.getElementById('rp-panel').innerHTML = `
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-bold text-gray-900">${esc(role.name)}</h3>
                <p class="text-xs text-gray-400 mt-0.5">${isAdmin ? 'Full access — all permissions always granted' : `${granted} of ${total} permissions granted`}</p>
            </div>
            ${!isAdmin ? `<button onclick="saveRolePerms(${role.id})" id="rp-save-${role.id}"
                class="inline-flex items-center gap-2 px-5 py-2 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Save Changes
            </button>` : '<span class="text-xs text-gray-400 italic">All access — no edits needed</span>'}
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">${modulesHtml}</div>
    `;
}

function rpToggle(roleId, permId, btn, permName) {
    const isOn = btn.dataset.on === '1';
    const newOn = !isOn;
    btn.dataset.on = newOn ? '1' : '0';
    btn.classList.toggle('bg-royal-600', newOn);
    btn.classList.toggle('bg-gray-200', !newOn);
    const dot = btn.querySelector('span');
    dot.classList.toggle('translate-x-6', newOn);
    dot.classList.toggle('translate-x-1', !newOn);
    // Update counter in the module header
    rpRefreshCounts(roleId);
}

function rpToggleModule(roleId, permIds, permNames, btn, wasAllChecked) {
    const newState = wasAllChecked ? 0 : 1;
    permIds.forEach((pid, i) => {
        const toggle = document.querySelector(`.rp-toggle[onclick*="${pid},"]`);
        if (toggle && toggle.dataset.on != newState) {
            rpToggle(roleId, pid, toggle, permNames[i]);
        }
    });
    btn.textContent = newState ? 'Revoke all' : 'Grant all';
    btn.closest('[onclick]') && (btn.closest('[onclick]').dataset.all = newState);
    btn.setAttribute('onclick', btn.getAttribute('onclick').replace(wasAllChecked ? ', 1)' : ', 0)', newState ? ', 1)' : ', 0)'));
}

function rpRefreshCounts(roleId) {
    // Refresh granted count in header
    const totalChecked = document.querySelectorAll('.rp-toggle[data-on="1"]').length;
    const totalAll = allPermissions.length;
    const subEl = document.querySelector('#rp-panel p.text-xs.text-gray-400');
    if (subEl) subEl.textContent = `${totalChecked} of ${totalAll} permissions granted`;
}

async function saveRolePerms(roleId) {
    const btn = document.getElementById('rp-save-' + roleId);
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

    const checks = document.querySelectorAll('.rp-toggle[data-on="1"]');
    const permIds = Array.from(checks).map(c => {
        const m = c.getAttribute('onclick').match(/rpToggle\(\d+,\s*(\d+)/);
        return m ? parseInt(m[1]) : null;
    }).filter(Boolean);

    try {
        const res = await fetch(SAPI + '/settings/roles/' + roleId + '/permissions', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ permission_ids: permIds })
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed');
        if (btn) {
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Saved!';
            btn.classList.replace('bg-royal-600','bg-emerald-600');
            btn.classList.replace('hover:bg-royal-700','hover:bg-emerald-700');
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Save Changes';
                btn.classList.replace('bg-emerald-600','bg-royal-600');
                btn.classList.replace('hover:bg-emerald-700','hover:bg-royal-700');
            }, 2000);
        }
        // Refresh allRoles cache
        const role = allRoles.find(r => r.id == roleId);
        if (role) {
            role.permissions = allPermissions.filter(p => permIds.includes(p.id));
        }
    } catch (err) {
        if (btn) { btn.disabled = false; btn.textContent = 'Save Changes'; }
        alert('Error: ' + err.message);
    }
}
</script>

<style>
.settings-module .stab-active { border-color: #3344a5; color: #3344a5; }
/* Role permissions toggles */
.rp-toggle { flex-shrink: 0; }
.rp-toggle:focus-visible { outline: 2px solid #3344a5; outline-offset: 2px; }
</style>

