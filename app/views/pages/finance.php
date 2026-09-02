<?php $B = $baseUrl ?? ''; ?>

<div class="finance-module">

<!-- Finance Module Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-heading font-semibold text-royal-900">Finance Center</h1>
        <p class="text-mist-600 text-sm mt-0.5">Overview, income, expenses, pledges, budgets, approvals, and reports</p>
    </div>
    <div class="flex items-center gap-3">
        <select id="fin-month-select" class="rounded-xl border border-mist-200 px-3 py-2 text-sm text-mist-700 focus:ring-2 focus:ring-royal-300">
        </select>
        <button onclick="openModal('entry-modal')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white font-semibold rounded-xl shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Entry
        </button>
        <button onclick="exportFinanceCSV()" title="Export current report as CSV"
            class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m9-9-9-9-9 9"/></svg>
            Export CSV
        </button>
        <button onclick="openModal('campaign-modal')" title="Register new campaign"
            style="color: #ffffff"
            class="inline-flex items-center gap-2 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7l3-7z"/></svg>
            Register Campaign
        </button>
        <button onclick="openModal('pledge-import-modal')" title="Import pledges from CSV"
            class="inline-flex items-center gap-2 px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl shadow-sm transition-colors text-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 10l5 5 5-5"/></svg>
            Import Pledges
        </button>
    </div>
</div>

<!-- ═══════════ TABS ═══════════ -->
<div class="mb-6 border-b border-mist-200 overflow-x-auto">
    
    <nav class="flex gap-1 -mb-px whitespace-nowrap" id="fin-tabs">
        <button data-tab="summary" class="fin-tab fin-tab-active px-4 py-2.5 text-sm font-semibold border-b-2 " style="color: #fafafa;">Overview</button>
        <button data-tab="income" class="fin-tab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors" style="color: #fafafa;">Income</button>
        <button data-tab="expenses" class="fin-tab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors" style="color: #fafafa;">Expenses</button>
        <button data-tab="pledges" class="fin-tab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors" style="color: #fafafa;">Pledges</button>
        <button data-tab="budgets" class="fin-tab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors" style="color: #fafafa;">Budgets</button>
        <button data-tab="approvals" class="fin-tab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors" style="color: #fafafa;">Approvals <span id="approval-badge" class="hidden ml-1 px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full"></span></button>
        <button data-tab="reports" class="fin-tab px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-mist-500 hover:text-royal-700 transition-colors" style="color: #fafafa;">Reports</button>
    </nav>

</div>

<?php include __DIR__ . '/finance/finance_summary.php'; ?>
<?php include __DIR__ . '/finance/finance_income.php'; ?>
<?php include __DIR__ . '/finance/finance_expenses.php'; ?>
<?php include __DIR__ . '/finance/finance_pledges.php'; ?>
<?php include __DIR__ . '/finance/finance_budget.php'; ?>
<?php include __DIR__ . '/finance/finance_approvals.php'; ?>
<?php include __DIR__ . '/finance/finance_reports.php'; ?>

<!-- ═══════════ MODALS ═══════════ -->

<!-- Add Finance Entry Modal -->
<div id="entry-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('entry-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">New Finance Entry</h3>
                <button onclick="closeModal('entry-modal')" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="entry-form" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="entry_date" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" id="entry-category" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (TZS) <span class="text-red-500">*</span></label>
                        <input name="amount" type="number" step="0.01" min="0" placeholder="0.00" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                        <select name="payment_method" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="">Select...</option>
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Church Account</label>
                        <select name="church_account_id" id="entry-account" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="">Select account...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                        <select name="member_id" id="entry-member" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="">Optional...</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <input name="description" placeholder="Brief description..." required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <p class="text-xs text-gray-400">Entry number will be generated automatically.</p>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('entry-modal')" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" id="entry-submit-btn" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Pledge Modal -->
<div id="pledge-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('pledge-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">New Pledge</h3>
                <button onclick="closeModal('pledge-modal')" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="pledge-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                    <select name="member_id" id="pledge-member" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">Select member...</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pledge Amount (TZS)</label>
                        <input name="total_amount" type="number" step="0.01" min="0" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Campaign</label>
                        <select name="campaign" id="pledge-campaign" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="">Select campaign...</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pledge Date</label>
                        <input type="date" name="pledge_date" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input name="description" placeholder="Description..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('pledge-modal')" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-sm">Save Pledge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Budget Request Modal -->
<div id="budget-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('budget-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Request Budget</h3>
                <button onclick="closeModal('budget-modal')" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="budget-form" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
                        <select name="department" id="budget-dept-select" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="">Loading…</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event <span class="text-gray-400 font-normal">(optional)</span></label>
                        <select name="event_id" id="budget-event-select" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="">— No event —</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Month <span class="text-red-500">*</span></label>
                        <input type="month" name="fiscal_month" required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Requested Amount (TZS) <span class="text-red-500">*</span></label>
                        <input name="planned_amount" type="number" step="1" min="0" required placeholder="0"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <input name="description" placeholder="Brief description of the budget purpose..." required class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Category <span class="text-gray-400 font-normal">(optional)</span></label>
                    <select name="category_id" id="budget-category" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">— No category link —</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('budget-modal')" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-royal-600 hover:bg-royal-700 rounded-xl shadow-sm">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Expense Item Modal -->
<div id="add-expense-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('add-expense-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Add Expense</h3>
                    <p id="expense-budget-label" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <button onclick="closeModal('add-expense-modal')" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="expense-remaining-info" class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-700"></div>
            <form id="add-expense-form" class="space-y-4">
                <input type="hidden" id="expense-budget-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
                    <input id="expense-item-name" required placeholder="e.g. Sound equipment rental" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (TZS) <span class="text-red-500">*</span></label>
                        <input id="expense-amount" type="number" step="1" min="1" required placeholder="0" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input id="expense-date" type="date" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <input id="expense-notes" placeholder="Optional notes..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('add-expense-modal')" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Budget Confirmation Modal -->
<div id="close-budget-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('close-budget-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Close Budget</h3>
                    <p id="close-budget-label" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <button onclick="closeModal('close-budget-modal')" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="close-budget-summary" class="mb-5 space-y-2"></div>
            <form id="close-budget-form" class="space-y-4">
                <input type="hidden" id="close-budget-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Closing Notes</label>
                    <input id="close-budget-notes" placeholder="e.g. Event completed successfully..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-400">
                </div>
                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-800">
                    <strong>What happens:</strong> Total expenses will be posted to Finance Entries automatically. The budget will be locked (read-only).
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('close-budget-modal')" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm">Close &amp; Post Expenses</button>
                </div>
            </form>
        </div>
    </div>
</div>
            </form>
        </div>
    </div>
</div>

<!-- Event Budget Review Modal -->
<div id="event-budget-review-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('event-budget-review-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl p-6 z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Event Budget Review</h3>
                <button onclick="closeModal('event-budget-review-modal')" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="budget-review-summary" class="mb-4 text-sm text-gray-700"></div>
            <div class="overflow-x-auto border border-gray-100 rounded-xl">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Item</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Planned</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Actual</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Review Note</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody id="budget-review-tbody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<!-- Campaign Registration Modal -->
<div id="campaign-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('campaign-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Register Campaign</h3>
                <button onclick="closeModal('campaign-modal')" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="campaign-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Title <span class="text-red-500">*</span></label>
                    <input name="title" required placeholder="e.g. Building Fund" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <p class="text-xs text-gray-500">Set a time limit for the campaign using the start and end dates above.</p>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('campaign-modal')" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm">Save Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pledge Import Modal -->
<div id="pledge-import-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('pledge-import-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Import Pledges (CSV)</h3>
                <button onclick="closeModal('pledge-import-modal')" class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="pledge-import-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select CSV file</label>
                    <input type="file" name="file" id="pledge-file-input" accept=".csv" required class="w-full">
                    <p class="text-xs text-gray-400 mt-2">CSV columns: member_code OR first_name,last_name, amount, pledge_date (YYYY-MM-DD), due_date, description, campaign</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('pledge-import-modal')" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const API = BASE_URL + '/api/v1';
const CURRENT_USER_ROLE_ID = <?= (int) ($_SESSION['user']['role_id'] ?? 0) ?>;
const CURRENT_USER_ROLE = '<?= strtolower(htmlspecialchars($_SESSION['user']['role'] ?? '', ENT_QUOTES)) ?>';
let categories = [];
let trendChart = null, categoryChart = null;
let activeBudgetReviewEventId = null;
let approvalWorkflows = {}; // workflow_type -> [{level_no, role_id, role_name}]

function fmt(n) { return 'TZS ' + Number(n||0).toLocaleString('en-US', {minimumFractionDigits: 0}); }
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// ──── TOAST SYSTEM ────
function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    const bgClass = type === 'error' ? 'bg-red-600' : type === 'info' ? 'bg-royal-600' : 'bg-emerald-600';
    
    toast.className = `${bgClass} text-white px-4 py-3 rounded-xl shadow-lg text-sm font-medium mb-2`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => toast.remove(), duration);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed top-6 right-6 z-50 flex flex-col gap-2';
    document.body.appendChild(container);
    return container;
}

