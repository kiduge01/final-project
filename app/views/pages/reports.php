<div class="mb-6">
    <h1 class="text-3xl font-heading font-semibold text-royal-900">Reports</h1>
    <p class="text-mist-600 text-sm mt-1">Centralized reports for members, guests, attendance, giving and assets.</p>
</div>
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6" id="report-kpis"></div>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-4"><h2 class="font-semibold text-mist-900">Administrative Summary</h2><button onclick="window.print()" class="px-4 py-2 rounded-xl bg-mist-900 text-white text-sm">Print</button></div>
    <div id="report-summary" class="text-sm leading-7 text-mist-700">Loading report...</div>
</div>
<script>
async function loadReports(){const r=await fetch(`${BASE_URL}/api/v1/dashboard/stats`);const j=await r.json();const d=j.data||{};const cards=[['Members',d.members||0],['Guests',d.guests||0],['Attendance',d.attendance||0],['Giving','TZS '+Number(d.income||0).toLocaleString()],['Expenses','TZS '+Number(d.expenses||0).toLocaleString()]];document.getElementById('report-kpis').innerHTML=cards.map(c=>`<div class="bg-white rounded-2xl border border-mist-200 p-4"><p class="text-xs text-mist-500">${c[0]}</p><p class="text-xl font-bold text-royal-800 mt-1">${c[1]}</p></div>`).join('');document.getElementById('report-summary').textContent=`Current administrative snapshot: ${d.members||0} active members, ${d.guests||0} registered guests, ${d.attendance||0} recorded attendance for the current month, TZS ${Number(d.income||0).toLocaleString()} recorded income/giving and TZS ${Number(d.expenses||0).toLocaleString()} recorded expenses for the current month.`;}loadReports();
</script>
