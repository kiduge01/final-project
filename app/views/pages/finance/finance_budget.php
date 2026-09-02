<!-- ═══════════ TAB 5: BUDGETS ═══════════ -->
<div id="tab-budgets" class="fin-panel hidden">

    <!-- Budget Sub-tabs -->
    <div class="flex gap-2 mb-5 border-b border-gray-200 pb-2">
        <button data-btab="requests" class="btab btab-active px-4 py-2 text-sm font-semibold rounded-t-lg border-b-2 border-royal-600 text-royal-700 bg-white">Budget Requests</button>
        <button data-btab="active" class="btab px-4 py-2 text-sm font-semibold rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-royal-600">Active Budgets</button>
        <button data-btab="closed" class="btab px-4 py-2 text-sm font-semibold rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-royal-600">Closed Budgets</button>
        <button data-btab="trail" class="btab px-4 py-2 text-sm font-semibold rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-royal-600">Trail Report</button>
    </div>

    <!-- ───── SUB 1: Budget Requests (submitted/pending) ───── -->
    <div id="btab-requests" class="btab-panel">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-semibold text-gray-800 text-base">Budget Requests</h2>
                <button onclick="openModal('budget-modal')" class="px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white text-sm font-semibold rounded-xl">+ Request Budget</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Department / Event</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Requested Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Requested By</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="budget-requests-tbody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
            <div id="budget-requests-empty" class="hidden px-5 py-10 text-center text-gray-400">
                <p class="font-medium">No pending budget requests</p>
            </div>
        </div>
    </div>

    <!-- ───── SUB 2: Active Budgets (approved / expenses_added) ───── -->
    <div id="btab-active" class="btab-panel hidden">
        <!-- Active budget list view -->
        <div id="active-budgets-list">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-800 text-base">Active Budgets (Approved)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Department / Event</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Month</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Approved Budget</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Reserved</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total Used</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Available</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Usage</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="active-budgets-tbody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                <div id="active-budgets-empty" class="hidden px-5 py-10 text-center text-gray-400">
                    <p class="font-medium">No active budgets</p>
                </div>
            </div>
        </div>

        <!-- Active budget DETAIL view (expense tracking) -->
        <div id="active-budget-detail" class="hidden">
            <button onclick="closeBudgetDetail()" class="mb-4 flex items-center gap-1 text-sm text-royal-600 hover:text-royal-800 font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back to Active Budgets
            </button>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header with budget info -->
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 id="detail-budget-title" class="font-bold text-gray-900 text-lg"></h2>
                            <p id="detail-budget-meta" class="text-xs text-gray-500 mt-0.5"></p>
                        </div>
                        <button id="detail-close-btn" onclick="openCloseBudgetFromDetail()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl">Close Budget</button>
                    </div>
                    <!-- KPI Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                        <div class="p-3 bg-blue-50 rounded-xl text-center">
                            <p id="detail-approved" class="text-lg font-bold text-blue-700">TZS 0</p>
                            <p class="text-xs text-blue-500">Approved Budget</p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-xl text-center">
                            <p id="detail-reserved" class="text-lg font-bold text-amber-700">TZS 0</p>
                            <p class="text-xs text-amber-500">Reserved (Procurement)</p>
                        </div>
                        <div class="p-3 bg-red-50 rounded-xl text-center">
                            <p id="detail-used" class="text-lg font-bold text-red-700">TZS 0</p>
                            <p class="text-xs text-red-500">Total Spent</p>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-xl text-center">
                            <p id="detail-remaining" class="text-lg font-bold text-emerald-700">TZS 0</p>
                            <p class="text-xs text-emerald-500">Available</p>
                        </div>
                    </div>
                    <!-- Progress bar -->
                    <div class="mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="detail-progress-bar" class="bg-blue-500 h-2 rounded-full transition-all duration-500" style="width:0%"></div>
                        </div>
                        <p id="detail-progress-label" class="text-xs text-gray-500 mt-1 text-right">0% used</p>
                    </div>
                </div>
                <!-- Expense Items Table -->
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-700 text-sm">Expense Items</h3>
                        <button id="detail-add-expense-btn" onclick="openModal('add-expense-modal')" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg">+ Add Expense</button>
                    </div>
                    <div class="overflow-x-auto border border-gray-100 rounded-xl">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Item Name</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Amount</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Notes</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">By</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600">Action</th>
                                </tr>
                            </thead>
                            <tbody id="detail-expenses-tbody" class="divide-y divide-gray-50"></tbody>
                        </table>
                    </div>
                    <div id="detail-expenses-empty" class="hidden py-8 text-center text-gray-400 text-sm">
                        No expenses recorded yet. Click "+ Add Expense" to start.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ───── SUB 3: Closed Budgets ───── -->
    <div id="btab-closed" class="btab-panel hidden">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800 text-base">Closed Budgets</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Department / Event</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Approved</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total Used</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Remaining</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Closed Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody id="closed-budgets-tbody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
            <div id="closed-budgets-empty" class="hidden px-5 py-10 text-center text-gray-400">
                <p class="font-medium">No closed budgets</p>
            </div>
        </div>
    </div>

    <!-- ───── SUB 4: Trail Report ───── -->
    <div id="btab-trail" class="btab-panel hidden">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 text-base">Budget Trail Report</h2>
                <button onclick="printBudgetTrail()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs rounded-lg flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>
                    Print
                </button>
            </div>
            <!-- Summary KPIs -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 px-5 py-4">
                <div class="p-3 bg-blue-50 rounded-xl text-center">
                    <p id="trail-total-budgets" class="text-lg font-bold text-blue-700">0</p>
                    <p class="text-xs text-blue-500">Total Budgets</p>
                </div>
                <div class="p-3 bg-green-50 rounded-xl text-center">
                    <p id="trail-total-approved" class="text-lg font-bold text-green-700">TZS 0</p>
                    <p class="text-xs text-green-500">Total Approved</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl text-center">
                    <p id="trail-total-reserved" class="text-lg font-bold text-amber-700">TZS 0</p>
                    <p class="text-xs text-amber-500">Reserved</p>
                </div>
                <div class="p-3 bg-red-50 rounded-xl text-center">
                    <p id="trail-total-used" class="text-lg font-bold text-red-700">TZS 0</p>
                    <p class="text-xs text-red-500">Total Expenses</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl text-center">
                    <p id="trail-total-remaining" class="text-lg font-bold text-emerald-700">TZS 0</p>
                    <p class="text-xs text-emerald-500">Available</p>
                </div>
            </div>
            <div id="trail-report-content" class="overflow-x-auto px-5 pb-5">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Department / Event</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Month</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Requested</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Approved</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Reserved</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Total Expenses</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Available</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody id="trail-tbody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