// ──── CONFIRMATION DIALOG ────
function showConfirmDialog(title, message, actionLabel, onConfirm, onCancel = null, isDestructive = false) {
    let dialog = document.getElementById('confirm-dialog-fin');
    if (!dialog) {
        dialog = document.createElement('div');
        dialog.id = 'confirm-dialog-fin';
        dialog.className = 'hidden fixed inset-0 z-50 overflow-y-auto';
        dialog.innerHTML = `
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50" onclick="closeConfirmDialog()"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 id="confirm-title-fin" class="text-lg font-semibold text-royal-900 mb-2"></h3>
                    <p id="confirm-message-fin" class="text-mist-600 text-sm mb-6 whitespace-pre-line"></p>
                    <div class="flex gap-3 justify-end">
                        <button onclick="closeConfirmDialog()" class="px-4 py-2.5 rounded-xl bg-mist-100 text-mist-700 hover:bg-mist-200 text-sm font-semibold transition">Cancel</button>
                        <button id="confirm-btn-fin" onclick="executeConfirmDialog()" class="px-4 py-2.5 rounded-xl text-white text-sm font-semibold transition flex items-center gap-2">
                            <span id="confirm-btn-text-fin">Confirm</span>
                            <span id="confirm-spinner-fin" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(dialog);
    }
    
    document.getElementById('confirm-title-fin').textContent = title;
    document.getElementById('confirm-message-fin').textContent = message;
    const btn = document.getElementById('confirm-btn-fin');
    btn.className = isDestructive 
        ? 'px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition flex items-center gap-2'
        : 'px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition flex items-center gap-2';
    document.getElementById('confirm-btn-text-fin').textContent = actionLabel;
    
    dialog.classList.remove('hidden');
    
    window.confirmDialogFnCallbacks = { onConfirm, onCancel };
}

function closeConfirmDialog() {
    const dialog = document.getElementById('confirm-dialog-fin');
    if (dialog) dialog.classList.add('hidden');
    const btn = document.getElementById('confirm-btn-fin');
    btn.disabled = false;
    document.getElementById('confirm-spinner-fin').classList.add('hidden');
    document.getElementById('confirm-btn-text-fin').classList.remove('hidden');
    if (window.confirmDialogFnCallbacks?.onCancel) window.confirmDialogFnCallbacks.onCancel();
}

async function executeConfirmDialog() {
    const btn = document.getElementById('confirm-btn-fin');
    btn.disabled = true;
    document.getElementById('confirm-spinner-fin').classList.remove('hidden');
    document.getElementById('confirm-btn-text-fin').classList.add('hidden');
    
    if (window.confirmDialogFnCallbacks?.onConfirm) {
        try {
            await window.confirmDialogFnCallbacks.onConfirm();
        } catch (e) {
            console.error('Dialog action failed:', e);
        }
    }
    
    closeConfirmDialog();
}

// ─── Tab switching ───
document.querySelectorAll('.fin-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.fin-tab').forEach(t => { t.classList.remove('fin-tab-active','border-royal-600','text-royal-700'); t.classList.add('border-transparent','text-mist-500'); });
        btn.classList.add('fin-tab-active','border-royal-600','text-royal-700');
        btn.classList.remove('border-transparent','text-mist-500');
        document.querySelectorAll('.fin-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('tab-' + btn.dataset.tab).classList.remove('hidden');

        const tab = btn.dataset.tab;
        if (tab === 'summary') loadOverview();
        else if (tab === 'income') loadIncome();
        else if (tab === 'expenses') loadExpenses();
        else if (tab === 'pledges') { loadPledges(); loadPledgeStats(); }
        else if (tab === 'budgets') loadBudgets();
        else if (tab === 'approvals') loadApprovals();
    });
});

// ─── Month selector ───
function buildMonthSelector() {
    const sel = document.getElementById('fin-month-select');
    const now = new Date();
    for (let i = 0; i < 12; i++) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        // Use local date parts — toISOString() converts to UTC which shifts the month in UTC+3 timezones
        const val = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        const label = d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        sel.innerHTML += '<option value="' + val + '"' + (i === 0 ? ' selected' : '') + '>' + label + '</option>';
    }
    sel.addEventListener('change', () => loadOverview());
}

// ─── Load Categories & Members ───
function canUserApproveType(workflowType) {
    const wf = approvalWorkflows[workflowType] || [];
    if (!wf.length) return { canApprove: true, levelInfo: '' };
    const isAdmin = CURRENT_USER_ROLE === 'admin';
    if (isAdmin) return { canApprove: true, levelInfo: '' };
    const matchedLevel = wf.find(l => l.role_id === CURRENT_USER_ROLE_ID);
    return {
        canApprove: !!matchedLevel,
        levelInfo: wf.length > 1 ? wf.map(l => 'L' + l.level_no + ': ' + l.role_name).join(' \u2192 ') : ''
    };
}

async function loadMeta() {
    try {
        const [catRes, memRes, deptRes, evtRes, acctRes, wfRes, campRes] = await Promise.all([
            fetch(API + '/finance/categories'),
            fetch(API + '/members?status=active'),
            fetch(API + '/departments?active=1'),
            fetch(API + '/events'),
            fetch(API + '/finance/accounts'),
            fetch(API + '/settings/approval-workflows'),
            fetch(API + '/campaigns')
        ]);
        const catData  = await catRes.json();
        const memData  = await memRes.json();
        const deptData = await deptRes.json();
        const evtData  = await evtRes.json();
        const acctData = await acctRes.json();
        const wfData   = await wfRes.json();
        const campData = await campRes.json();
        categories = catData.data || [];

        // Parse workflow config
        if (wfData.success && wfData.data) {
            wfData.data.forEach(w => {
                if (!approvalWorkflows[w.workflow_type]) approvalWorkflows[w.workflow_type] = [];
                approvalWorkflows[w.workflow_type].push({ level_no: +w.level_no, role_id: +w.role_id, role_name: w.role_name });
            });
        }

        const catSel = document.getElementById('entry-category');
        catSel.innerHTML = '<option value="">Select category...</option>';
        categories.forEach(c => {
            catSel.innerHTML += '<option value="' + c.id + '">' + c.name + ' (' + (c.category_type === 'income' ? 'Income' : 'Expense') + ')</option>';
        });

        const incomeCatFilter = document.getElementById('income-cat-filter');
        const expensesCatFilter = document.getElementById('expenses-cat-filter');
        categories.forEach(c => {
            if (c.category_type === 'income') incomeCatFilter.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>';
            else expensesCatFilter.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>';
        });

        // Populate budget category selector with expense categories only
        const budgetCat = document.getElementById('budget-category');
        if (budgetCat) {
            budgetCat.innerHTML = '<option value="">— No category link —</option>';
            categories.filter(c => c.category_type === 'expense').forEach(c => {
                budgetCat.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>';
            });
        }

        // Populate department dropdown in budget modal
        const deptSel = document.getElementById('budget-dept-select');
        if (deptSel) {
            const depts = deptData.data || [];
            if (depts.length) {
                deptSel.innerHTML = '<option value="">Select department...</option>';
                depts.forEach(d => {
                    deptSel.innerHTML += '<option value="' + d.name + '">' + d.name + '</option>';
                });
            } else {
                deptSel.innerHTML = '<option value="">No departments — add in Settings</option>';
            }
        }

        // Populate events dropdown in budget modal
        const evtSel = document.getElementById('budget-event-select');
        if (evtSel) {
            const events = evtData.data || [];
            evtSel.innerHTML = '<option value="">— No event link —</option>';
            events.forEach(ev => {
                evtSel.innerHTML += '<option value="' + ev.id + '">' + ev.title + (ev.event_date ? ' (' + ev.event_date + ')' : '') + '</option>';
            });
        }

        const members = memData.data || [];
        ['entry-member', 'pledge-member'].forEach(selId => {
            const s = document.getElementById(selId);
            s.innerHTML = '<option value="">Select member...</option>';
            members.forEach(m => {
                s.innerHTML += '<option value="' + m.id + '">' + m.first_name + ' ' + m.last_name + ' (' + m.member_code + ')</option>';
            });
        });

        // Populate church accounts dropdown in entry form
        const acctSel = document.getElementById('entry-account');
        if (acctSel) {
            const accounts = acctData.data || [];
            acctSel.innerHTML = '<option value="">Select account...</option>';
            const icons = { cash: '💵', bank: '🏦', mobile_money: '📱' };
            accounts.forEach(a => {
                acctSel.innerHTML += '<option value="' + a.id + '">' + (icons[a.account_type] || '') + ' ' + a.account_name + (a.bank_name ? ' (' + a.bank_name + ')' : '') + '</option>';
            });
        }

        // Populate campaign dropdown for pledges
        const pledgeCampSel = document.getElementById('pledge-campaign');
        if (pledgeCampSel) {
            const campaigns = (campData && campData.data) ? campData.data : [];
            pledgeCampSel.innerHTML = '<option value="">Select campaign...</option>';
            campaigns.forEach(c => {
                const label = c.title + (c.start_date ? ' (' + c.start_date + (c.end_date ? ' - ' + c.end_date : '') + ')' : '');
                pledgeCampSel.innerHTML += '<option value="' + (c.title || '') + '">' + label + '</option>';
            });
        }
    } catch (e) { console.error('Meta load failed:', e); }
}

// ═══════════ TAB 1: SUMMARY ═══════════
async function loadOverview() {
    const month = document.getElementById('fin-month-select').value;
    try {
        const res = await fetch(API + '/finance/overview?month=' + month);
        const d = (await res.json()).data;
        if (!d) return;

        // ── Month label ──
        const [yr, mo] = month.split('-');
        const monthName = new Date(yr, mo - 1, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        document.querySelectorAll('#kpi-month-label').forEach(el => el.textContent = monthName);

        // ── KPI values ──
        document.getElementById('kpi-income').textContent   = fmt(d.month_income);
        document.getElementById('kpi-expense').textContent  = fmt(d.month_expense);
        document.getElementById('kpi-balance').textContent  = fmt(d.month_balance);
        document.getElementById('kpi-alltime').textContent  = 'All-time: ' + fmt(d.all_time_balance);

        // Balance label colour hint
        const balLabel = document.getElementById('kpi-balance-label');
        balLabel.textContent = d.month_balance >= 0 ? '▲ Surplus' : '▼ Deficit';

        // Pending counts (two sets: KPI card + Pledge panel)
        const ea = d.pending_approvals || 0;
        const ba = d.pending_budgets   || 0;
        ['pending-entries-count','pending-entries-count2'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = ea;
        });
        ['pending-budgets-count','pending-budgets-count2'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = ba;
        });
        const totalPending = ea + ba;
        document.getElementById('kpi-pending-total').textContent = totalPending;
        document.getElementById('kpi-pledges').textContent = fmt(d.pending_pledges);

        // Approval tab badge
        const badge = document.getElementById('approval-badge');
        if (totalPending > 0) { badge.textContent = totalPending; badge.classList.remove('hidden'); }
        else badge.classList.add('hidden');

        // ── Progress bars on KPI cards ──
        const total = d.month_income + d.month_expense;
        if (total > 0) {
            document.getElementById('kpi-income-bar').style.width  = Math.min(100, (d.month_income  / total * 100)).toFixed(1) + '%';
            document.getElementById('kpi-expense-bar').style.width = Math.min(100, (d.month_expense / total * 100)).toFixed(1) + '%';
        }

        // ── Health bar ──
        const healthBar   = document.getElementById('health-bar');
        const healthLabel = document.getElementById('health-label');
        if (total > 0) {
            const incomePct = d.month_income / total * 100;
            healthBar.style.width = incomePct.toFixed(1) + '%';
            if (incomePct >= 60) {
                healthBar.className = 'h-3 rounded-full transition-all duration-1000 bg-emerald-500';
                healthLabel.textContent = 'Healthy — income exceeds expenses';
            } else if (incomePct >= 45) {
                healthBar.className = 'h-3 rounded-full transition-all duration-1000 bg-amber-400';
                healthLabel.textContent = 'Caution — nearly balanced';
            } else {
                healthBar.className = 'h-3 rounded-full transition-all duration-1000 bg-red-500';
                healthLabel.textContent = 'Alert — expenses exceed income';
            }
        } else {
            healthBar.style.width = '0%';
            healthLabel.textContent = 'No data for this month';
        }

        // ── Recent entries ──
        const tbody  = document.getElementById('recent-tbody');
        const empty  = document.getElementById('recent-empty');
        const entries = d.recent_entries || [];
        if (entries.length === 0) {
            tbody.innerHTML = ''; empty.classList.remove('hidden');
        } else {
            empty.classList.add('hidden');
            tbody.innerHTML = entries.slice(0, 10).map(r => {
                const isIncome = r.category_type === 'income';
                return '<tr class="hover:bg-gray-50 transition">' +
                    '<td class="py-2.5 text-xs text-gray-500 whitespace-nowrap">' + (r.entry_date || '-') + '</td>' +
                    '<td class="py-2.5 text-xs font-semibold text-gray-800 max-w-[140px] truncate">' + (r.category_name || '-') + '</td>' +
                    '<td class="py-2.5"><span class="px-2 py-0.5 rounded-md text-[10px] font-bold ' + (isIncome ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700') + '">' + (isIncome ? 'Income' : 'Expense') + '</span></td>' +
                    '<td class="py-2.5 text-right text-xs font-bold ' + (isIncome ? 'text-emerald-600' : 'text-red-600') + '">' + fmt(r.amount) + '</td></tr>';
            }).join('');
        }

        renderTrendChart(d.trend);
        renderCategoryChart(d.category_breakdown);

        // ── Church Account Balances ──
        const acctSection = document.getElementById('accounts-section');
        const acctGrid    = document.getElementById('accounts-grid');
        const acctTotal   = document.getElementById('total-church-balance');
        const accounts    = d.accounts || [];
        if (accounts.length > 0) {
            acctSection.classList.remove('hidden');
            let totalBal = 0;
            const typeIcons = { cash: '💵', bank: '🏦', mobile_money: '📱' };
            const typeColors = {
                cash: 'from-emerald-50 to-emerald-100 border-emerald-200',
                bank: 'from-blue-50 to-blue-100 border-blue-200',
                mobile_money: 'from-purple-50 to-purple-100 border-purple-200'
            };
            acctGrid.innerHTML = accounts.map(a => {
                const bal = parseFloat(a.balance) || 0;
                totalBal += bal;
                const colors = typeColors[a.account_type] || 'from-gray-50 to-gray-100 border-gray-200';
                const icon = typeIcons[a.account_type] || '💰';
                return '<div class="bg-gradient-to-br ' + colors + ' border rounded-2xl p-4">' +
                    '<div class="flex items-center justify-between mb-2">' +
                        '<span class="text-lg">' + icon + '</span>' +
                        '<span class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">' + (a.account_type || '').replace('_', ' ') + '</span>' +
                    '</div>' +
                    '<p class="text-sm font-semibold text-gray-800 truncate">' + a.account_name + '</p>' +
                    (a.bank_name ? '<p class="text-xs text-gray-500">' + a.bank_name + '</p>' : '') +
                    '<p class="text-lg font-extrabold mt-2 ' + (bal >= 0 ? 'text-emerald-700' : 'text-red-600') + '">' + fmt(bal) + '</p>' +
                    '<div class="flex justify-between text-[10px] text-gray-400 mt-1">' +
                        '<span>In: ' + fmt(a.total_in) + '</span>' +
                        '<span>Out: ' + fmt(a.total_out) + '</span>' +
                    '</div>' +
                '</div>';
            }).join('');
            acctTotal.textContent = fmt(totalBal);
        } else {
            acctSection.classList.add('hidden');
        }

    } catch (e) { console.error('Overview failed:', e); }
}

function switchToApprovals() {
    document.querySelector('[data-tab="approvals"]')?.click();
}

function renderTrendChart(trend) {
    const ctx = document.getElementById('trend-chart');
    if (trendChart) trendChart.destroy();
    const months = Object.keys(trend).sort();
    if (months.length === 0) return;
    trendChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months.map(m => new Date(m + '-01').toLocaleDateString('en-US', { month: 'short', year: '2-digit' })),
            datasets: [
                {
                    label: 'Income',
                    data: months.map(m => trend[m]?.income || 0),
                    backgroundColor: 'rgba(34,197,94,0.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Expenses',
                    data: months.map(m => trend[m]?.expense || 0),
                    backgroundColor: 'rgba(239,68,68,0.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f3f4f6' },
                    ticks: {
                        font: { size: 10 },
                        callback: v => v >= 1000000 ? 'TZS ' + (v/1000000).toFixed(1) + 'M'
                                     : v >= 1000    ? 'TZS ' + (v/1000).toFixed(0) + 'K'
                                     : 'TZS ' + v
                    }
                }
            }
        }
    });
}

function renderCategoryChart(breakdown) {
    const ctx = document.getElementById('category-chart');
    if (categoryChart) categoryChart.destroy();
    const legend = document.getElementById('category-legend');
    if (!breakdown || breakdown.length === 0) { legend.innerHTML = '<p class="text-xs text-gray-400 text-center">No data</p>'; return; }

    const palette = ['#22c55e','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
    const colors  = breakdown.map((_, i) => palette[i % palette.length]);
    const total   = breakdown.reduce((s, c) => s + parseFloat(c.total), 0);

    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: breakdown.map(c => c.name),
            datasets: [{
                data: breakdown.map(c => parseFloat(c.total)),
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.label + ': ' + fmt(ctx.raw) + ' (' + (ctx.raw / total * 100).toFixed(1) + '%)'
                    }
                }
            }
        }
    });

    // Custom legend
    legend.innerHTML = breakdown.slice(0, 6).map((c, i) =>
        '<div class="flex items-center justify-between text-xs">' +
        '<span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full flex-shrink-0" style="background:' + colors[i] + '"></span>' +
        '<span class="text-gray-600 truncate max-w-[110px]">' + c.name + '</span></span>' +
        '<span class="font-semibold text-gray-700 ml-2">' + (total > 0 ? (parseFloat(c.total)/total*100).toFixed(1) + '%' : '—') + '</span>' +
        '</div>'
    ).join('') + (breakdown.length > 6 ? '<p class="text-[10px] text-gray-400 text-center mt-1">+' + (breakdown.length - 6) + ' more</p>' : '');
}

// ═══════════ TAB 2: INCOME ═══════════
// shared row renderer used by both income and expense tabs
function renderEntryRow(r, amountClass, showSource) {
    const isPending  = r.approval_status === 'pending';
    const rowOpacity = isPending ? ' opacity-60' : '';
    const statusBadge = isPending
        ? '<span class="ml-1.5 px-1.5 py-0.5 text-[10px] bg-amber-100 text-amber-700 rounded-full font-semibold align-middle">Pending</span>'
        : '';
    const sourceCell = showSource
        ? '<td class="px-4 py-3 text-xs"><span class="px-2 py-0.5 rounded-md capitalize ' + (r.source_type === 'event' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600') + '">' + (r.source_type || '-') + '</span></td>'
        : '';
    const printBtn = (!isPending)
        ? '<button onclick="printEntryReceipt(' + r.id + ')" class="p-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition text-xs" title="Print Receipt"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9v6h12V9M6 9l3-3h6l3 3"/></svg></button>'
        : '';
    return '<tr class="hover:bg-gray-50 transition' + rowOpacity + '">' +
        '<td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">' + (r.entry_date || '-') + '</td>' +
        '<td class="px-4 py-3 text-xs font-mono text-gray-400 whitespace-nowrap">' + (r.entry_no || '') + statusBadge + '</td>' +
        '<td class="px-4 py-3 text-sm font-semibold text-gray-800">' + (r.category_name || '-') + '</td>' +
        (showSource ? sourceCell : '<td class="px-4 py-3 text-xs text-gray-500">' + (r.first_name ? r.first_name + ' ' + r.last_name : '-') + '</td>') +
        '<td class="px-4 py-3 text-right font-bold ' + amountClass + ' whitespace-nowrap">' + (isPending ? '<span class="line-through opacity-50">' + fmt(r.amount) + '</span>' : fmt(r.amount)) + '</td>' +
        '<td class="px-4 py-3 text-xs text-gray-500 capitalize">' + (r.payment_method || '').replace('_', ' ') + '</td>' +
        '<td class="px-4 py-3 text-xs text-gray-400 truncate max-w-[160px]" title="' + (r.description || '').replace(/"/g, '&quot;') + '">' + (r.description || '-') + '</td>' +
        '<td class="px-4 py-3 text-center">' + printBtn + '</td>' +
        '</tr>';
}

async function loadIncome() {
    const cat = document.getElementById('income-cat-filter').value;
    const from = document.getElementById('income-from').value;
    const to = document.getElementById('income-to').value;
    let url = API + '/finance/entries/filtered?type=income';
    if (cat) url += '&category=' + cat;
    if (from) url += '&date_from=' + from;
    if (to) url += '&date_to=' + to;
    try {
        const res = await fetch(url);
        const rows = (await res.json()).data || [];
        const tbody = document.getElementById('income-tbody');
        const empty = document.getElementById('income-empty');
        if (rows.length === 0) { tbody.innerHTML = ''; empty.classList.remove('hidden'); }
        else {
            empty.classList.add('hidden');
            tbody.innerHTML = rows.map(r => renderEntryRow(r, 'text-emerald-600', false)).join('');
        }
        // Totals only sum approved entries
        const approvedRows = rows.filter(r => !r.approval_status || r.approval_status === 'approved');
        document.getElementById('income-count').textContent = approvedRows.length + ' approved' + (rows.length > approvedRows.length ? ' (' + (rows.length - approvedRows.length) + ' pending)' : '');
        document.getElementById('income-total').textContent = fmt(approvedRows.reduce((s, r) => s + parseFloat(r.amount), 0));
    } catch (e) { console.error('Income failed:', e); }
}

// ═══════════ TAB 3: EXPENSES ═══════════
async function loadExpenses() {
    const cat = document.getElementById('expenses-cat-filter').value;
    const src = document.getElementById('expenses-source-filter')?.value || '';
    const from = document.getElementById('expenses-from')?.value || '';
    const to   = document.getElementById('expenses-to')?.value   || '';
    let url = API + '/finance/entries/filtered?type=expense';
    if (cat) url += '&category=' + cat;
    if (src) url += '&source_type=' + src;
    if (from) url += '&date_from=' + from;
    if (to)   url += '&date_to=' + to;
    try {
        const res = await fetch(url);
        const rows = (await res.json()).data || [];
        const tbody = document.getElementById('expenses-tbody');
        const empty = document.getElementById('expenses-empty');
        if (rows.length === 0) { tbody.innerHTML = ''; empty.classList.remove('hidden'); }
        else {
            empty.classList.add('hidden');
            tbody.innerHTML = rows.map(r => renderEntryRow(r, 'text-red-600', true)).join('');
        }
        // Totals only sum approved entries
        const approvedRows = rows.filter(r => !r.approval_status || r.approval_status === 'approved');
        document.getElementById('expenses-count').textContent = approvedRows.length + ' approved' + (rows.length > approvedRows.length ? ' (' + (rows.length - approvedRows.length) + ' pending)' : '');
        document.getElementById('expenses-total').textContent = fmt(approvedRows.reduce((s, r) => s + parseFloat(r.amount), 0));
    } catch (e) { console.error('Expenses failed:', e); }
}

// ═══════════ TAB 4: PLEDGES ═══════════
async function loadPledges() {
    try {
        const res = await fetch(API + '/finance/pledges');
        const rows = (await res.json()).data || [];
        const tbody = document.getElementById('pledges-tbody');
        const empty = document.getElementById('pledges-empty');
        const sc = { active:'bg-blue-100 text-blue-800', completed:'bg-green-100 text-green-800', overdue:'bg-red-100 text-red-800', cancelled:'bg-gray-100 text-gray-800' };
        if (rows.length === 0) { tbody.innerHTML = ''; empty.classList.remove('hidden'); }
        else {
            empty.classList.add('hidden');
            tbody.innerHTML = rows.map(r => {
                const pct = r.total_amount > 0 ? Math.round(r.paid_amount / r.total_amount * 100) : 0;
                return '<tr class="hover:bg-gray-50">' +
                    '<td class="px-4 py-3 text-xs font-mono text-gray-500">' + r.pledge_no + '</td>' +
                    '<td class="px-4 py-3 text-sm font-medium text-gray-900">' + r.first_name + ' ' + r.last_name + '</td>' +
                    '<td class="px-4 py-3 text-xs text-gray-600">' + (r.campaign || '-') + '</td>' +
                    '<td class="px-4 py-3 text-right font-semibold text-gray-900">' + fmt(r.total_amount) + '</td>' +
                    '<td class="px-4 py-3 text-right text-green-600 font-medium">' + fmt(r.paid_amount) + '</td>' +
                    '<td class="px-4 py-3 text-right text-amber-600 font-medium">' + fmt(r.balance) + '</td>' +
                    '<td class="px-4 py-3 text-xs text-gray-600">' + (r.due_date || '-') + '</td>' +
                    '<td class="px-4 py-3"><div class="flex items-center gap-2">' +
                    '<span class="px-2 py-0.5 rounded-full text-xs font-medium ' + (sc[r.status]||'bg-gray-100') + '">' + r.status + '</span>' +
                    '<div class="w-16 bg-gray-200 rounded-full h-1.5"><div class="bg-green-500 h-1.5 rounded-full" style="width:' + pct + '%"></div></div>' +
                    '<span class="text-xs text-gray-400">' + pct + '%</span></div></td></tr>';
            }).join('');
        }
    } catch (e) { console.error('Pledges failed:', e); }
}

async function loadPledgeStats() {
    try {
        const res = await fetch(API + '/finance/pledges/stats');
        const d = (await res.json()).data;
        document.getElementById('pledge-total-count').textContent = d.total || 0;
        document.getElementById('pledge-total-pledged').textContent = fmt(d.total_pledged);
        document.getElementById('pledge-total-paid').textContent = fmt(d.total_paid);
        document.getElementById('pledge-total-balance').textContent = fmt(d.total_balance);
    } catch (e) { console.error('Pledge stats failed:', e); }
}

// ═══════════ TAB 5: BUDGETS ═══════════
let allBudgets = [];
let currentDetailBudget = null;

// Budget sub-tab switching
document.querySelectorAll('.btab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.btab').forEach(t => { t.classList.remove('btab-active','border-royal-600','text-royal-700','bg-white'); t.classList.add('border-transparent','text-gray-500'); });
        btn.classList.add('btab-active','border-royal-600','text-royal-700','bg-white');
        btn.classList.remove('border-transparent','text-gray-500');
        document.querySelectorAll('.btab-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('btab-' + btn.dataset.btab).classList.remove('hidden');
    });
});

async function loadBudgets() {
    try {
        const res = await fetch(API + '/finance/budgets');
        allBudgets = (await res.json()).data || [];
        renderBudgetRequests();
        renderActiveBudgets();
        renderClosedBudgets();
        renderTrailReport();
    } catch (e) { console.error('Budgets failed:', e); }
}

function budgetLabel(r) {
    return (r.event_title ? r.event_title + ' — ' : '') + r.department;
}

function renderBudgetRequests() {
    const rows = allBudgets.filter(r => r.status === 'submitted' || r.status === 'draft' || r.status === 'rejected');
    const tbody = document.getElementById('budget-requests-tbody');
    const empty = document.getElementById('budget-requests-empty');
    const sc = { draft:'bg-gray-100 text-gray-600', submitted:'bg-amber-100 text-amber-800', rejected:'bg-red-100 text-red-800' };
    if (!rows.length) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    tbody.innerHTML = rows.map(r => {
        let actions = '';
        if (r.status === 'submitted') {
            const bwf = canUserApproveType('budget');
            if (bwf.canApprove) {
                actions = '<div class="flex items-center justify-center gap-1">' +
                    '<button onclick="approveBudget(' + r.id + ',\'approved\')" class="px-2.5 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold">Approve</button>' +
                    '<button onclick="approveBudget(' + r.id + ',\'rejected\')" class="px-2.5 py-1 text-xs bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold">Reject</button>' +
                    '</div>';
            } else {
                actions = '<span class="text-[10px] text-mist-400 italic">Not your level</span>';
            }
        } else if (r.status === 'rejected') {
            actions = '<span class="text-xs text-gray-400">Rejected</span>';
        }
        return '<tr class="hover:bg-gray-50">' +
            '<td class="px-4 py-3 text-sm font-semibold text-gray-900">' + budgetLabel(r) + '</td>' +
            '<td class="px-4 py-3 text-xs text-gray-600">' + r.fiscal_month + '</td>' +
            '<td class="px-4 py-3 text-right font-semibold text-gray-900">' + fmt(r.planned_amount) + '</td>' +
            '<td class="px-4 py-3 text-xs text-gray-600 max-w-[200px] truncate">' + (r.description || r.notes || '—') + '</td>' +
            '<td class="px-4 py-3 text-xs text-gray-600">' + (r.submitted_by_name || '—') + '</td>' +
            '<td class="px-4 py-3 text-xs text-gray-500">' + (r.created_at ? r.created_at.substring(0,10) : '—') + '</td>' +
            '<td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ' + (sc[r.status]||'') + '">' + r.status + '</span></td>' +
            '<td class="px-4 py-3 text-center">' + actions + '</td>' +
            '</tr>';
    }).join('');
}

function renderActiveBudgets() {
    const rows = allBudgets.filter(r => r.status === 'approved' || r.status === 'expenses_added');
    const tbody = document.getElementById('active-budgets-tbody');
    const empty = document.getElementById('active-budgets-empty');
    if (!rows.length) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    tbody.innerHTML = rows.map(r => {
        const planned = parseFloat(r.planned_amount) || 0;
        const used = parseFloat(r.total_used) || parseFloat(r.actual_amount) || 0;
        const reserved = parseFloat(r.reserved_amount) || 0;
        const available = planned - used - reserved;
        const pct = planned > 0 ? Math.round((used + reserved) / planned * 100) : 0;
        const barColor = pct >= 100 ? 'bg-red-500' : pct >= 80 ? 'bg-amber-500' : 'bg-blue-500';
        return '<tr class="hover:bg-gray-50 cursor-pointer" onclick=\'openBudgetDetail(' + JSON.stringify(r).replace(/'/g,"&#39;") + ')\'>' +
            '<td class="px-4 py-3 text-sm font-semibold text-royal-700">' + budgetLabel(r) + '</td>' +
            '<td class="px-4 py-3 text-xs text-gray-600">' + r.fiscal_month + '</td>' +
            '<td class="px-4 py-3 text-right font-semibold text-gray-900">' + fmt(planned) + '</td>' +
            '<td class="px-4 py-3 text-right font-semibold text-amber-600">' + fmt(reserved) + '</td>' +
            '<td class="px-4 py-3 text-right font-semibold text-red-600">' + fmt(used) + '</td>' +
            '<td class="px-4 py-3 text-right font-semibold ' + (available < 0 ? 'text-red-600' : 'text-emerald-600') + '">' + fmt(available) + '</td>' +
            '<td class="px-4 py-3"><div class="flex items-center gap-1.5">' +
                '<div class="w-16 bg-gray-200 rounded-full h-1.5"><div class="' + barColor + ' h-1.5 rounded-full" style="width:' + Math.min(pct,100) + '%"></div></div>' +
                '<span class="text-xs ' + (pct>=90?'text-red-600 font-bold':'text-gray-500') + '">' + pct + '%</span></div></td>' +
            '<td class="px-4 py-3 text-center"><button onclick="event.stopPropagation();openBudgetDetail(' + JSON.stringify(r).replace(/'/g,"&#39;") + ')" class="px-3 py-1.5 text-xs bg-royal-600 hover:bg-royal-700 text-white rounded-lg font-semibold">View</button></td></tr>';
    }).join('');
}

function renderClosedBudgets() {
    const rows = allBudgets.filter(r => r.status === 'closed');
    const tbody = document.getElementById('closed-budgets-tbody');
    const empty = document.getElementById('closed-budgets-empty');
    if (!rows.length) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    tbody.innerHTML = rows.map(r => {
        const planned = parseFloat(r.planned_amount) || 0;
        const used = parseFloat(r.total_used) || parseFloat(r.actual_amount) || 0;
        const surplus = planned - used;
        return '<tr class="hover:bg-gray-50 opacity-80">' +
            '<td class="px-4 py-3 text-sm font-medium text-gray-700">' + budgetLabel(r) + '</td>' +
            '<td class="px-4 py-3 text-xs text-gray-600">' + r.fiscal_month + '</td>' +
            '<td class="px-4 py-3 text-right font-semibold text-gray-700">' + fmt(planned) + '</td>' +
            '<td class="px-4 py-3 text-right font-semibold text-red-600">' + fmt(used) + '</td>' +
            '<td class="px-4 py-3 text-right font-semibold ' + (surplus < 0 ? 'text-red-500' : 'text-emerald-600') + '">' + fmt(surplus) + '</td>' +
            '<td class="px-4 py-3 text-xs text-gray-500">' + (r.closed_at ? r.closed_at.substring(0,10) : '—') + '</td>' +
            '<td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">Closed</span>' +
            (r.finance_entry_id ? ' <span class="text-[10px] text-green-600">Posted</span>' : '') + '</td></tr>';
    }).join('');
}

function renderTrailReport() {
    const rows = allBudgets.filter(r => r.status !== 'draft');
    const tbody = document.getElementById('trail-tbody');
    const sc = { submitted:'bg-amber-100 text-amber-800', approved:'bg-green-100 text-green-800', rejected:'bg-red-100 text-red-800', expenses_added:'bg-blue-100 text-blue-800', closed:'bg-purple-100 text-purple-700' };
    let totalApproved = 0, totalUsed = 0, totalReserved = 0, totalAvailable = 0;

    tbody.innerHTML = rows.map(r => {
        const planned = parseFloat(r.planned_amount) || 0;
        const used = parseFloat(r.total_used) || parseFloat(r.actual_amount) || 0;
        const reserved = parseFloat(r.reserved_amount) || 0;
        const available = planned - used - reserved;
        if (r.status !== 'submitted' && r.status !== 'rejected') {
            totalApproved += planned;
            totalUsed += used;
            totalReserved += reserved;
            totalAvailable += available;
        }
        return '<tr class="hover:bg-gray-50">' +
            '<td class="px-3 py-2 text-sm font-medium text-gray-800">' + budgetLabel(r) + '</td>' +
            '<td class="px-3 py-2 text-xs text-gray-600">' + r.fiscal_month + '</td>' +
            '<td class="px-3 py-2 text-right text-sm font-medium text-gray-700">' + fmt(parseFloat(r.planned_amount) || 0) + '</td>' +
            '<td class="px-3 py-2 text-right text-sm font-semibold text-gray-900">' + fmt(planned) + '</td>' +
            '<td class="px-3 py-2 text-right text-sm font-semibold text-amber-600">' + fmt(reserved) + '</td>' +
            '<td class="px-3 py-2 text-right text-sm font-semibold text-red-600">' + fmt(used) + '</td>' +
            '<td class="px-3 py-2 text-right text-sm font-semibold ' + (available < 0 ? 'text-red-500' : 'text-emerald-600') + '">' + fmt(available) + '</td>' +
            '<td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ' + (sc[r.status]||'bg-gray-100 text-gray-600') + '">' + r.status + '</span></td></tr>';
    }).join('');

    document.getElementById('trail-total-budgets').textContent = rows.length;
    document.getElementById('trail-total-approved').textContent = fmt(totalApproved);
    document.getElementById('trail-total-reserved').textContent = fmt(totalReserved);
    document.getElementById('trail-total-used').textContent = fmt(totalUsed);
    document.getElementById('trail-total-remaining').textContent = fmt(totalAvailable);
}

function printBudgetTrail() {
    const content = document.getElementById('trail-report-content').innerHTML;
    const w = window.open('', '_blank');
    w.document.write('<html><head><title>Budget Trail Report</title><link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"></head><body class="p-8"><h1 class="text-xl font-bold mb-4">Budget Trail Report</h1>' + content + '</body></html>');
    w.document.close();
    w.print();
}

// ── Active Budget Detail View ──
async function openBudgetDetail(r) {
    if (typeof r === 'string') r = JSON.parse(r);
    currentDetailBudget = r;
    const planned = parseFloat(r.planned_amount) || 0;
    const used = parseFloat(r.total_used) || parseFloat(r.actual_amount) || 0;
    const reserved = parseFloat(r.reserved_amount) || 0;
    const available = planned - used - reserved;
    const pct = planned > 0 ? Math.round((used + reserved) / planned * 100) : 0;

    document.getElementById('detail-budget-title').textContent = budgetLabel(r);
    document.getElementById('detail-budget-meta').textContent = r.fiscal_month + (r.description ? ' — ' + r.description : '');
    document.getElementById('detail-approved').textContent = fmt(planned);
    document.getElementById('detail-reserved').textContent = fmt(reserved);
    document.getElementById('detail-used').textContent = fmt(used);
    document.getElementById('detail-remaining').textContent = fmt(available);

    const barColor = pct >= 100 ? 'bg-red-500' : pct >= 80 ? 'bg-amber-500' : 'bg-blue-500';
    const bar = document.getElementById('detail-progress-bar');
    bar.className = barColor + ' h-2 rounded-full transition-all duration-500';
    bar.style.width = Math.min(pct, 100) + '%';
    document.getElementById('detail-progress-label').textContent = pct + '% committed';

    // Show detail, hide list
    document.getElementById('active-budgets-list').classList.add('hidden');
    document.getElementById('active-budget-detail').classList.remove('hidden');

    // Update expense modal label
    document.getElementById('expense-budget-label').textContent = budgetLabel(r) + ' — ' + r.fiscal_month;
    document.getElementById('expense-budget-id').value = r.id;
    document.getElementById('expense-remaining-info').innerHTML = '<b>Approved:</b> ' + fmt(planned) + ' | <b>Reserved:</b> ' + fmt(reserved) + ' | <b>Used:</b> ' + fmt(used) + ' | <b>Available:</b> ' + fmt(available);

    // Load expense items
    await loadBudgetExpenses(r.id);
}

function closeBudgetDetail() {
    document.getElementById('active-budget-detail').classList.add('hidden');
    document.getElementById('active-budgets-list').classList.remove('hidden');
    currentDetailBudget = null;
}

async function loadBudgetExpenses(budgetId) {
    try {
        const res = await fetch(API + '/finance/budgets/' + budgetId + '/expenses');
        const items = (await res.json()).data || [];
        const tbody = document.getElementById('detail-expenses-tbody');
        const empty = document.getElementById('detail-expenses-empty');
        if (!items.length) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
        empty.classList.add('hidden');
        tbody.innerHTML = items.map(e =>
            '<tr class="hover:bg-gray-50">' +
            '<td class="px-3 py-2 text-sm font-medium text-gray-800">' + e.item_name + '</td>' +
            '<td class="px-3 py-2 text-right text-sm font-semibold text-red-600">' + fmt(e.amount) + '</td>' +
            '<td class="px-3 py-2 text-xs text-gray-500">' + e.expense_date + '</td>' +
            '<td class="px-3 py-2 text-xs text-gray-500">' + (e.notes || '—') + '</td>' +
            '<td class="px-3 py-2 text-xs text-gray-500">' + (e.recorded_by_name || '—') + '</td>' +
            '<td class="px-3 py-2 text-center"><button onclick="deleteExpenseItem(' + budgetId + ',' + e.id + ')" class="text-xs text-red-500 hover:text-red-700 font-semibold">Remove</button></td></tr>'
        ).join('');
    } catch (e) { console.error('Load expenses failed:', e); }
}

// Add expense form
document.getElementById('add-expense-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const budgetId = document.getElementById('expense-budget-id').value;
    const payload = {
        item_name: document.getElementById('expense-item-name').value,
        amount: document.getElementById('expense-amount').value,
        expense_date: document.getElementById('expense-date').value || new Date().toISOString().substring(0,10),
        notes: document.getElementById('expense-notes').value,
    };
    try {
        const res = await fetch(API + '/finance/budgets/' + budgetId + '/expenses', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed');
        showToast('Expense added', 'success');
        closeModal('add-expense-modal');
        this.reset();
        document.getElementById('expense-budget-id').value = budgetId;
        // Reload data and refresh detail
        await loadBudgets();
        const updated = allBudgets.find(b => b.id == budgetId);
        if (updated) openBudgetDetail(updated);
    } catch (err) { showToast(err.message, 'error'); }
});

async function deleteExpenseItem(budgetId, expenseId) {
    if (!confirm('Remove this expense item?')) return;
    try {
        const res = await fetch(API + '/finance/budgets/' + budgetId + '/expenses/' + expenseId, { method: 'DELETE' });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed');
        showToast('Expense removed', 'success');
        await loadBudgets();
        const updated = allBudgets.find(b => b.id == budgetId);
        if (updated) openBudgetDetail(updated);
    } catch (err) { showToast(err.message, 'error'); }
}

// Close budget from detail view
function openCloseBudgetFromDetail() {
    const r = currentDetailBudget;
    if (!r) return;
    const planned = parseFloat(r.planned_amount) || 0;
    const used = parseFloat(r.total_used) || parseFloat(r.actual_amount) || 0;
    const remaining = planned - used;
    document.getElementById('close-budget-id').value = r.id;
    document.getElementById('close-budget-label').textContent = budgetLabel(r) + ' — ' + r.fiscal_month;
    document.getElementById('close-budget-notes').value = '';
    document.getElementById('close-budget-summary').innerHTML =
        '<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">' +
        '<div class="p-3 bg-blue-50 rounded-xl text-center"><p class="text-sm font-bold text-blue-700">' + fmt(planned) + '</p><p class="text-xs text-blue-500">Approved</p></div>' +
        '<div class="p-3 bg-red-50 rounded-xl text-center"><p class="text-sm font-bold text-red-700">' + fmt(used) + '</p><p class="text-xs text-red-500">Total Used</p></div>' +
        '<div class="p-3 ' + (remaining >= 0 ? 'bg-emerald-50' : 'bg-orange-50') + ' rounded-xl text-center"><p class="text-sm font-bold ' + (remaining >= 0 ? 'text-emerald-700' : 'text-orange-700') + '">' + fmt(Math.abs(remaining)) + '</p><p class="text-xs ' + (remaining >= 0 ? 'text-emerald-500' : 'text-orange-500') + '">' + (remaining >= 0 ? 'Remaining' : 'Over budget') + '</p></div></div>';
    openModal('close-budget-modal');
}

document.getElementById('close-budget-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const id    = document.getElementById('close-budget-id').value;
    const notes = document.getElementById('close-budget-notes').value;
    try {
        const res = await fetch(API + '/finance/budgets/' + id + '/close', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ notes })
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed');
        closeModal('close-budget-modal');
        closeBudgetDetail();
        showToast('Budget closed — expenses posted to finance entries', 'success');
        loadBudgets();
        loadOverview();
    } catch (err) { showToast(err.message, 'error'); }
});

// ═══════════ TAB 6: APPROVALS ═══════════
async function loadApprovals() {
    // --- helpers ---
    function setApprState(prefix, state, msg) {
        ['loading','wrap','empty','error'].forEach(s => {
            const el = document.getElementById('appr-' + prefix + '-' + s);
            if (el) el.classList.toggle('hidden', s !== state);
        });
        if (state === 'error' && msg) {
            const el = document.getElementById('appr-' + prefix + '-error');
            if (el) el.textContent = msg;
        }
    }

    // Fetch workflow config once (if not already loaded in loadMeta)
    if (!Object.keys(approvalWorkflows).length) {
        try {
            const wfRes = await fetch(API + '/settings/approval-workflows');
            const wfPayload = await wfRes.json();
            if (wfPayload.success && wfPayload.data) {
                wfPayload.data.forEach(w => {
                    if (!approvalWorkflows[w.workflow_type]) approvalWorkflows[w.workflow_type] = [];
                    approvalWorkflows[w.workflow_type].push({ level_no: +w.level_no, role_id: +w.role_id, role_name: w.role_name });
                });
            }
        } catch (e) { /* non-critical */ }
    }

    setApprState('entries', 'loading');
    setApprState('budgets', 'loading');

    const entryWf = canUserApproveType('finance_entry');
    const budgetWf = canUserApproveType('budget');

    // ── Finance Entries (independent) ─────────────────────────────────────
    try {
        const res = await fetch(API + '/finance/entries/filtered?approval=pending');
        if (!res.ok) throw new Error('Server error ' + res.status);
        const payload = await res.json();
        if (!payload.success) throw new Error(payload.message || 'Failed to load entries');
        const entries = payload.data || [];

        const badge = document.getElementById('appr-entry-count-badge');
        if (entries.length > 0) {
            badge.textContent = entries.length + ' entr' + (entries.length === 1 ? 'y' : 'ies') + ' pending';
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }

        const tbody = document.getElementById('appr-entries-tbody');
        if (entries.length === 0) {
            tbody.innerHTML = '';
            setApprState('entries', 'empty');
        } else {
            tbody.innerHTML = entries.map(r => {
                const rejCount = parseInt(r.rejection_count || 0);
                const resubBadge = rejCount > 0
                    ? '<span class="ml-1 px-1.5 py-0.5 text-[10px] bg-amber-100 text-amber-700 rounded-full font-semibold">Resubmitted' + (rejCount > 1 ? ' \xd7' + rejCount : '') + '</span>'
                    : '';
                const rejReason = r.rejection_reason
                    ? '<div class="text-[10px] text-red-500 mt-0.5 italic truncate max-w-[200px]" title="' + (r.rejection_reason || '').replace(/"/g, '&quot;') + '">⚠ ' + r.rejection_reason + '</div>'
                    : '';
                const srcColor = r.source_type === 'event' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600';
                return '<tr class="hover:bg-gray-50 transition">' +
                    '<td class="px-4 py-3 text-xs font-mono text-mist-600 whitespace-nowrap">' + (r.entry_no || '-') + resubBadge + '</td>' +
                    '<td class="px-4 py-3 text-xs text-mist-600 whitespace-nowrap">' + (r.entry_date || '-') + '</td>' +
                    '<td class="px-4 py-3 text-sm text-royal-800 font-medium">' + (r.category_name || '-') + '</td>' +
                    '<td class="px-4 py-3 text-right font-semibold text-red-600 whitespace-nowrap">' + fmt(r.amount) + '</td>' +
                    '<td class="px-4 py-3 text-xs"><span class="px-2 py-0.5 rounded-md ' + srcColor + ' font-medium capitalize">' + (r.source_type || '-') + '</span></td>' +
                    '<td class="px-4 py-3 text-xs text-mist-500 max-w-[160px]"><div class="truncate" title="' + (r.description || '').replace(/"/g, '&quot;') + '">' + (r.description || '-') + '</div>' + rejReason + '</td>' +
                    '<td class="px-4 py-3 text-xs text-mist-600">' + (r.recorded_by_name || '-') + '</td>' +
                    '<td class="px-4 py-3 text-center">' +
                    '<div class="flex items-center justify-center gap-1.5 flex-wrap">' +
                    (entryWf.canApprove
                        ? '<button onclick="approveEntry(' + r.id + ',\'approved\')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs rounded-lg font-semibold transition active:scale-95">Approve</button>' +
                          '<button onclick="approveEntry(' + r.id + ',\'rejected\')" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs rounded-lg font-semibold transition active:scale-95">Reject</button>'
                        : '<span class="text-[10px] text-mist-400 italic">Not your approval level</span>') +
                    (r.source_type === 'event' && r.event_id ? '<button onclick="openEventBudgetReview(' + r.event_id + ')" class="px-3 py-1.5 bg-royal-700 hover:bg-royal-800 text-white text-xs rounded-lg font-semibold transition active:scale-95">View Budget</button>' : '') +
                    '</div></td></tr>';
            }).join('');
            setApprState('entries', 'wrap');
        }
    } catch (e) {
        console.error('Entries load failed:', e);
        setApprState('entries', 'error', 'Could not load entries: ' + e.message);
    }

    // ── Department Budgets (independent) ──────────────────────────────────
    try {
        const res = await fetch(API + '/finance/budgets?status=submitted');
        if (!res.ok) throw new Error('Server error ' + res.status);
        const payload = await res.json();
        if (!payload.success) throw new Error(payload.message || 'Failed to load budgets');
        const budgets = payload.data || [];

        const badge = document.getElementById('appr-budget-count-badge');
        if (budgets.length > 0) {
            badge.textContent = budgets.length + ' budget' + (budgets.length === 1 ? '' : 's') + ' pending';
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }

        const tbody = document.getElementById('appr-budgets-tbody');
        if (budgets.length === 0) {
            tbody.innerHTML = '';
            setApprState('budgets', 'empty');
        } else {
            tbody.innerHTML = budgets.map(r =>
                '<tr class="hover:bg-gray-50 transition">' +
                '<td class="px-4 py-3 text-sm font-semibold text-royal-800">' + (r.department || '-') + '</td>' +
                '<td class="px-4 py-3 text-xs text-mist-600">' + (r.fiscal_month || '-') + '</td>' +
                '<td class="px-4 py-3 text-right font-semibold text-mist-700 whitespace-nowrap">' + fmt(r.planned_amount) + '</td>' +
                '<td class="px-4 py-3 text-xs text-mist-600">' + (r.submitted_by_name || '-') + '</td>' +
                '<td class="px-4 py-3 text-center">' +
                '<div class="flex items-center justify-center gap-1.5">' +
                (budgetWf.canApprove
                    ? '<button onclick="approveBudget(' + r.id + ',\'approved\')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs rounded-lg font-semibold transition active:scale-95">Approve</button>' +
                      '<button onclick="approveBudget(' + r.id + ',\'rejected\')" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs rounded-lg font-semibold transition active:scale-95">Reject</button>'
                    : '<span class="text-[10px] text-mist-400 italic">Not your approval level</span>') +
                (budgetWf.levelInfo ? '<div class="text-[10px] text-mist-400 mt-1">' + budgetWf.levelInfo + '</div>' : '') +
                '</div></td></tr>'
            ).join('');
            setApprState('budgets', 'wrap');
        }
    } catch (e) {
        console.error('Budgets load failed:', e);
        setApprState('budgets', 'error', 'Could not load department budgets: ' + e.message);
    }
}

async function openEventBudgetReview(eventId) {
    activeBudgetReviewEventId = eventId;
    try {
        const res = await fetch(API + '/events/' + eventId + '/details');
        const payload = await res.json();
        if (!res.ok || !payload.success) {
            throw new Error(payload.message || 'Unable to load event budget details');
        }

        const data = payload.data;
        const summary = document.getElementById('budget-review-summary');
        summary.innerHTML = '<strong>Event:</strong> ' + (data.overview?.title || '-') +
            ' | <strong>Total Budget:</strong> ' + fmt(data.overview?.budget_total || 0) +
            ' | <strong>Actual:</strong> ' + fmt(data.budget?.actual_expenses || 0);

        const tbody = document.getElementById('budget-review-tbody');
        const rows = data.budget?.items || [];
        tbody.innerHTML = rows.map(item =>
            '<tr>' +
            '<td class="px-3 py-2"><input id="rv-name-' + item.id + '" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" value="' + (item.item_name || '') + '"></td>' +
            '<td class="px-3 py-2"><select id="rv-type-' + item.id + '" class="border border-gray-300 rounded px-2 py-1 text-xs"><option value="income"' + (item.item_type === 'income' ? ' selected' : '') + '>income</option><option value="expense"' + (item.item_type === 'expense' ? ' selected' : '') + '>expense</option></select></td>' +
            '<td class="px-3 py-2"><input id="rv-planned-' + item.id + '" type="number" min="0" step="0.01" class="w-28 border border-gray-300 rounded px-2 py-1 text-xs" value="' + (item.planned_amount || 0) + '"></td>' +
            '<td class="px-3 py-2"><input id="rv-actual-' + item.id + '" type="number" min="0" step="0.01" class="w-28 border border-gray-300 rounded px-2 py-1 text-xs" value="' + (item.actual_amount || 0) + '"></td>' +
            '<td class="px-3 py-2"><input id="rv-note-' + item.id + '" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Reason / correction" value="' + (item.notes || '') + '"></td>' +
            '<td class="px-3 py-2"><div class="flex gap-1"><button onclick="saveBudgetReviewItem(' + item.id + ', false)" class="px-2 py-1 bg-blue-600 text-white rounded text-xs font-semibold">Save</button><button onclick="saveBudgetReviewItem(' + item.id + ', true)" class="px-2 py-1 bg-red-600 text-white rounded text-xs font-semibold">Mark Rejected</button></div></td>' +
            '</tr>'
        ).join('');

        openModal('event-budget-review-modal');
    } catch (e) {
        showToast('Error: ' + (e.message || 'Failed to load budget review'), 'error');
    }
}

async function saveBudgetReviewItem(itemId, markRejected) {
    if (!activeBudgetReviewEventId) return;
    try {
        const note = document.getElementById('rv-note-' + itemId).value || '';
        const payload = {
            item_name: document.getElementById('rv-name-' + itemId).value || '',
            item_type: document.getElementById('rv-type-' + itemId).value || 'expense',
            planned_amount: document.getElementById('rv-planned-' + itemId).value || 0,
            actual_amount: document.getElementById('rv-actual-' + itemId).value || 0,
            notes: markRejected ? ('[REJECTED] ' + note) : note,
        };

        const res = await fetch(API + '/events/' + activeBudgetReviewEventId + '/budget-items/' + itemId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Failed to save');
        }
        
        showToast(markRejected ? 'Success: Item marked as rejected and saved' : 'Success: Budget item updated', 'success');
        await openEventBudgetReview(activeBudgetReviewEventId);
    } catch (e) {
        showToast('Error: ' + (e.message || 'Failed to save budget item'), 'error');
    }
}

async function approveEntry(id, decision) {
    try {
        const title = decision === 'approved' ? 'Approve Entry?' : 'Reject Entry?';
        const message = decision === 'approved'
            ? `Approve entry #${id}? A receipt will be available to print.`
            : `Reject entry #${id}? Please provide a reason for rejection.`;
        const isDestructive = decision === 'rejected';
        
        // For rejection, prompt for reason
        let rejectionNotes = '';
        if (decision === 'rejected') {
            rejectionNotes = prompt('Sababu ya kukataa (Rejection reason):');
            if (rejectionNotes === null) return; // User cancelled
            if (rejectionNotes.trim() === '') {
                showToast('Please provide a reason for rejection', 'error');
                return;
            }
        }

        showConfirmDialog(
            title,
            message,
            decision === 'approved' ? 'Approve' : 'Reject',
            async () => {
                try {
                    const body = { decision };
                    if (rejectionNotes) body.notes = rejectionNotes;
                    
                    const res = await fetch(API + '/finance/entries/' + id + '/approve', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    });
                    
                    if (!res.ok) {
                        const err = await res.json();
                        throw new Error(err.message || `Failed to ${decision}`);
                    }
                    
                    const data = await res.json();
                    showToast('Success: Entry ' + decision + ' successfully', 'success');
                    await Promise.all([ loadApprovals(), loadOverview() ]);

                    // Offer print receipt after approval
                    if (decision === 'approved') {
                        setTimeout(() => {
                            showConfirmDialog(
                                'Print Receipt?',
                                'Entry approved. Would you like to print a receipt?',
                                'Print Receipt',
                                () => printEntryReceipt(id),
                                null,
                                false
                            );
                        }, 400);
                    }
                } catch (e) {
                    console.error('Approve failed:', e);
                    showToast('Error: ' + (e.message || 'Failed to approve/reject'), 'error');
                }
            },
            null,
            isDestructive
        );
    } catch (e) {
        console.error('approveEntry error:', e);
        showToast('Error: ' + (e.message || 'Unexpected error'), 'error');
    }
}

