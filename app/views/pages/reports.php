<div class="ui-fade-in" id="reports-app">
  <div class="ui-page-head">
    <div><h1 class="ui-title">Reports Center</h1><p class="ui-subtitle">Create time-based church reports, compare them with past records, add preparer notes and download a real PDF file.</p></div>
    <div class="flex gap-2 no-print"><button class="ui-btn ui-btn-secondary" id="refresh-report">Refresh</button><button class="ui-btn ui-btn-primary" id="download-pdf">Download PDF</button></div>
  </div>

  <section class="ui-card mb-5 no-print">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
      <div><label class="ui-label">Period</label><select class="ui-select" id="period"><option value="this_week">This Week</option><option value="last_week">Last Week</option><option value="this_month" selected>This Month</option><option value="last_month">Last Month</option><option value="this_quarter">This Quarter</option><option value="last_quarter">Last Quarter</option><option value="this_year">This Year</option><option value="custom">Custom Range</option></select></div>
      <div><label class="ui-label">From</label><input class="ui-input" type="date" id="from"></div>
      <div><label class="ui-label">To</label><input class="ui-input" type="date" id="to"></div>
      <div><label class="ui-label">Report Type</label><select class="ui-select" id="report-type"><option>Overview</option><option>Attendance / Ibada</option><option>Members</option><option>Guests</option><option>Church Giving</option><option>Assets</option></select></div>
      <div class="flex items-end"><button class="ui-btn ui-btn-secondary w-full" id="apply-filter">Apply Filters</button></div>
    </div>
  </section>

  <div class="ui-kpis" id="report-kpis">
    <?php for($i=0;$i<4;$i++): ?><div class="ui-card ui-kpi"><div class="ui-skeleton h-4 w-24"></div><div class="ui-skeleton h-8 w-32 mt-4"></div></div><?php endfor; ?>
  </div>

  <div class="ui-grid-2">
    <section class="ui-card">
      <div class="flex items-start justify-between gap-3"><div><h2 class="ui-section-title">Trend comparison</h2><p class="ui-section-sub" id="period-label">Selected period compared with the immediately preceding equal period.</p></div><span class="ui-pill ui-pill-green">AI assisted</span></div>
      <div id="comparison-bars" class="mt-6 space-y-4"></div>
      <div class="mt-5 rounded-xl border border-indigo-100 bg-indigo-50 p-4">
        <div class="text-xs font-bold uppercase tracking-wide text-indigo-700">AI trend interpretation</div>
        <div id="report-summary" class="mt-2 text-sm leading-7 text-slate-700">Loading current and historical records…</div>
        <div class="mt-2 text-[11px] text-slate-500">The AI receives calculated current/previous values from the report service. It does not calculate or invent the source figures itself.</div>
      </div>
    </section>

    <aside class="ui-card">
      <h2 class="ui-section-title">Report description</h2><p class="ui-section-sub">Optional human-authored context or remarks to include in the downloaded report.</p>
      <textarea id="report-description" maxlength="1800" class="ui-textarea mt-4 min-h-[170px]" placeholder="Example: Attendance improved during the final two Sundays because of the special worship program…"></textarea>
      <div class="mt-4 text-xs text-slate-500">Prepared by</div><div class="font-semibold text-sm mt-1"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Administrator') ?></div>
      <div class="mt-5 rounded-xl bg-slate-50 border border-slate-200 p-3 text-xs leading-5 text-slate-600">Your description remains clearly separated from the AI-assisted trend analysis in the PDF.</div>
    </aside>
  </div>

  <section class="ui-card mt-5">
    <div class="flex items-center justify-between"><div><h2 class="ui-section-title">Comparison details</h2><p class="ui-section-sub">Current period versus the previous period of equal duration.</p></div><span class="ui-pill" id="comparison-range">—</span></div>
    <div class="overflow-x-auto mt-4"><table class="ui-table"><thead><tr><th>Measure</th><th>Current</th><th>Previous</th><th>Difference</th><th>Change</th></tr></thead><tbody id="comparison-rows"><tr><td colspan="5">Loading…</td></tr></tbody></table></div>
  </section>

  <section class="ui-card mt-5">
    <div class="flex items-center justify-between"><div><h2 class="ui-section-title">Attendance / Ibada details</h2><p class="ui-section-sub">Records from registered Events / Ibada in the selected period.</p></div><span class="ui-pill ui-pill-green" id="report-status">Ready</span></div>
    <div class="overflow-x-auto mt-4"><table class="ui-table"><thead><tr><th>Ibada / Event</th><th>Date</th><th>Type</th><th>Men</th><th>Women</th><th>Children</th><th>Youth</th><th>Guests</th><th>Total</th></tr></thead><tbody id="attendance-rows"><tr><td colspan="9">Loading…</td></tr></tbody></table></div>
  </section>
</div>

