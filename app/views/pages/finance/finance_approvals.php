<!-- ═══════════ TAB 6: APPROVALS ═══════════ -->
<div id="tab-approvals" class="fin-panel hidden">
    <!-- Summary bar -->
    <div class="flex flex-wrap items-center gap-3 mb-5">
        <h2 class="text-lg font-bold text-royal-800 flex-1">Approval Queue</h2>
        <span id="appr-entry-count-badge" class="hidden px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold"></span>
        <span id="appr-budget-count-badge" class="hidden px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold"></span>
        <button onclick="loadApprovals()" class="px-3 py-1.5 rounded-xl bg-mist-100 hover:bg-mist-200 text-mist-700 text-xs font-semibold transition flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M5.635 19A9 9 0 104.582 9"/></svg>
            Refresh
        </button>
    </div>

    <!-- Finance Entries Pending -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
            <h3 class="font-semibold text-gray-800 text-sm">Finance Entries — Pending Approval</h3>
        </div>
        <div id="appr-entries-loading" class="px-5 py-6 text-center text-sm text-gray-400">Loading…</div>
        <div id="appr-entries-wrap" class="hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Entry No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Source</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Recorded By</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appr-entries-tbody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
        <div id="appr-entries-empty" class="hidden px-5 py-10 text-center">
            <div class="w-10 h-10 mx-auto rounded-full bg-emerald-50 flex items-center justify-center mb-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-500">All caught up — no entries pending approval</p>
        </div>
        <div id="appr-entries-error" class="hidden px-5 py-6 text-center text-sm text-red-500"></div>
    </div>

    <!-- Department Budgets Pending -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-purple-400"></div>
            <h3 class="font-semibold text-gray-800 text-sm">Department Budgets — Pending Approval</h3>
        </div>
        <div id="appr-budgets-loading" class="px-5 py-6 text-center text-sm text-gray-400">Loading…</div>
        <div id="appr-budgets-wrap" class="hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Prepared By</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appr-budgets-tbody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
        <div id="appr-budgets-empty" class="hidden px-5 py-10 text-center">
            <div class="w-10 h-10 mx-auto rounded-full bg-emerald-50 flex items-center justify-center mb-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-500">No department budgets pending approval</p>
        </div>
        <div id="appr-budgets-error" class="hidden px-5 py-6 text-center text-sm text-red-500"></div>
    </div>
</div>