// ── Print receipt for an approved finance entry ──
async function printEntryReceipt(entryId) {
    try {
        const res = await fetch(API + '/finance/entries/filtered?approval=approved');
        const entries = (await res.json()).data || [];
        const entry = entries.find(e => e.id == entryId);
        
        if (!entry) {
            // Try fetching all entries as fallback
            const res2 = await fetch(API + '/finance/entries/filtered');
            const all = (await res2.json()).data || [];
            const fallback = all.find(e => e.id == entryId);
            if (!fallback) { showToast('Entry not found for receipt', 'error'); return; }
            Object.assign(entry || {}, fallback);
        }

        const e = entry || {};
        const receiptHtml = `<!DOCTYPE html><html><head><title>Finance Receipt - ${e.entry_no}</title>
        <style>
            body{font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:30px;color:#333}
            .header{text-align:center;border-bottom:2px solid #1e3a5f;padding-bottom:16px;margin-bottom:20px}
            .header h1{font-size:20px;color:#1e3a5f;margin:0 0 4px}
            .header p{font-size:12px;color:#666;margin:0}
            .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eee;font-size:14px}
            .row .label{color:#666;font-weight:600}
            .row .value{color:#222;font-weight:500}
            .amount{font-size:22px;font-weight:bold;color:#1e3a5f;text-align:center;padding:16px 0;margin:16px 0;background:#f0f5ff;border-radius:8px}
            .stamp{margin-top:24px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;text-align:center;font-size:13px}
            .stamp strong{color:#166534}
            .footer{margin-top:30px;text-align:center;font-size:11px;color:#999}
            @media print{body{margin:0;padding:10px}.no-print{display:none}}
        </style></head><body>
        <div class="header">
            <h1>FINANCE RECEIPT</h1>
            <p>Official Transaction Record</p>
        </div>
        <div class="row"><span class="label">Entry No:</span><span class="value">${e.entry_no || 'N/A'}</span></div>
        <div class="row"><span class="label">Date:</span><span class="value">${e.entry_date || 'N/A'}</span></div>
        <div class="row"><span class="label">Category:</span><span class="value">${e.category_name || 'N/A'}</span></div>
        <div class="row"><span class="label">Payment Method:</span><span class="value" style="text-transform:capitalize">${e.payment_method || 'N/A'}</span></div>
        <div class="row"><span class="label">Source:</span><span class="value" style="text-transform:capitalize">${e.source_type || 'Manual'}</span></div>
        <div class="amount">TZS ${parseFloat(e.amount || 0).toLocaleString('en-US', {minimumFractionDigits:2})}</div>
        <div class="row"><span class="label">Description:</span><span class="value" style="max-width:300px;text-align:right">${e.description || '-'}</span></div>
        <div class="row"><span class="label">Recorded By:</span><span class="value">${e.recorded_by_name || '-'}</span></div>
        <div class="stamp">
            <strong>APPROVED</strong><br>
            ${e.approved_by_name ? 'By: ' + e.approved_by_name : ''}
            ${e.approved_at ? ' &bull; ' + new Date(e.approved_at).toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'}) : ''}
        </div>
        <div class="footer">Generated on ${new Date().toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'})}</div>
        <div class="no-print" style="margin-top:20px;text-align:center">
            <button onclick="window.print()" style="padding:8px 24px;border:none;background:#1e3a5f;color:#fff;border-radius:6px;cursor:pointer;font-size:14px">Print</button>
            <button onclick="window.close()" style="padding:8px 24px;border:1px solid #ccc;background:#fff;color:#333;border-radius:6px;cursor:pointer;font-size:14px;margin-left:8px">Close</button>
        </div>
        </body></html>`;

        const w = window.open('', '_blank');
        w.document.write(receiptHtml);
        w.document.close();
    } catch (err) {
        console.error('Print receipt error:', err);
        showToast('Failed to generate receipt', 'error');
    }
}

