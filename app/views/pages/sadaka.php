<?php
/** @var string $baseUrl */
$B = $baseUrl ?? '';
$categorySlug = $_GET['category'] ?? 'sadaka-za-upendo';
?>

<div class="sadaka-module">

    <style>
        /* Ensure button text appears white across this module */
        .sadaka-module button { color: #ffffff !important; }
        .sadaka-module button svg { color: inherit; }
    </style>

<!-- Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-heading font-semibold text-royal-900">Sadaka Management</h1>
        <p class="text-mist-600 text-sm mt-0.5">Manage church offerings and contributions</p>
    </div>
    <div class="flex items-center gap-3 flex-wrap">
        <select id="sadaka-month-select" class="rounded-xl border border-mist-200 px-3 py-2 text-sm text-mist-700 focus:ring-2 focus:ring-royal-300">
        </select>
        <button onclick="openModal('add-entry-modal')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white font-semibold rounded-xl shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Entry
        </button>
        <button onclick="openModal('upload-modal')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-glory-600 hover:bg-glory-700 text-white font-semibold rounded-xl shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Upload File
        </button>
        <button onclick="generateReport()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Generate Report
        </button>
        <button id="register-pledge-btn" onclick="openPledgeModal()" class="hidden inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Register Pledge
        </button>
        <button id="view-pledges-btn" onclick="loadPledges(); openModal('pledges-list-modal')" class="hidden inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/></svg>
            View Pledges
        </button>
        <button onclick="viewReport()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            View Report
        </button>
    </div>
</div>

<!-- Category Navigation -->
<div class="mb-6 border-b border-mist-200 overflow-x-auto">
    <nav class="flex gap-1 -mb-px whitespace-nowrap" id="sadaka-categories">
        <!-- Categories will be loaded here via JavaScript -->
    </nav>
</div>

<div id="sadaka-summary-grid" class="mb-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4"></div>

<!-- Main Content -->
<div class="bg-white rounded-2xl shadow-sm border border-mist-200 overflow-hidden">
    <div id="sadaka-loading" class="flex items-center justify-center p-12">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-royal-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 0316 0 8 8 0 01-16 0z"/>
            </svg>
            <span class="text-mist-600 font-medium">Loading sadaka data...</span>
        </div>
    </div>

    <!-- Table Container -->
    <div id="sadaka-table-container" class="hidden overflow-x-auto">
        <table class="w-full">
            <thead class="bg-mist-50 border-b border-mist-200 sticky top-0" id="sadaka-thead">
                <tr id="sadaka-header-row">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-mist-700 uppercase">Member</th>
                    <!-- Week headers will be inserted here by JavaScript -->
                    <th class="px-6 py-3 text-center text-xs font-semibold text-mist-700 uppercase">Monthly Total</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-royal-700 uppercase bg-royal-50">Yearly Total</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-mist-700 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="sadaka-tbody" class="divide-y divide-mist-200">
                <!-- Members and data will be loaded here -->
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    <div id="sadaka-empty" class="hidden flex flex-col items-center justify-center p-12 text-center">
        <svg class="w-16 h-16 text-mist-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="text-lg font-semibold text-mist-900 mb-1">No entries found</h3>
        <p class="text-mist-600">Add entries manually or upload a CSV/Excel file to get started.</p>
    </div>
</div>

</div>

<!-- ═════════════ MODALS ═════════════ -->

<!-- Report Modal -->
<div id="report-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('report-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-6 z-10 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-heading font-semibold text-royal-900">Sadaka Report</h3>
                <button onclick="closeModal('report-modal')" class="text-mist-400 hover:text-mist-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="report-content" class="space-y-4">
                <!-- Report will be generated here -->
            </div>

            <div class="mt-4 flex gap-3 justify-end border-t border-mist-200 pt-3 flex-wrap">
                <button onclick="exportReportCSV()" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Export CSV
                </button>
                <button onclick="closeModal('report-modal')" class="inline-flex items-center gap-2 px-3 py-2 bg-mist-300 hover:bg-mist-400 text-mist-900 font-semibold rounded-lg shadow-sm transition-colors text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

            <!-- Pledge Registration Modal -->
            <div id="pledge-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('pledge-modal')"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-xl font-heading font-semibold text-royal-900">Register Pledge (Michango / Ahadi)</h3>
                            <button onclick="closeModal('pledge-modal')" class="text-mist-400 hover:text-mist-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form id="pledge-form" class="space-y-4">
                            <input type="hidden" id="pledge-id" value="">
                            <div>
                                <label class="block text-sm font-semibold text-mist-700 mb-2">Pledge Name *</label>
                                <input type="text" id="pledge-name" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300" placeholder="e.g. Machangizo - Ahadi ya nyumba" required>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-mist-700 mb-2">Time Limit (months) *</label>
                                    <input type="number" id="pledge-months" min="1" max="120" value="12" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-mist-700 mb-2">Amount (TZS) *</label>
                                    <input type="number" id="pledge-amount" min="0" step="0.01" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-mist-700 mb-2">Extra Notes</label>
                                <textarea id="pledge-notes" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300 resize-none" rows="3" placeholder="Optional notes about this pledge"></textarea>
                            </div>

                            <div class="flex gap-3 pt-4">
                                <button type="button" onclick="closeModal('pledge-modal')" class="flex-1 px-4 py-2.5 border border-mist-200 text-mist-700 font-semibold rounded-xl hover:bg-mist-50 transition-colors">Cancel</button>
                                <button type="submit" class="flex-1 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl transition-colors">Save Pledge</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Pledges List Modal -->
            <div id="pledges-list-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('pledges-list-modal')"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-heading font-semibold text-royal-900">Registered Pledges</h3>
                            <div class="flex items-center gap-2">
                                <button onclick="exportPledgesCSV()" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">Export CSV</button>
                                <button onclick="closeModal('pledges-list-modal')" class="px-3 py-2 bg-mist-200 hover:bg-mist-300 text-mist-900 rounded-lg text-sm">Close</button>
                            </div>
                        </div>

                        <div id="pledges-list-content" class="space-y-3">
                            <!-- pledges table inserted here -->
                        </div>
                    </div>
                </div>
            </div>
<!-- Add Entry Modal -->
<div id="add-entry-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('add-entry-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-heading font-semibold text-royal-900">Add Sadaka Entry</h3>
                <button onclick="closeAddEntryModal()" class="text-mist-400 hover:text-mist-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="add-entry-form" class="space-y-4">
                <input type="hidden" id="edit-entry-id" value="">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-2">Member *</label>
                    <select id="entry-member-select" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300" required>
                        <option value="">Select a member...</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-mist-700 mb-2">Amount (TZS) *</label>
                        <input type="number" id="entry-amount" min="0" step="0.01" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-mist-700 mb-2">Date</label>
                        <input type="date" id="entry-date" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-2">Week (optional)</label>
                    <input type="number" id="entry-week" min="1" max="4" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300" placeholder="1-4">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-2">Notes</label>
                    <textarea id="entry-notes" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300 resize-none" rows="2" placeholder="Optional notes..."></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeAddEntryModal()" class="flex-1 px-4 py-2.5 border border-mist-200 text-mist-700 font-semibold rounded-xl hover:bg-mist-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white font-semibold rounded-xl transition-colors">
                        Save Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="upload-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('upload-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-heading font-semibold text-royal-900">Upload Sadaka Data</h3>
                <button onclick="closeModal('upload-modal')" class="text-mist-400 hover:text-mist-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="upload-form" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-2">Select File (CSV/Excel) *</label>
                    <div class="border-2 border-dashed border-mist-300 rounded-xl p-6 text-center cursor-pointer hover:border-royal-500 transition-colors"
                         onclick="document.getElementById('file-input').click()"
                         id="upload-drop-zone">
                        <svg class="w-10 h-10 text-mist-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p id="file-name" class="text-sm text-mist-600">Click to select or drag and drop CSV/Excel file</p>
                    </div>
                    <input type="file" id="file-input" class="hidden" accept=".csv,.xlsx,.xls" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-mist-700 mb-2">Month *</label>
                        <select id="upload-month" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300" required>
                            <option value="">Select month...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-mist-700 mb-2">Year *</label>
                        <input type="number" id="upload-year" min="2020" max="2099" value="<?= date('Y') ?>" class="w-full rounded-xl border border-mist-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-royal-300" required>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-mist-500 mb-2">CSV Format: member_code, first_name, last_name, amount, [date], [week], [notes]</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeModal('upload-modal')" class="flex-1 px-4 py-2.5 border border-mist-200 text-mist-700 font-semibold rounded-xl hover:bg-mist-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-glory-600 hover:bg-glory-700 text-white font-semibold rounded-xl transition-colors">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Member Entries Modal -->
<div id="manage-entries-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeModal('manage-entries-modal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 z-10">
            <div class="flex items-center justify-between mb-5 border-b border-mist-200 pb-3">
                <h3 id="manage-entries-modal-title" class="text-xl font-heading font-semibold text-royal-900">Manage Member Entries</h3>
                <button onclick="closeModal('manage-entries-modal')" class="text-mist-400 hover:text-mist-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-mist-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-mist-700">Date</th>
                            <th class="px-4 py-2 text-center font-semibold text-mist-700">Week</th>
                            <th class="px-4 py-2 text-right font-semibold text-mist-700">Amount</th>
                            <th class="px-4 py-2 text-left font-semibold text-mist-700">Notes</th>
                            <th class="px-4 py-2 text-center font-semibold text-mist-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="manage-entries-tbody" class="divide-y divide-mist-200">
                        <!-- Individual entries will be loaded here -->
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="closeModal('manage-entries-modal')" class="px-4 py-2 bg-mist-200 hover:bg-mist-300 text-mist-900 font-semibold rounded-xl transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// BASE_URL and CSRF_TOKEN are already defined in the layout
let currentCategory = '<?= htmlspecialchars($categorySlug) ?>';
let currentMonth = parseInt('<?= date('m') ?>');
let currentYear = parseInt('<?= date('Y') ?>');
let allMembers = [];
let isInitializing = true; // Track initialization state
let initErrors = [];
let sadakaData = {
    category_id: null,
    category_slug: '<?= htmlspecialchars($categorySlug) ?>',
    members: []
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', async function() {
    console.log('Initializing Sadaka module...');
    initErrors = [];
    isInitializing = true;
    populateMonths();
    populateYearsInUpload();
    
    try {
        await loadCategories();
        await loadMembers();
        await loadSadakaData();
        setupEventListeners();
        console.log('Sadaka module initialized');
    } catch (error) {
        console.error('Initialization error:', error);
        showErrorBanner('Failed to initialize Sadaka module: ' + error.message);
    } finally {
        isInitializing = false;
    }
});

