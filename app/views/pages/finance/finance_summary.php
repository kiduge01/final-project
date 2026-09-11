<!-- ═══════════ TAB 1: SUMMARY (Dashboard) ═══════════ -->
<div id="tab-summary" class="fin-panel">

    <!-- ── KPI Cards ─────────────────────────────────────── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <!-- Income -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-5 text-white shadow-md">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>
                <span class="text-[10px] font-semibold bg-white/20 rounded-full px-2 py-0.5" id="kpi-month-label">Month</span>
            </div>
            <p class="text-xl font-extrabold leading-tight" id="kpi-income">TZS 0</p>
            <p class="text-xs text-emerald-100 mt-1 font-medium">Total Income</p>
            <div class="mt-3 h-1 rounded-full bg-white/20"><div id="kpi-income-bar" class="h-1 rounded-full bg-white/70 transition-all duration-700" style="width:0%"></div></div>
        </div>

        <!-- Expenses -->
        <div class="bg-gradient-to-br from-red-500 to-rose-700 rounded-2xl p-5 text-white shadow-md">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
                </div>
                <span class="text-[10px] font-semibold bg-white/20 rounded-full px-2 py-0.5">Month</span>
            </div>
            <p class="text-xl font-extrabold leading-tight" id="kpi-expense">TZS 0</p>
            <p class="text-xs text-red-100 mt-1 font-medium">Total Expenses</p>
            <div class="mt-3 h-1 rounded-full bg-white/20"><div id="kpi-expense-bar" class="h-1 rounded-full bg-white/70 transition-all duration-700" style="width:0%"></div></div>
        </div>

        <!-- Balance -->
        <div class="bg-gradient-to-br from-royal-700 to-royal-900 rounded-2xl p-5 text-white shadow-md">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <span id="kpi-balance-label" class="text-[10px] font-semibold bg-white/20 rounded-full px-2 py-0.5">Balance</span>
            </div>
            <p class="text-xl font-extrabold leading-tight" id="kpi-balance">TZS 0</p>
            <p class="text-xs text-blue-200 mt-1 font-medium">Net Balance</p>
            <p class="text-[10px] text-blue-200 mt-2 font-medium" id="kpi-alltime">All-time: TZS 0</p>
        </div>

        <!-- Pending Actions -->
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-md">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <button onclick="switchToApprovals()" class="text-[10px] font-semibold bg-white/20 hover:bg-white/30 rounded-full px-2 py-0.5 transition">Review</button>
            </div>
            <p class="text-xl font-extrabold leading-tight" id="kpi-pending-total">0</p>
            <p class="text-xs text-amber-100 mt-1 font-medium">Needs Approval</p>
            <div class="mt-2 flex gap-2 text-[10px]">
                <span class="bg-white/20 rounded-full px-2 py-0.5">Entries: <b id="pending-entries-count">0</b></span>
                <span class="bg-white/20 rounded-full px-2 py-0.5">Budgets: <b id="pending-budgets-count">0</b></span>
            </div>
        </div>
    </div>

    <!-- ── Health Bar ─────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">Monthly Financial Health</span>
            <span class="text-xs text-gray-400" id="health-label">—</span>
        </div>
        <div class="w-full h-3 rounded-full bg-gray-100 overflow-hidden">
            <div id="health-bar" class="h-3 rounded-full transition-all duration-1000 bg-emerald-500" style="width:0%"></div>
        </div>
        <div class="flex justify-between text-[10px] text-gray-400 mt-1.5">
            <span>Expense-heavy</span><span>Balanced</span><span>Income-surplus</span>
        </div>
    </div>

    <!-- ── Church Account Balances ───────────────────────── -->
    <div id="accounts-section" class="mb-6 hidden">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-royal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                Church Accounts
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">Total Balance:</span>
                <span id="total-church-balance" class="text-sm font-extrabold text-royal-700">TZS 0</span>
            </div>
        </div>
        <div id="accounts-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
    </div>

    <!-- ── Charts ─────────────────────────────────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-6">
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">6-Month Trend</h3>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>Income</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>Expenses</span>
                </div>
            </div>
            <canvas id="trend-chart" height="190"></canvas>
        </div>
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4">Category Breakdown</h3>
            <canvas id="category-chart" height="190"></canvas>
            <div id="category-legend" class="mt-3 space-y-1.5 max-h-28 overflow-y-auto"></div>
        </div>
    </div>

    <!-- ── Pledges + Recent Activity ─────────────────────────────────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Pledge Status -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4">Pledge Status</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50 border border-amber-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                        <span class="text-sm font-medium text-gray-700">Outstanding</span>
                    </div>
                    <span class="text-sm font-bold text-amber-700" id="kpi-pledges">TZS 0</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-red-50 border border-red-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-2 h-2 rounded-full bg-red-400"></div>
                        <span class="text-sm font-medium text-gray-700">Entry Approvals</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700" id="pending-entries-count2">0</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-purple-50 border border-purple-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                        <span class="text-sm font-medium text-gray-700">Budget Approvals</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700" id="pending-budgets-count2">0</span>
                </div>
            </div>
            <button onclick="switchToApprovals()" class="mt-4 w-full py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition active:scale-95">
                Go to Approvals →
            </button>
        </div>

        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">Recent Activity</h3>
                <span class="text-xs text-gray-400">Last 10 entries</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="pb-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Date</th>
                            <th class="pb-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Category</th>
                            <th class="pb-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Type</th>
                            <th class="pb-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wide text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="recent-tbody" class="divide-y divide-gray-50"></tbody>
                </table>
            </div>
            <div id="recent-empty" class="hidden py-8 text-center text-sm text-gray-400">No recent entries</div>
        </div>
    </div>
</div>