async function approveBudget(id, decision) {
    try {
        const title = decision === 'approved' ? 'Approve Budget?' : 'Reject Budget?';
        const message = `Budget ID: ${id}\n\nThis action cannot be undone.`;
        const isDestructive = decision === 'rejected';

        let rejectionNotes = '';
        if (decision === 'rejected') {
            rejectionNotes = prompt('Sababu ya kukataa (Rejection reason):');
            if (rejectionNotes === null) return;
            if (rejectionNotes.trim() === '') {
                showToast('Please provide a reason for rejection', 'error');
                return;
            }
        }
        
        showConfirmDialog(
            title,
            message,
            decision === 'approved' ? 'Approve' : 'Reject',
            async () => {
                try {
                    const body = { decision };
                    if (rejectionNotes) body.notes = rejectionNotes;

                    const res = await fetch(API + '/finance/budgets/' + id + '/approve', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    });
                    
                    if (!res.ok) {
                        const err = await res.json();
                        throw new Error(err.message || `Failed to ${decision}`);
                    }
                    
                    const data = await res.json();
                    showToast('Success: Budget ' + decision + ' successfully', 'success');
                    await Promise.all([ loadApprovals(), loadBudgets(), loadOverview() ]);
                } catch (e) {
                    console.error('Approve budget failed:', e);
                    showToast('Error: ' + (e.message || 'Failed to approve/reject budget'), 'error');
                }
            },
            null,
            isDestructive
        );
    } catch (e) {
        console.error('approveBudget error:', e);
        showToast('Error: ' + (e.message || 'Unexpected error'), 'error');
    }
}