<script>
const pad=n=>String(n).padStart(2,'0'),fmt=d=>`${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
const from=document.getElementById('from'),to=document.getElementById('to'),period=document.getElementById('period');
function rangeFor(v){const n=new Date(),d=new Date(n.getFullYear(),n.getMonth(),n.getDate());let s,e;if(v==='this_week'||v==='last_week'){const day=(d.getDay()+6)%7;s=new Date(d);s.setDate(d.getDate()-day-(v==='last_week'?7:0));e=new Date(s);e.setDate(s.getDate()+6)}else if(v==='last_month'){s=new Date(d.getFullYear(),d.getMonth()-1,1);e=new Date(d.getFullYear(),d.getMonth(),0)}else if(v==='this_quarter'||v==='last_quarter'){let q=Math.floor(d.getMonth()/3)+(v==='last_quarter'?-1:0),y=d.getFullYear();if(q<0){q=3;y--}s=new Date(y,q*3,1);e=new Date(y,q*3+3,0)}else if(v==='this_year'){s=new Date(d.getFullYear(),0,1);e=new Date(d.getFullYear(),11,31)}else{s=new Date(d.getFullYear(),d.getMonth(),1);e=new Date(d.getFullYear(),d.getMonth()+1,0)}return[fmt(s),fmt(e)]}
function setRange(){if(period.value==='custom')return;const [s,e]=rangeFor(period.value);from.value=s;to.value=e}period.addEventListener('change',setRange);setRange();
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
const money=n=>'TZS '+Number(n||0).toLocaleString(undefined,{maximumFractionDigits:0});
function pctText(x){return x.percent===null?'New activity':`${x.percent>=0?'+':''}${Number(x.percent).toFixed(1)}%`}
function trendBar(label,x,color='bg-indigo-500'){const current=Number(x.current||0),previous=Number(x.previous||0),max=Math.max(current,previous,1);return `<div><div class="flex justify-between gap-3 text-xs mb-1"><span class="font-semibold text-slate-700">${label}</span><span class="${current>=previous?'text-emerald-700':'text-rose-700'} font-semibold">${pctText(x)}</span></div><div class="grid grid-cols-[70px_1fr] items-center gap-2 text-[10px] text-slate-500 mb-1"><span>Current</span><div class="h-2.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full ${color} rounded-full" style="width:${current/max*100}%"></div></div></div><div class="grid grid-cols-[70px_1fr] items-center gap-2 text-[10px] text-slate-500"><span>Previous</span><div class="h-2.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full bg-slate-400 rounded-full" style="width:${previous/max*100}%"></div></div></div></div>`}
async function loadReport(){const s=from.value,e=to.value;if(!s||!e||s>e){alert('Choose a valid reporting period.');return}document.getElementById('report-status').textContent='Loading';try{const r=await fetch(`${BASE_URL}/api/v1/reports/comparison?start=${encodeURIComponent(s)}&end=${encodeURIComponent(e)}`);const j=await r.json();if(!r.ok||j.success===false)throw new Error(j.message||'Unable to load report');const d=j.data,c=d.current,ch=d.changes;document.getElementById('period-label').textContent=`${s} → ${e} compared with ${d.comparison_range.start} → ${d.comparison_range.end}`;document.getElementById('comparison-range').textContent=`Previous: ${d.comparison_range.start} → ${d.comparison_range.end}`;
const cards=[['Attendance',Number(c.attendance).toLocaleString(),pctText(ch.attendance)],['Average / Ibada',Number(c.attendance_average).toFixed(1),pctText(ch.attendance_average)],['Guest visits',Number(c.guests_period).toLocaleString(),pctText(ch.guests_period)],['Church Giving',money(c.giving),pctText(ch.giving)]];document.getElementById('report-kpis').innerHTML=cards.map(x=>`<div class="ui-card ui-kpi ui-fade-in"><span class="ui-kpi-dot"></span><div class="ui-kpi-label">${x[0]}</div><div class="ui-kpi-value">${x[1]}</div><div class="ui-kpi-note">${x[2]} vs previous period</div></div>`).join('');
document.getElementById('comparison-bars').innerHTML=trendBar('Attendance',ch.attendance)+trendBar('Average attendance',ch.attendance_average,'bg-emerald-500')+trendBar('Guest visits',ch.guests_period,'bg-violet-500')+trendBar('Giving',ch.giving,'bg-amber-500');document.getElementById('report-summary').textContent=d.ai_trend_summary||d.trend_summary;
const labels={attendance:'Attendance',attendance_average:'Average attendance / Ibada',guests_period:'Guest visits',giving:'Church giving',new_members:'New members'};document.getElementById('comparison-rows').innerHTML=Object.entries(labels).map(([k,l])=>{const x=ch[k],isMoney=k==='giving';return `<tr><td class="font-semibold">${l}</td><td>${isMoney?money(x.current):Number(x.current).toLocaleString()}</td><td>${isMoney?money(x.previous):Number(x.previous).toLocaleString()}</td><td class="${x.difference>=0?'text-emerald-700':'text-rose-700'}">${x.difference>=0?'+':''}${isMoney?money(x.difference):Number(x.difference).toLocaleString()}</td><td>${pctText(x)}</td></tr>`}).join('');document.getElementById('attendance-rows').innerHTML=(c.attendance_rows||[]).map(x=>`<tr><td class="font-semibold">${esc(x.service_name)}</td><td>${esc(x.service_date)}</td><td>${esc(({main_service:'Main / Single Service',first_sermon:'First Sermon',second_sermon:'Second Sermon',third_sermon:'Third Sermon',other:'Other Session'})[x.session_type]||x.session_type||'-')}</td><td>${x.men_count}</td><td>${x.women_count}</td><td>${x.children_count}</td><td>${x.youth_count}</td><td>${x.guests_count}</td><td class="font-semibold text-royal-700">${x.total_count}</td></tr>`).join('')||'<tr><td colspan="9" class="text-slate-500">No attendance records in this period.</td></tr>';document.getElementById('report-status').textContent='Ready';}catch(err){document.getElementById('report-status').textContent='Error';document.getElementById('report-summary').textContent=err.message}}
document.getElementById('apply-filter').onclick=loadReport;document.getElementById('refresh-report').onclick=loadReport;
document.getElementById('download-pdf').onclick=()=>{const s=from.value,e=to.value;if(!s||!e||s>e){alert('Choose a valid reporting period.');return}const q=new URLSearchParams({start:s,end:e,type:document.getElementById('report-type').value,description:document.getElementById('report-description').value});window.location.href=`${BASE_URL}/api/v1/reports/download/pdf?${q.toString()}`};loadReport();
</script>
