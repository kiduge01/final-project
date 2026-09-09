<!-- ═══════════ TAB 4: PLEDGES ═══════════ -->
<div id="tab-pledges" class="fin-panel hidden">
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900" id="pledge-total-count">0</p>
            <p class="text-xs text-gray-500">Total Pledges</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-green-600" id="pledge-total-pledged">TZS 0</p>
            <p class="text-xs text-gray-500">Total Pledged</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600" id="pledge-total-paid">TZS 0</p>
            <p class="text-xs text-gray-500">Total Paid</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-amber-600" id="pledge-total-balance">TZS 0</p>
            <p class="text-xs text-gray-500">Outstanding</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Pledge List</h2>
            <button onclick="openModal('pledge-modal')" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl">+ Add Pledge</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pledge No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Member</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Campaign</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Balance</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Due Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody id="pledges-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="pledges-empty" class="hidden px-5 py-12 text-center text-gray-400">
            <p class="font-medium">No pledges recorded</p>
        </div>
    </div>
</div>