// ═══════════ TAB 7: REPORTS ═══════════
async function generateReport(type) {
    const output = document.getElementById('report-output');
    const content = document.getElementById('report-content');
    const title = document.getElementById('report-title');
    output.classList.remove('hidden');
    const month = document.getElementById('fin-month-select').value;

    if (type === 'monthly') {
        title.textContent = 'Monthly Report: ' + month;
        try {
            const res = await fetch(API + '/finance/overview?month=' + month);
            const d = (await res.json()).data;
            content.innerHTML =
                '<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">' +
                '<div class="p-4 bg-green-50 rounded-xl text-center"><p class="text-lg font-bold text-green-700">' + fmt(d.month_income) + '</p><p class="text-xs text-green-600">Income</p></div>' +
                '<div class="p-4 bg-red-50 rounded-xl text-center"><p class="text-lg font-bold text-red-700">' + fmt(d.month_expense) + '</p><p class="text-xs text-red-600">Expenses</p></div>' +
                '<div class="p-4 bg-blue-50 rounded-xl text-center"><p class="text-lg font-bold text-blue-700">' + fmt(d.month_balance) + '</p><p class="text-xs text-blue-600">Balance</p></div></div>' +
                '<h4 class="font-semibold text-sm mb-2">Category Breakdown</h4>' +
                '<table class="w-full text-sm"><thead><tr class="text-left text-xs text-gray-500 uppercase border-b"><th class="pb-2">Category</th><th class="pb-2">Type</th><th class="pb-2 text-right">Amount</th></tr></thead><tbody>' +
                (d.category_breakdown||[]).map(c =>
                    '<tr class="border-b border-gray-50"><td class="py-2">' + c.name + '</td><td class="py-2"><span class="px-2 py-0.5 rounded text-xs ' + (c.category_type==='income'?'bg-green-100 text-green-800':'bg-red-100 text-red-800') + '">' + c.category_type + '</span></td><td class="py-2 text-right font-medium">' + fmt(c.total) + '</td></tr>'
                ).join('') + '</tbody></table>';
        } catch (e) { content.innerHTML = '<p class="text-red-500">Failed to generate report</p>'; }
    } else if (type === 'category') {
        title.textContent = 'Category Report';
        try {
            const res = await fetch(API + '/finance/overview?month=' + month);
            const d = (await res.json()).data;
            const inc = (d.category_breakdown||[]).filter(c => c.category_type === 'income');
            const exp = (d.category_breakdown||[]).filter(c => c.category_type === 'expense');
            content.innerHTML =
                '<div class="grid grid-cols-2 gap-6">' +
                '<div><h4 class="font-semibold text-green-700 mb-2">Income</h4>' +
                (inc.length ? inc.map(c => '<div class="flex justify-between py-1.5 border-b border-gray-50"><span class="text-sm">' + c.name + '</span><span class="font-medium text-green-600">' + fmt(c.total) + '</span></div>').join('') : '<p class="text-gray-400 text-sm">None</p>') +
                '</div><div><h4 class="font-semibold text-red-700 mb-2">Expenses</h4>' +
                (exp.length ? exp.map(c => '<div class="flex justify-between py-1.5 border-b border-gray-50"><span class="text-sm">' + c.name + '</span><span class="font-medium text-red-600">' + fmt(c.total) + '</span></div>').join('') : '<p class="text-gray-400 text-sm">None</p>') +
                '</div></div>';
        } catch (e) { content.innerHTML = '<p class="text-red-500">Error</p>'; }
    } else if (type === 'pledge') {
        title.textContent = 'Pledge Report';
        try {
            const [pRes, sRes] = await Promise.all([fetch(API + '/finance/pledges'), fetch(API + '/finance/pledges/stats')]);
            const pledges = (await pRes.json()).data || [];
            const stats = (await sRes.json()).data;
            content.innerHTML =
                '<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">' +
                '<div class="p-3 bg-gray-50 rounded-xl text-center"><p class="text-lg font-bold">' + stats.total + '</p><p class="text-xs text-gray-500">Total</p></div>' +
                '<div class="p-3 bg-green-50 rounded-xl text-center"><p class="text-lg font-bold text-green-700">' + fmt(stats.total_pledged) + '</p><p class="text-xs text-green-600">Pledged</p></div>' +
                '<div class="p-3 bg-blue-50 rounded-xl text-center"><p class="text-lg font-bold text-blue-700">' + fmt(stats.total_paid) + '</p><p class="text-xs text-blue-600">Paid</p></div>' +
                '<div class="p-3 bg-amber-50 rounded-xl text-center"><p class="text-lg font-bold text-amber-700">' + fmt(stats.total_balance) + '</p><p class="text-xs text-amber-600">Outstanding</p></div></div>' +
                '<table class="w-full text-sm"><thead><tr class="text-left text-xs text-gray-500 uppercase border-b"><th class="pb-2">Member</th><th class="pb-2">Campaign</th><th class="pb-2 text-right">Pledged</th><th class="pb-2 text-right">Paid</th><th class="pb-2 text-right">Balance</th><th class="pb-2">Status</th></tr></thead><tbody>' +
                pledges.map(p => '<tr class="border-b border-gray-50"><td class="py-2">' + p.first_name + ' ' + p.last_name + '</td><td class="py-2 text-xs">' + (p.campaign||'-') + '</td><td class="py-2 text-right">' + fmt(p.total_amount) + '</td><td class="py-2 text-right text-green-600">' + fmt(p.paid_amount) + '</td><td class="py-2 text-right text-amber-600">' + fmt(p.balance) + '</td><td class="py-2"><span class="px-2 py-0.5 rounded text-xs">' + p.status + '</span></td></tr>').join('') +
                '</tbody></table>';
        } catch (e) { content.innerHTML = '<p class="text-red-500">Error</p>'; }
    }
}

