<!-- ═══════════ TAB 2: INCOME ═══════════ -->
<div id="tab-income" class="fin-panel hidden">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-gray-800">Income Records</h2>
            <div class="flex gap-2 flex-wrap">
                <select id="income-cat-filter" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs"><option value="">All Categories</option></select>
                <input type="date" id="income-from" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs">
                <input type="date" id="income-to" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs">
                <button onclick="loadIncome()" class="px-3 py-1.5 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 font-semibold">Search</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Entry No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Member</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Amount (TZS)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Receipt</th>
                    </tr>
                </thead>
                <tbody id="income-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="income-empty" class="hidden px-5 py-12 text-center text-gray-400">
            <p class="font-medium">No income records found</p>
        </div>
        <div class="px-5 py-3 border-t border-gray-100 flex justify-between items-center">
            <span id="income-count" class="text-sm text-gray-500">0 records</span>
            <span id="income-total" class="text-sm font-bold text-green-600">TZS 0</span>
        </div>
    </div>
</div>