// Show error banner to user
function showErrorBanner(message) {
    const banner = document.createElement('div');
    banner.className = 'mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm';
    banner.innerHTML = `<strong>Error:</strong> ${message}`;
    
    const mainContent = document.querySelector('main');
    if (mainContent) {
        mainContent.insertBefore(banner, mainContent.firstChild);
        setTimeout(() => banner.remove(), 5000);
    }
}

// Show success banner to user
function showSuccessBanner(message) {
    const banner = document.createElement('div');
    banner.className = 'mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm';
    banner.innerHTML = `<strong>Success:</strong> ${message}`;
    
    const mainContent = document.querySelector('main');
    if (mainContent) {
        mainContent.insertBefore(banner, mainContent.firstChild);
        setTimeout(() => banner.remove(), 3000);
    }
}

// Load categories and populate tabs
async function loadCategories() {
    try {
        console.log('Loading categories...');
        const response = await fetch(`${BASE_URL}/api/v1/sadaka/categories`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('Categories response:', result);

        if (result.success && result.data) {
            const container = document.getElementById('sadaka-categories');
            container.innerHTML = '';

            result.data.forEach(category => {
                const btn = document.createElement('button');
                btn.className = `sadaka-tab px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors ${
                    category.category_slug === currentCategory
                        ? 'border-royal-600 text-royal-700'
                        : 'border-transparent text-mist-500 hover:text-royal-700'
                }`;
                btn.textContent = category.category_name;
                btn.type = 'button';
                btn.onclick = (e) => {
                    e.preventDefault();
                    switchCategory(category.category_slug, category.id);
                };
                container.appendChild(btn);
            });
        } else {
            throw new Error(result.message || 'Failed to load categories');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        initErrors.push('Failed to load categories: ' + error.message);
        throw error;
    }
}

// Load all members for dropdown
async function loadMembers() {
    try {
        console.log('Loading members...');
        const response = await fetch(`${BASE_URL}/api/v1/members`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('Members response:', result);

        if (result.success && result.data) {
            allMembers = result.data;
            populateMemberSelect();
        } else {
            throw new Error(result.message || 'Failed to load members');
        }
    } catch (error) {
        console.error('Error loading members:', error);
        initErrors.push('Failed to load members: ' + error.message);
        // Don't throw - continue with empty members list
    }
}

// Populate member dropdown
function populateMemberSelect() {
    const select = document.getElementById('entry-member-select');
    if (!select) return;
    
    select.innerHTML = '<option value="">Select a member...</option>';
    allMembers.forEach(member => {
        const option = document.createElement('option');
        option.value = member.id;
        option.textContent = `${member.last_name}, ${member.first_name} (${member.member_code})`;
        select.appendChild(option);
    });
    console.log('Member select populated with', allMembers.length, 'members');
}

// Load sadaka data for current category, month, year
async function loadSadakaData() {
    const loading = document.getElementById('sadaka-loading');
    const container = document.getElementById('sadaka-table-container');
    const empty = document.getElementById('sadaka-empty');
    
    if (loading) loading.classList.remove('hidden');
    if (container) container.classList.add('hidden');
    if (empty) empty.classList.add('hidden');

    try {
        console.log(`Loading sadaka data for ${currentCategory}, month ${currentMonth}, year ${currentYear}`);
        const url = `${BASE_URL}/api/v1/sadaka/entries/${currentCategory}?month=${currentMonth}&year=${currentYear}`;
        console.log('Fetch URL:', url);
        
        const response = await fetch(url);
        const result = await response.json();
        console.log('Sadaka data response:', result);

        if (result.success && result.data) {
            sadakaData = result.data;
            renderTable();
        } else {
            console.error('Failed to load sadaka data:', result.message);
            if (empty) empty.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading sadaka data:', error);
        if (empty) empty.classList.remove('hidden');
    } finally {
        if (loading) loading.classList.add('hidden');
    }
}

function renderCategorySummary() {
    const summaryGrid = document.getElementById('sadaka-summary-grid');
    if (!summaryGrid) return;

    const members = sadakaData.members || [];
    const totalPaid = members.reduce((sum, member) => sum + parseFloat(member.month_total || 0), 0);
    const paidMembers = members.filter(member => parseFloat(member.month_total || 0) > 0).length;
    const allPaid = members.length > 0 && paidMembers === members.length;
    const categoryLabel = (sadakaData.category_slug || currentCategory || 'Category').replace(/[-_]/g, ' ').replace(/\b\w/g, ch => ch.toUpperCase());

    summaryGrid.innerHTML = `
        <div class="bg-white border border-mist-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-mist-500">Time limit</p>
            <p class="mt-2 text-lg font-bold text-royal-800">${getMonthName(currentMonth)} ${currentYear}</p>
            <p class="mt-1 text-xs text-mist-500">${categoryLabel}</p>
        </div>
        <div class="bg-white border border-mist-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-mist-500">Amount paid</p>
            <p class="mt-2 text-lg font-bold text-emerald-700">TZS ${totalPaid.toLocaleString('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
            <p class="mt-1 text-xs text-mist-500">Current month contribution</p>
        </div>
        <div class="bg-white border border-mist-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-mist-500">Status</p>
            <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${allPaid ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">
                ${allPaid ? 'Completed' : 'Not completed'}
            </span>
            <p class="mt-2 text-xs text-mist-500">${members.length ? paidMembers + ' of ' + members.length + ' members paid' : 'No members in this category yet'}</p>
        </div>
        <div class="bg-white border border-mist-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-mist-500">Extra</p>
            <p class="mt-2 text-lg font-bold text-royal-800">${members.length ? paidMembers : 0}</p>
            <p class="mt-1 text-xs text-mist-500">Members have contributed this period</p>
        </div>
    `;
}

function isPledgeLikeCategory(categorySlugValue) {
    const slug = String(categorySlugValue || '').toLowerCase();
    return /(ahadi|michango|machango|mchango|pledge|contribution)/i.test(slug);
}

// Render members table with week data
function renderTable() {
    const members = sadakaData.members || [];
    const tbody = document.getElementById('sadaka-tbody');
    const container = document.getElementById('sadaka-table-container');
    const empty = document.getElementById('sadaka-empty');
    const headerRow = document.getElementById('sadaka-header-row');
    const monthSelect = document.getElementById('sadaka-month-select');
    const isPledgeView = isPledgeLikeCategory(currentCategory || sadakaData.category_slug || '');

    if (monthSelect) {
        monthSelect.style.display = isPledgeView ? 'none' : '';
    }

    renderCategorySummary();

    if (members.length === 0) {
        container.classList.add('hidden');
        empty.classList.remove('hidden');
        return;
    }

    container.classList.remove('hidden');
    empty.classList.add('hidden');

    if (isPledgeView) {
        headerRow.innerHTML = `
            <th class="px-6 py-3 text-left text-xs font-semibold text-mist-700 uppercase">Name</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-mist-700 uppercase">Ahadi</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-mist-700 uppercase">Paid</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-mist-700 uppercase">Extra</th>
        `;

        tbody.innerHTML = members.map(member => {
            const pledgeAmount = parseFloat(member.month_total || 0);
            const paidAmount = parseFloat(member.yearly_total || 0);
            const remaining = Math.max(pledgeAmount - paidAmount, 0);
            const extra = member.entries && member.entries.length
                ? `${member.entries.length} entry${member.entries.length > 1 ? 'ies' : 'y'}${remaining > 0 ? ' · remaining ' + remaining.toLocaleString('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : ' · completed'}`
                : 'No entries';

            return `
                <tr class="hover:bg-mist-50 transition-colors border-b border-mist-200">
                    <td class="px-6 py-3 text-sm font-medium">
                        <span class="text-royal-900 font-semibold">${member.last_name}, ${member.first_name}</span>
                        <br><span class="text-xs text-mist-500">${member.member_code}</span>
                    </td>
                    <td class="px-6 py-3 text-sm font-semibold text-royal-800">TZS ${pledgeAmount.toLocaleString('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td class="px-6 py-3 text-sm font-semibold ${paidAmount > 0 ? 'text-emerald-700' : 'text-mist-400'}">TZS ${paidAmount.toLocaleString('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td class="px-6 py-3 text-sm text-mist-600">${extra}</td>
                </tr>
            `;
        }).join('');
        return;
    }

    // Determine number of weeks to display (max 4)
    let maxWeeks = 4;
    members.forEach(member => {
        if (member.week_data && member.week_data.length > maxWeeks) {
            maxWeeks = member.week_data.length;
        }
    });

    // Rebuild header row with week columns
    // Keep first cell (Member), add week headers, then add totals and actions
    let headerHTML = '<th class="px-6 py-3 text-left text-xs font-semibold text-mist-700 uppercase">Member</th>';
    
    // Add week headers
    for (let i = 1; i <= maxWeeks; i++) {
        headerHTML += `<th class="px-4 py-3 text-center text-xs font-semibold text-mist-700 uppercase bg-blue-50">Week ${i}</th>`;
    }
    
    // Add total columns
    headerHTML += '<th class="px-6 py-3 text-center text-xs font-semibold text-mist-700 uppercase">Monthly Total</th>';
    headerHTML += '<th class="px-6 py-3 text-center text-xs font-semibold text-royal-700 uppercase bg-royal-50">Yearly Total</th>';
    headerHTML += '<th class="px-6 py-3 text-center text-xs font-semibold text-mist-700 uppercase">Actions</th>';
    
    headerRow.innerHTML = headerHTML;

    // Build member rows
    tbody.innerHTML = members.map(member => {
        let weekCellsHTML = '';
        for (let i = 1; i <= maxWeeks; i++) {
            const weekData = member.week_data.find(w => parseInt(w.entry_week) === i);
            const amount = weekData ? parseFloat(weekData.week_total).toLocaleString('en-TZ', {minimumFractionDigits: 2}) : '-';
            // Highlight week cells with a light blue background
            weekCellsHTML += `<td class="px-4 py-3 text-center text-sm font-semibold text-royal-700 bg-blue-50">${amount}</td>`;
        }

        return `
            <tr class="hover:bg-mist-50 transition-colors border-b border-mist-200">
                <td class="px-6 py-3 text-sm font-medium">
                    <span class="text-royal-900 font-semibold">${member.last_name}, ${member.first_name}</span>
                    <br><span class="text-xs text-mist-500">${member.member_code}</span>
                </td>
                ${weekCellsHTML}
                <td class="px-6 py-3 text-center text-sm font-semibold text-mist-900">
                    ${parseFloat(member.month_total || 0).toLocaleString('en-TZ', {minimumFractionDigits: 2})}
                </td>
                <td class="px-6 py-3 text-center text-sm font-bold text-royal-700 bg-royal-50">
                    ${parseFloat(member.yearly_total || 0).toLocaleString('en-TZ', {minimumFractionDigits: 2})}
                </td>
                <td class="px-6 py-3 text-center space-x-2">
                    <button onclick="manageMemberEntries(${member.id})" class="inline-block px-2 py-1 text-royal-600 hover:text-royal-800 hover:bg-royal-100 rounded transition" title="Edit">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M4 20l7.536-1.5L20 9.036 14.964 4 4 20z"/></svg>
                        Edit
                    </button>
                    <button onclick="manageMemberEntries(${member.id})" class="inline-block px-2 py-1 text-red-600 hover:text-red-800 hover:bg-red-100 rounded transition" title="Delete">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Delete
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// Switch category
function switchCategory(slug, categoryId) {
    console.log('Switching to category:', slug);
    currentCategory = slug;
    window.history.replaceState({}, '', `${BASE_URL}/sadaka?category=${slug}`);
    
    // Update tab styles
    document.querySelectorAll('.sadaka-tab').forEach(tab => {
        tab.classList.remove('border-royal-600', 'text-royal-700');
        tab.classList.add('border-transparent', 'text-mist-500');
    });
    
    // Find and highlight the active tab
    const activeTab = Array.from(document.querySelectorAll('.sadaka-tab')).find(
        tab => tab.textContent.toLowerCase().includes(slug.split('-').join(' '))
    );
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-mist-500');
        activeTab.classList.add('border-royal-600', 'text-royal-700');
    }
    
    loadSadakaData();
}

// Populate months dropdown
function populateMonths() {
    const select = document.getElementById('sadaka-month-select');
    const uploadSelect = document.getElementById('upload-month');
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
    
    if (select) {
        select.innerHTML = '';
        monthNames.forEach((name, index) => {
            const option = document.createElement('option');
            option.value = index + 1;
            option.textContent = name;
            if (index + 1 === currentMonth) option.selected = true;
            select.appendChild(option);
        });
        console.log('Main month select populated');
    }

    if (uploadSelect) {
        uploadSelect.innerHTML = '';
        monthNames.forEach((name, index) => {
            const option = document.createElement('option');
            option.value = index + 1;
            option.textContent = name;
            if (index + 1 === currentMonth) option.selected = true;
            uploadSelect.appendChild(option);
        });
        console.log('Upload month select populated');
    }
}

function populateYearsInUpload() {
    const yearInput = document.getElementById('upload-year');
    if (yearInput) {
        yearInput.value = currentYear;
        console.log('Year input set to:', currentYear);
    }
}

// Setup event listeners
function setupEventListeners() {
    // Month selector
    const monthSelect = document.getElementById('sadaka-month-select');
    if (monthSelect) {
        monthSelect.addEventListener('change', (e) => {
            currentMonth = parseInt(e.target.value);
            console.log('Month changed to:', currentMonth);
            loadSadakaData();
        });
    }

    // Add entry form
    const addForm = document.getElementById('add-entry-form');
    if (addForm) {
        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Add entry form submitted');
            await addEntry();
        });
    }

    // Upload form
    const uploadForm = document.getElementById('upload-form');
    if (uploadForm) {
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Upload form submitted');
            await uploadFile();
        });
    }

    // File drag and drop
    const dropZone = document.getElementById('upload-drop-zone');
    if (dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        dropZone.addEventListener('dragover', () => dropZone.classList.add('border-royal-500'));
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-royal-500'));

        dropZone.addEventListener('drop', (e) => {
            dropZone.classList.remove('border-royal-500');
            const files = e.dataTransfer.files;
            const fileInput = document.getElementById('file-input');
            if (files.length && fileInput) {
                fileInput.files = files;
                updateFileName();
            }
        });
    }

    const fileInput = document.getElementById('file-input');
    if (fileInput) {
        fileInput.addEventListener('change', updateFileName);
    }

    console.log('Event listeners setup complete');
}

function updateFileName() {
    const file = document.getElementById('file-input').files[0];
    const nameDisplay = document.getElementById('file-name');
    if (file && nameDisplay) {
        nameDisplay.textContent = file.name;
        console.log('File selected:', file.name);
    }
}

// Add entry with validation
async function addEntry() {
    const memberSelect = document.getElementById('entry-member-select');
    const amountInput = document.getElementById('entry-amount');
    const dateInput = document.getElementById('entry-date');
    const weekInput = document.getElementById('entry-week');
    const notesInput = document.getElementById('entry-notes');

    const memberId = memberSelect.value.trim();
    const amountStr = amountInput.value.trim();
    const date = dateInput.value;
    const weekStr = weekInput.value.trim();
    const notes = notesInput.value.trim();

    // Validate initialization
    if (isInitializing) {
        showErrorBanner('Still loading category data. Please wait...');
        return;
    }

    // Validate required fields
    if (!memberId) {
        showErrorBanner('Please select a member');
        return;
    }

    if (!amountStr) {
        showErrorBanner('Please enter an amount');
        return;
    }

    // Validate amount
    const amount = parseFloat(amountStr);
    if (isNaN(amount)) {
        showErrorBanner('Amount must be a valid number');
        return;
    }
    if (amount <= 0) {
        showErrorBanner('Amount must be greater than 0');
        return;
    }
    if (amount > 9999999.99) {
        showErrorBanner('Amount exceeds maximum allowed value (9,999,999.99)');
        return;
    }

    // Validate date if provided
    if (date && isNaN(new Date(date).getTime())) {
        showErrorBanner('Invalid date format');
        return;
    }

    // Validate week if provided
    let week = null;
    if (weekStr) {
        week = parseInt(weekStr);
        if (isNaN(week) || week < 1 || week > 4) {
            showErrorBanner('Week must be between 1 and 4');
            return;
        }
    }

    if (!sadakaData.category_id) {
        showErrorBanner('Please load a category first');
        return;
    }

    const editEntryId = document.getElementById('edit-entry-id').value;
    const isEdit = !!editEntryId;
    const url = isEdit ? `${BASE_URL}/api/v1/sadaka/entries/${editEntryId}` : `${BASE_URL}/api/v1/sadaka/entries`;
    const method = isEdit ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                member_id: parseInt(memberId),
                category_id: sadakaData.category_id,
                amount: amount,
                entry_date: date,
                week: week,
                notes: notes
            })
        });

        const result = await response.json();
        console.log('Save entry response:', result);

        if (result.success) {
            showSuccessBanner(isEdit ? 'Entry updated successfully!' : 'Entry added successfully!');
            closeAddEntryModal();
            await loadSadakaData();
        } else {
            showErrorBanner(result.message || 'Failed to save entry');
        }
    } catch (error) {
        console.error('Error saving entry:', error);
        showErrorBanner('Error saving entry: ' + error.message);
    }
}

// Upload file with validation
async function uploadFile() {
    const file = document.getElementById('file-input').files[0];
    const month = document.getElementById('upload-month').value;
    const year = document.getElementById('upload-year').value;

    console.log('Uploading file:', {file: file?.name, month, year});

    // Validate initialization
    if (isInitializing) {
        showErrorBanner('Still loading category data. Please wait...');
        return;
    }

    if (!file) {
        showErrorBanner('Please select a file to upload');
        return;
    }

    if (!month) {
        showErrorBanner('Please select a month');
        return;
    }

    if (!year) {
        showErrorBanner('Please enter a year');
        return;
    }

    if (!sadakaData.category_id) {
        showErrorBanner('Please load a category first');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('category_id', sadakaData.category_id);
    formData.append('month', month);
    formData.append('year', year);

    try {
        const response = await fetch(`${BASE_URL}/api/v1/sadaka/upload`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: formData
        });

        const result = await response.json();
        console.log('Upload response:', result);

        if (result.success) {
            const msg = `Upload successful! ${result.data.successful} entries added`;
            if (result.data.failed > 0) {
                showErrorBanner(msg + `, ${result.data.failed} failed. Check console for details.`);
            } else {
                showSuccessBanner(msg);
            }
            closeModal('upload-modal');
            document.getElementById('upload-form').reset();
            document.getElementById('file-name').textContent = 'Click to select or drag and drop CSV file';
            await loadSadakaData();
        } else {
            showErrorBanner(result.message || 'Upload failed');
        }
    } catch (error) {
        console.error('Error uploading:', error);
        showErrorBanner('Error uploading file: ' + error.message);
    }
}

// Generate report for current month/year
function generateReport() {
    const month = currentMonth || new Date().getMonth() + 1;
    const year = currentYear || new Date().getFullYear();
    
    showSuccessBanner(`Generating report for ${getMonthName(month)} ${year}...`);
    loadReportData(month, year);
}

// View generated report
function viewReport() {
    const month = currentMonth || new Date().getMonth() + 1;
    const year = currentYear || new Date().getFullYear();
    
    loadReportData(month, year);
}

// Load report data from API
async function loadReportData(month, year) {
    try {
        const response = await fetch(`${BASE_URL}/api/v1/sadaka/report/${year}/${month}`, {
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayReport(result.data, month, year);
            openModal('report-modal');
        } else {
            showErrorBanner(result.message || 'Failed to load report');
        }
    } catch (error) {
        showErrorBanner('Error loading report: ' + error.message);
    }
}

// Display report in modal
function displayReport(reportData, month, year) {
    const monthName = getMonthName(month);
    const reportContent = document.getElementById('report-content');
    
    let html = `
        <div class="bg-royal-50 border border-royal-200 rounded-lg p-3 mb-4">
            <h4 class="text-base font-semibold text-royal-900 mb-3">Report Summary</h4>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white p-2.5 rounded border border-mist-200">
                    <p class="text-xs text-mist-600 uppercase font-semibold">Month/Year</p>
                    <p class="text-sm font-bold text-royal-900 mt-1">${monthName} ${year}</p>
                </div>
                <div class="bg-white p-2.5 rounded border border-mist-200">
                    <p class="text-xs text-mist-600 uppercase font-semibold">Total Members</p>
                    <p class="text-sm font-bold text-royal-900 mt-1">${reportData.totalMembers}</p>
                </div>
                <div class="bg-white p-2.5 rounded border border-mist-200">
                    <p class="text-xs text-mist-600 uppercase font-semibold">Total Entries</p>
                    <p class="text-sm font-bold text-royal-900 mt-1">${reportData.totalEntries}</p>
                </div>
                <div class="bg-white p-2.5 rounded border border-mist-200">
                    <p class="text-xs text-mist-600 uppercase font-semibold">Total Amount</p>
                    <p class="text-sm font-bold text-green-700 mt-1">${parseFloat(reportData.totalAmount || 0).toLocaleString('en-TZ', {minimumFractionDigits: 2})}</p>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-mist-100 border-b border-mist-200 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-mist-700 text-xs">Member Code</th>
                        <th class="px-3 py-2 text-left font-semibold text-mist-700 text-xs">Member Name</th>
                        <th class="px-2 py-2 text-center font-semibold text-mist-700 text-xs">Wk 1</th>
                        <th class="px-2 py-2 text-center font-semibold text-mist-700 text-xs">Wk 2</th>
                        <th class="px-2 py-2 text-center font-semibold text-mist-700 text-xs">Wk 3</th>
                        <th class="px-2 py-2 text-center font-semibold text-mist-700 text-xs">Wk 4</th>
                        <th class="px-3 py-2 text-center font-semibold text-royal-700 bg-royal-50 text-xs">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-mist-200">
    `;
    
    if (reportData.members && reportData.members.length > 0) {
        reportData.members.forEach(member => {
            const week1 = (member.week_data.find(w => parseInt(w.entry_week) === 1)?.week_total || 0);
            const week2 = (member.week_data.find(w => parseInt(w.entry_week) === 2)?.week_total || 0);
            const week3 = (member.week_data.find(w => parseInt(w.entry_week) === 3)?.week_total || 0);
            const week4 = (member.week_data.find(w => parseInt(w.entry_week) === 4)?.week_total || 0);
            const total = parseFloat(member.month_total || 0);
            
            html += `
                <tr class="hover:bg-mist-50 transition-colors">
                    <td class="px-3 py-1.5 text-mist-700 font-mono text-xs">${member.member_code}</td>
                    <td class="px-3 py-1.5 text-mist-900 font-medium text-xs">${member.last_name}, ${member.first_name}</td>
                    <td class="px-2 py-1.5 text-center text-mist-700 text-xs">${week1 > 0 ? parseFloat(week1).toLocaleString('en-TZ', {minimumFractionDigits: 2}) : '-'}</td>
                    <td class="px-2 py-1.5 text-center text-mist-700 text-xs">${week2 > 0 ? parseFloat(week2).toLocaleString('en-TZ', {minimumFractionDigits: 2}) : '-'}</td>
                    <td class="px-2 py-1.5 text-center text-mist-700 text-xs">${week3 > 0 ? parseFloat(week3).toLocaleString('en-TZ', {minimumFractionDigits: 2}) : '-'}</td>
                    <td class="px-2 py-1.5 text-center text-mist-700 text-xs">${week4 > 0 ? parseFloat(week4).toLocaleString('en-TZ', {minimumFractionDigits: 2}) : '-'}</td>
                    <td class="px-3 py-1.5 text-center text-royal-900 font-bold bg-royal-50 text-xs">${parseFloat(total).toLocaleString('en-TZ', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        });
    }
    
    html += `
                </tbody>
                <tfoot class="bg-royal-50 border-t-2 border-royal-300">
                    <tr class="font-bold">
                        <td colspan="2" class="px-3 py-2 text-right text-royal-900 text-xs">Grand Total:</td>
                        <td class="px-2 py-2 text-center text-royal-900 text-xs">-</td>
                        <td class="px-2 py-2 text-center text-royal-900 text-xs">-</td>
                        <td class="px-2 py-2 text-center text-royal-900 text-xs">-</td>
                        <td class="px-2 py-2 text-center text-royal-900 text-xs">-</td>
                        <td class="px-3 py-2 text-center text-royal-900 font-bold text-sm">${parseFloat(reportData.totalAmount || 0).toLocaleString('en-TZ', {minimumFractionDigits: 2})}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    
    reportContent.innerHTML = html;
}

// Export report as CSV
function exportReportCSV() {
    const month = currentMonth || new Date().getMonth() + 1;
    const year = currentYear || new Date().getFullYear();
    const monthName = getMonthName(month);
    
    // Get current report data from DOM
    const table = document.querySelector('#report-modal table');
    if (!table) {
        showErrorBanner('No report data to export');
        return;
    }
    
    let csv = `Sadaka Report - ${monthName} ${year}\n\n`;
    csv += 'Member Code,Member Name,Week 1,Week 2,Week 3,Week 4,Total\n';
    
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = Array.from(cells).map(cell => `"${cell.textContent.trim()}"`).join(',');
        csv += rowData + '\n';
    });
    
    csv += `\nGrand Total,,,,,,"${table.querySelector('tfoot td:last-child').textContent.trim()}"\n`;
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `sadaka-report-${year}-${String(month).padStart(2, '0')}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showSuccessBanner('Report exported as CSV');
}

// -- Pledge tracker helpers --
function togglePledgeButtons(isPledgeView) {
    const regBtn = document.getElementById('register-pledge-btn');
    const viewBtn = document.getElementById('view-pledges-btn');
    if (regBtn) regBtn.style.display = isPledgeView ? '' : 'none';
    if (viewBtn) viewBtn.style.display = isPledgeView ? '' : 'none';
}

function openPledgeModal() {
    document.getElementById('pledge-form').reset();
    document.getElementById('pledge-id').value = '';
    openModal('pledge-modal');
}

async function loadPledges() {
    const container = document.getElementById('pledges-list-content');
    if (!container) return;
    container.innerHTML = '<div class="p-4 text-center text-mist-500">Loading pledges...</div>';

    try {
        const url = `${BASE_URL}/api/v1/sadaka/pledges?category=${encodeURIComponent(currentCategory)}`;
        const res = await fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        const result = await res.json();
        if (result.success && result.data) {
            renderPledgesList(result.data);
        } else {
            container.innerHTML = `<div class="p-4 text-center text-mist-500">${result.message || 'No pledges found'}</div>`;
        }
    } catch (err) {
        console.error('Error loading pledges:', err);
        container.innerHTML = `<div class="p-4 text-center text-red-600">Error loading pledges</div>`;
    }
}

function renderPledgesList(pledges) {
    const container = document.getElementById('pledges-list-content');
    if (!container) return;

    if (!pledges.length) {
        container.innerHTML = '<div class="p-4 text-center text-mist-500">No pledges registered yet.</div>';
        return;
    }

    let html = `
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-mist-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-mist-700">Pledge Name</th>
                        <th class="px-3 py-2 text-center font-semibold text-mist-700">Months</th>
                        <th class="px-3 py-2 text-right font-semibold text-mist-700">Amount (TZS)</th>
                        <th class="px-3 py-2 text-left font-semibold text-mist-700">Notes</th>
                        <th class="px-3 py-2 text-center font-semibold text-mist-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-mist-200">
    `;

    pledges.forEach(p => {
        html += `
            <tr class="hover:bg-mist-50 transition-colors">
                <td class="px-3 py-2 text-mist-900 font-medium">${escapeHtml(p.name || p.pledge_name || '')}</td>
                <td class="px-3 py-2 text-center text-mist-700">${parseInt(p.months || p.time_limit_months || 0)}</td>
                <td class="px-3 py-2 text-right text-royal-900 font-semibold">${parseFloat(p.amount || 0).toLocaleString('en-TZ', {minimumFractionDigits: 2})}</td>
                <td class="px-3 py-2 text-mist-600">${escapeHtml(p.notes || '')}</td>
                <td class="px-3 py-2 text-center space-x-2">
                    <button onclick="viewPledgeDetails(${p.id})" class="px-2 py-1 bg-royal-600 text-white rounded text-xs">Details</button>
                    <button onclick="deletePledge(${p.id})" class="px-2 py-1 bg-red-600 text-white rounded text-xs">Delete</button>
                </td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;
    container.innerHTML = html;
}

async function viewPledgeDetails(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/v1/sadaka/pledges/${id}`, { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        const result = await res.json();
        if (!result.success) {
            showErrorBanner(result.message || 'Failed to load pledge details');
            return;
        }

        const p = result.data;
        const content = document.getElementById('pledges-list-content');
        content.innerHTML = `
            <div class="bg-white border border-mist-200 rounded-lg p-4 mb-4">
                <h4 class="text-lg font-semibold text-royal-900">${escapeHtml(p.name)}</h4>
                <p class="text-sm text-mist-600 mt-1">Time limit: <strong>${parseInt(p.months || p.time_limit_months || 0)} months</strong></p>
                <p class="text-sm text-mist-600 mt-1">Amount: <strong>TZS ${parseFloat(p.amount || 0).toLocaleString('en-TZ', {minimumFractionDigits:2})}</strong></p>
                <p class="text-sm text-mist-600 mt-1">Notes: ${escapeHtml(p.notes || '-')}</p>
                <p class="text-sm text-mist-500 mt-2">Registered: ${p.created_at || p.registered_at || '-'}</p>
            </div>
            <div class="mb-4">
                <button onclick="loadPledges()" class="px-3 py-2 bg-royal-600 text-white rounded">Back to list</button>
            </div>
        `;
    } catch (err) {
        console.error('Error loading pledge details:', err);
        showErrorBanner('Error loading pledge details');
    }
}

async function deletePledge(id) {
    if (!confirm('Are you sure you want to delete this pledge?')) return;
    try {
        const res = await fetch(`${BASE_URL}/api/v1/sadaka/pledges/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        const result = await res.json();
        if (result.success) {
            showSuccessBanner('Pledge deleted');
            await loadPledges();
        } else {
            showErrorBanner(result.message || 'Failed to delete pledge');
        }
    } catch (err) {
        console.error('Error deleting pledge:', err);
        showErrorBanner('Error deleting pledge');
    }
}

function exportPledgesCSV() {
    const table = document.querySelector('#pledges-list-content table');
    if (!table) { showErrorBanner('No pledges to export'); return; }
    let csv = 'Pledge Name,Months,Amount,Notes\n';
    table.querySelectorAll('tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = [cells[0].textContent.trim(), cells[1].textContent.trim(), cells[2].textContent.trim(), '"'+cells[3].textContent.trim()+'"'].join(',');
        csv += rowData + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `pledges-${new Date().toISOString().slice(0,10)}.csv`;
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    showSuccessBanner('Pledges exported');
}

// Simple HTML escaper
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>\"']/g, function (s) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[s];
    });
}

// Get month name from number
function getMonthName(monthNum) {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    return monthNames[monthNum - 1] || 'Unknown';
}

// Open/Close and reset helpers
function closeAddEntryModal() {
    closeModal('add-entry-modal');
    resetAddEntryForm();
}

function resetAddEntryForm() {
    document.getElementById('add-entry-form').reset();
    document.getElementById('edit-entry-id').value = '';
    document.getElementById('entry-date').value = new Date().toISOString().split('T')[0];
    const modalTitle = document.querySelector('#add-entry-modal .text-xl');
    if (modalTitle) {
        modalTitle.textContent = 'Add Sadaka Entry';
    }
}

// Manage Member Entries
function manageMemberEntries(memberId) {
    const member = sadakaData.members.find(m => m.id == memberId);
    if (!member) {
        showErrorBanner('Member not found');
        return;
    }

    const title = document.getElementById('manage-entries-modal-title');
    title.textContent = `Entries for ${member.first_name} ${member.last_name} (${member.member_code})`;

    const tbody = document.getElementById('manage-entries-tbody');
    const entries = member.entries || [];

    if (entries.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-mist-500">No entries found for this member in this month.</td></tr>';
    } else {
        tbody.innerHTML = entries.map(entry => `
            <tr class="hover:bg-mist-50 border-b border-mist-100 last:border-b-0">
                <td class="px-4 py-2.5 text-sm">${entry.entry_date}</td>
                <td class="px-4 py-2.5 text-sm text-center font-semibold text-royal-700 bg-blue-50/50">${entry.entry_week || '-'}</td>
                <td class="px-4 py-2.5 text-sm text-right font-semibold text-royal-700">${parseFloat(entry.amount).toLocaleString('en-TZ', {minimumFractionDigits: 2})}</td>
                <td class="px-4 py-2.5 text-sm text-mist-600 max-w-xs truncate" title="${entry.notes || ''}">${entry.notes || '-'}</td>
                <td class="px-4 py-2.5 text-sm text-center space-x-2">
                    <button onclick="editSpecificEntry(${memberId}, ${entry.id})" class="text-royal-600 hover:text-royal-800 font-semibold">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M4 20l7.536-1.5L20 9.036 14.964 4 4 20z"/></svg>
                        Edit
                    </button>
                    <button onclick="deleteSpecificEntry(${entry.id})" class="text-red-600 hover:text-red-800 font-semibold">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Delete
                    </button>
                </td>
            </tr>
        `).join('');
    }

    openModal('manage-entries-modal');
}

// Edit specific entry
function editSpecificEntry(memberId, entryId) {
    const member = sadakaData.members.find(m => m.id == memberId);
    const entry = member.entries.find(e => e.id == entryId);
    if (!member || !entry) {
        showErrorBanner('Entry or Member not found');
        return;
    }

    closeModal('manage-entries-modal');

    // Pre-fill the form
    document.getElementById('edit-entry-id').value = entryId;
    document.getElementById('entry-member-select').value = memberId;
    document.getElementById('entry-amount').value = entry.amount;
    document.getElementById('entry-date').value = entry.entry_date;
    document.getElementById('entry-week').value = entry.entry_week || '';
    document.getElementById('entry-notes').value = entry.notes || '';

    // Update modal title
    const modalTitle = document.querySelector('#add-entry-modal .text-xl');
    if (modalTitle) {
        modalTitle.textContent = `Edit Sadaka Entry - ${member.first_name} ${member.last_name}`;
    }

    openModal('add-entry-modal');
}

// Delete specific entry
async function deleteSpecificEntry(entryId) {
    if (!confirm('Are you sure you want to delete this specific entry? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`${BASE_URL}/api/v1/sadaka/entries/${entryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        const result = await response.json();
        console.log('Delete response:', result);

        if (result.success) {
            showSuccessBanner('Entry deleted successfully');
            closeModal('manage-entries-modal');
            await loadSadakaData();
        } else {
            showErrorBanner(result.message || 'Failed to delete entry');
        }
    } catch (error) {
        console.error('Error deleting entry:', error);
        showErrorBanner('Error deleting entry: ' + error.message);
    }
}

// Modal helpers
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        console.log('Opened modal:', modalId);
    } else {
        console.error('Modal not found:', modalId);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        console.log('Closed modal:', modalId);
    }
}

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
        const modal = e.target.closest('div[id$="-modal"]');
        if (modal) {
            closeModal(modal.id);
        }
    }
});
</script>

<style>
#sadaka-table-container table {
    table-layout: auto;
}
</style>