function printReport() {
    const content = document.getElementById('report-output').innerHTML;
    const w = window.open('', '_blank');
    w.document.write('<html><head><title>Finance Report</title><link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"></head><body class="p-8">' + content + '</body></html>');
    w.document.close();
    w.print();
}

// ═══════════ FORM SUBMISSIONS ═══════════
document.getElementById('entry-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('entry-submit-btn');
    const origText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Saving...';
    const payload = Object.fromEntries(new FormData(this).entries());
    // Remove empty optional fields to keep payload clean
    if (!payload.member_id) delete payload.member_id;
    if (!payload.church_account_id) delete payload.church_account_id;
    try {
        const res = await fetch(API + '/finance/entries', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok) {
            this.reset();
            closeModal('entry-modal');
            showToast(data.message || 'Entry saved successfully!', 'success');
            loadOverview();
            // Reload current tab data if on income/expenses
            const activeTab = document.querySelector('.fin-tab-active')?.dataset?.tab;
            if (activeTab === 'income') loadIncome();
            else if (activeTab === 'expenses') loadExpenses();
        }
        else { alert(data.message || 'Error saving entry'); }
    } catch (err) { alert('Network error: ' + err.message); }
    finally { btn.disabled = false; btn.textContent = origText; }
});

document.getElementById('pledge-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const payload = Object.fromEntries(new FormData(this).entries());
    try {
        const res = await fetch(API + '/finance/pledges', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok) { this.reset(); closeModal('pledge-modal'); loadPledges(); loadPledgeStats(); }
        else { alert(data.message || 'Error'); }
    } catch (err) { alert('Network error'); }
});

