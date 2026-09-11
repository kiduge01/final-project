<!-- ═══════════ TAB 7: REPORTS ═══════════ -->
<div id="tab-reports" class="fin-panel hidden">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center cursor-pointer hover:border-primary-300 transition-colors" onclick="generateReport('monthly')">
            <div class="w-12 h-12 mx-auto rounded-xl bg-mist-100 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-royal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="font-semibold text-gray-900">Monthly Report</h3>
            <p class="text-sm text-gray-500 mt-1">Income, Expenses, Balance</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center cursor-pointer hover:border-primary-300 transition-colors" onclick="generateReport('category')">
            <div class="w-12 h-12 mx-auto rounded-xl bg-mist-100 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-royal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
            </div>
            <h3 class="font-semibold text-gray-900">Category Report</h3>
            <p class="text-sm text-gray-500 mt-1">Income & expense breakdown</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center cursor-pointer hover:border-primary-300 transition-colors" onclick="generateReport('pledge')">
            <div class="w-12 h-12 mx-auto rounded-xl bg-mist-100 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-royal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="font-semibold text-gray-900">Pledge Report</h3>
            <p class="text-sm text-gray-500 mt-1">Pledge status & payments</p>
        </div>
    </div>
    <div id="report-output" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 id="report-title" class="font-semibold text-gray-800">Report</h3>
            <button onclick="printReport()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs rounded-lg flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>
                Print
            </button>
        </div>
        <div id="report-content"></div>
    </div>
</div>
