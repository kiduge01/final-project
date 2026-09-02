<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div><h1 class="text-3xl font-heading font-semibold text-royal-900">Guests</h1><p class="text-mist-600 text-sm mt-1">Register guests, track visits and identify follow-up needs.</p></div>
    <button onclick="document.getElementById('guest-form').classList.toggle('hidden')" class="px-4 py-2.5 rounded-xl bg-royal-600 text-white font-semibold">Register Guest</button>
</div>
<section id="guest-form" class="hidden bg-white rounded-2xl border border-mist-200 shadow-sm p-5 mb-5">
<form id="guest-register" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
<?php foreach ([['first_name','First name','text'],['last_name','Last name','text'],['phone','Phone','tel'],['location','Location/Area','text'],['email','Email','email'],['service_date','Service date','date']] as $f): ?>
<div><label class="block text-xs font-semibold text-mist-600 mb-1"><?= htmlspecialchars($f[1]) ?></label><input name="<?= $f[0] ?>" type="<?= $f[2] ?>" <?= $f[0] !== 'email' ? 'required' : '' ?> class="w-full rounded-xl border border-mist-200 px-3 py-2.5"></div>
<?php endforeach; ?>
<div><label class="block text-xs font-semibold text-mist-600 mb-1">Visit type</label><select name="visit_type" class="w-full rounded-xl border border-mist-200 px-3 py-2.5"><option value="first_time">First time</option><option value="returning">Returning</option><option value="referred">Referred</option></select></div>
<div class="md:col-span-2"><label class="block text-xs font-semibold text-mist-600 mb-1">Notes</label><textarea name="notes" class="w-full rounded-xl border border-mist-200 px-3 py-2.5"></textarea></div>
<div class="flex items-end"><button class="w-full px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold">Save Guest</button></div>
</form><div id="guest-feedback" class="hidden mt-3 text-sm"></div>
</section>
<section class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
<div class="flex gap-2 mb-4"><input id="guest-search" class="flex-1 rounded-xl border border-mist-200 px-3 py-2.5" placeholder="Search name, phone or location"><select id="guest-status" class="rounded-xl border border-mist-200 px-3 py-2.5"><option value="">All statuses</option><option value="registered">Registered</option><option value="visited">Visited</option><option value="converted">Converted</option><option value="inactive">Inactive</option></select><button onclick="loadGuests()" class="px-4 rounded-xl bg-mist-900 text-white">Search</button></div>
<div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="border-b text-left"><th class="py-3">Guest</th><th>Phone</th><th>Visit</th><th>Service date</th><th>Status</th><th>Follow-up</th></tr></thead><tbody id="guest-rows"></tbody></table></div>
</section>
<script>
async function loadGuests(){const q=new URLSearchParams({search:document.getElementById('guest-search').value,status:document.getElementById('guest-status').value});const r=await fetch(`${BASE_URL}/api/v1/attendance/guests?${q}`);const j=await r.json();const rows=document.getElementById('guest-rows');rows.innerHTML=(j.data||[]).map(g=>`<tr class="border-b border-mist-100"><td class="py-3 font-semibold">${esc(g.first_name+' '+g.last_name)}</td><td>${esc(g.phone)}</td><td>${esc(g.visit_type)}</td><td>${esc(g.service_date)}</td><td>${esc(g.status)}</td><td>${esc(g.follow_up_date||'—')}</td></tr>`).join('') || '<tr><td colspan="6" class="py-8 text-center text-mist-500">No guests found.</td></tr>';}
function esc(v){const d=document.createElement('div');d.textContent=v??'';return d.innerHTML;}
document.getElementById('guest-register').addEventListener('submit',async e=>{e.preventDefault();const data=Object.fromEntries(new FormData(e.target));if(!data.service_date)data.service_date=new Date().toISOString().slice(0,10);const r=await fetch(`${BASE_URL}/api/v1/attendance/register-guest`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});const j=await r.json();const f=document.getElementById('guest-feedback');f.className='mt-3 text-sm '+(j.success?'text-emerald-700':'text-red-700');f.textContent=j.message||'Saved';if(j.success){e.target.reset();loadGuests();}});loadGuests();
</script>
