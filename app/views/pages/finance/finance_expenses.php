<!-- ═══════════ TAB 3: EXPENSES ═══════════ -->
<div id="tab-expenses" class="fin-panel hidden">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-gray-800">Expense Records</h2>
            <div class="flex gap-2 flex-wrap items-center">
                <select id="expenses-cat-filter" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs"><option value="">All Categories</option></select>
                <select id="expenses-source-filter" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs">
                    <option value="">All Sources</option>
                    <option value="manual">Manual</option>
                    <option value="procurement">Procurement</option>
                    <option value="event">Event</option>
                </select>
                <input type="date" id="expenses-from" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs" placeholder="From">
                <input type="date" id="expenses-to" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs" placeholder="To">
                <button onclick="loadExpenses()" class="px-3 py-1.5 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 font-semibold">Search</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Entry No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Source</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Amount (TZS)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Receipt</th>
                    </tr>
                </thead>
                <tbody id="expenses-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="expenses-empty" class="hidden px-5 py-12 text-center text-gray-400">
            <p class="font-medium">No expense records found</p>
        </div>
        <div class="px-5 py-3 border-t border-gray-100 flex justify-between items-center">
            <span id="expenses-count" class="text-sm text-gray-500">0 records</span>
            <span id="expenses-total" class="text-sm font-bold text-red-600">TZS 0</span>
        </div>
    </div>
</div>