document.getElementById('budget-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const origText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Submitting…';
    const payload = Object.fromEntries(new FormData(this).entries());
    try {
        const res = await fetch(API + '/finance/budgets', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok) {
            this.reset();
            closeModal('budget-modal');
            showToast('Budget request submitted successfully!', 'success');
            loadBudgets();
        } else {
            alert(data.message || 'Error submitting budget');
        }
    } catch (err) {
        alert('Network error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.textContent = origText;
    }
});

// Export current finance report as CSV (opens API CSV export in new tab)
function exportFinanceCSV() {
    try {
        const params = new URLSearchParams();
        // optional filters: date_from, date_to, event_id, status, department
        // use month selector to set a conservative date range for monthly reports
        const month = document.getElementById('fin-month-select')?.value;
        if (month) {
            const [yr, mo] = month.split('-');
            const from = yr + '-' + String(mo).padStart(2, '0') + '-01';
            const dt = new Date(yr, parseInt(mo,10), 0);
            const to = yr + '-' + String(mo).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');
            params.set('date_from', from);
            params.set('date_to', to);
        }
    
        const url = API + '/reports/export/csv' + (params.toString() ? ('?' + params.toString()) : '');
        window.open(url, '_blank');
    } catch (e) { console.error('CSV export failed', e); showToast('Unable to export CSV', 'error'); }
}

// Campaign form submission
document.getElementById('campaign-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const orig = btn.textContent;
    btn.disabled = true; btn.textContent = 'Saving...';
    const payload = Object.fromEntries(new FormData(this).entries());
    try {
        // Try to save via API (if server implements campaigns)
        const res = await fetch(API + '/campaigns', { method: 'POST', headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN}, body: JSON.stringify(payload) });
        if (res.status === 404) {
            // Server endpoint not implemented — close modal and notify user
            this.reset(); closeModal('campaign-modal'); showToast('Campaign registered locally (server endpoint not implemented)', 'info');
        } else {
            const data = await res.json();
            if (res.ok) {
                this.reset(); closeModal('campaign-modal'); showToast(data.message || 'Campaign registered', 'success');
                if (typeof loadCampaigns === 'function') loadCampaigns();
            } else {
                showToast(data.message || 'Failed to save campaign', 'error');
                console.warn('Campaign save returned:', data);
            }
        }
    } catch (err) {
        console.error('Campaign save error', err);
        showToast('Network error saving campaign', 'error');
    } finally { btn.disabled = false; btn.textContent = orig; }
});

// Pledge import form handler
document.getElementById('pledge-import-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = document.getElementById('pledge-import-form');
    const fileInput = document.getElementById('pledge-file-input');
    if (!fileInput.files || fileInput.files.length === 0) { showToast('Please select a CSV file', 'error'); return; }
    const fd = new FormData();
    fd.append('file', fileInput.files[0]);
    const btn = form.querySelector('button[type="submit"]');
    const orig = btn.textContent; btn.disabled = true; btn.textContent = 'Uploading...';
    try {
        const res = await fetch(API + '/finance/pledges/upload', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }, body: fd });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Pledges imported', 'success');
            closeModal('pledge-import-modal');
            loadPledges();
        } else {
            showToast(data.message || 'Import failed', 'error');
            console.warn('Import pledges failed', data);
        }
    } catch (err) {
        console.error('Pledge import error', err);
        showToast('Network error importing pledges', 'error');
    } finally { btn.disabled = false; btn.textContent = orig; }
});

// ═══════════ INIT ═══════════
buildMonthSelector();
loadMeta();
loadOverview();
document.querySelector('.fin-tab').classList.add('border-royal-600','text-royal-700');
</script>


</div>

<style>
.finance-module .fin-tab-active { border-color: #3344a5; color: #3344a5; }
.finance-module .fin-tab { font-family: inherit; }
.finance-module svg { color: inherit; }
</style>
