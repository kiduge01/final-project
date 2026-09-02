<?php $B = $baseUrl ?? ''; ?>

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-3xl font-heading font-semibold text-royal-900">Church Members</h1>
        <p class="text-mist-600 text-sm mt-1">Manage church members and guest follow-up from one place.</p>
    </div>
    <div class="flex gap-2">
        <button type="button" class="tab-btn px-4 py-2 rounded-xl bg-royal-600 text-white text-sm font-semibold" data-tab="members" id="btn-tab-members">Members</button>
        <button type="button" class="tab-btn px-4 py-2 rounded-xl bg-mist-100 text-mist-700 text-sm font-semibold" data-tab="guests" id="btn-tab-guests">Guests</button>
    </div>
</div>

<!-- MEMBERS -->
<section id="tab-content-members" class="tab-content">
    <div class="flex flex-wrap justify-between gap-2 mb-5">
        <div><h2 class="text-2xl font-semibold text-royal-900">Members List</h2><p class="text-sm text-mist-600">Register, search and maintain congregation member records.</p></div>
        <div class="flex gap-2">
            <button id="btn-export-members" class="px-4 py-2 rounded-xl bg-mist-100 text-mist-700 text-sm font-semibold">Export CSV</button>
            <button id="btn-add-member" class="px-4 py-2 rounded-xl bg-royal-600 text-white text-sm font-semibold">+ Add Member</button>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-mist-500 uppercase">Total</p><p id="member-total" class="text-2xl font-bold text-royal-800">0</p></div>
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-emerald-600 uppercase">Active</p><p id="member-active" class="text-2xl font-bold text-emerald-700">0</p></div>
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-mist-500 uppercase">Inactive</p><p id="member-inactive" class="text-2xl font-bold text-mist-700">0</p></div>
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-dawn-600 uppercase">Transferred</p><p id="member-transferred" class="text-2xl font-bold text-dawn-700">0</p></div>
    </div>

    <div class="bg-white rounded-2xl border p-4 mb-4 flex flex-wrap gap-2">
        <input id="member-search" class="flex-1 min-w-56 rounded-xl border px-3 py-2 text-sm" placeholder="Search name, phone, code or email">
        <select id="member-status" class="rounded-xl border px-3 py-2 text-sm"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="transferred">Transferred</option><option value="deceased">Deceased</option></select>
        <select id="member-gender" class="rounded-xl border px-3 py-2 text-sm"><option value="">All Gender</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
        <select id="member-region" class="rounded-xl border px-3 py-2 text-sm"><option value="">All Regions</option></select>
        <button id="member-clear" class="px-3 py-2 rounded-xl bg-mist-100 text-mist-700 text-sm">Clear</button>
    </div>

    <div class="bg-white rounded-2xl border overflow-hidden">
        <div class="px-5 py-3 border-b flex justify-between"><h3 class="font-semibold text-royal-800">Members</h3><span id="member-count" class="text-xs text-mist-500"></span></div>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-mist-50"><tr>
            <th class="px-4 py-3 text-left">Code</th><th class="px-4 py-3 text-left">Full Name</th><th class="px-4 py-3 text-left">Phone</th><th class="px-4 py-3 text-left">Gender</th><th class="px-4 py-3 text-left">Region</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-right">Actions</th>
        </tr></thead><tbody id="members-tbody" class="divide-y"></tbody></table></div>
        <div id="members-empty" class="hidden py-12 text-center text-mist-500">No members found.</div>
    </div>
</section>

<!-- GUESTS TAB -->
<section id="tab-content-guests" class="tab-content hidden">
    <div class="flex flex-wrap justify-between gap-2 mb-5">
        <div><h2 class="text-2xl font-semibold text-royal-900">Guest Registry</h2><p class="text-sm text-mist-600">Register visitors, track visits, update follow-up and identify guests who need attention.</p></div>
        <div class="flex gap-2"><button id="btn-export-guests" class="px-4 py-2 rounded-xl bg-mist-100 text-mist-700 text-sm font-semibold">Export CSV</button><button id="btn-add-guest" class="px-4 py-2 rounded-xl bg-royal-600 text-white text-sm font-semibold">+ Register Guest</button></div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-mist-500 uppercase">Total</p><p id="guest-total" class="text-2xl font-bold text-royal-800">0</p></div>
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-emerald-600 uppercase">Registered</p><p id="guest-registered" class="text-2xl font-bold text-emerald-700">0</p></div>
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-blue-600 uppercase">Visited</p><p id="guest-visited" class="text-2xl font-bold text-blue-700">0</p></div>
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-purple-600 uppercase">Converted</p><p id="guest-converted" class="text-2xl font-bold text-purple-700">0</p></div>
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-dawn-600 uppercase">Follow-up</p><p id="guest-followup" class="text-2xl font-bold text-dawn-700">0</p></div>
    </div>

    <div class="bg-white rounded-2xl border p-4 mb-4 flex flex-wrap gap-2">
        <input id="guest-search" class="flex-1 min-w-56 rounded-xl border px-3 py-2 text-sm" placeholder="Search code, name, phone, email or location">
        <select id="guest-status" class="rounded-xl border px-3 py-2 text-sm"><option value="">All Status</option><option value="registered">Registered</option><option value="visited">Visited</option><option value="converted">Converted</option><option value="inactive">Inactive</option></select>
        <button id="guest-clear" class="px-3 py-2 rounded-xl bg-mist-100 text-mist-700 text-sm">Clear</button>
    </div>

    <div class="bg-white rounded-2xl border overflow-hidden"><div class="px-5 py-3 border-b flex justify-between"><h3 class="font-semibold text-royal-800">Guests</h3><span id="guest-count" class="text-xs text-mist-500"></span></div>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-mist-50"><tr>
            <th class="px-4 py-3 text-left">Code</th><th class="px-4 py-3 text-left">Full Name</th><th class="px-4 py-3 text-left">Phone</th><th class="px-4 py-3 text-left">Service Date</th><th class="px-4 py-3 text-left">Visit</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Follow-up</th><th class="px-4 py-3 text-right">Actions</th>
        </tr></thead><tbody id="guests-tbody" class="divide-y"></tbody></table></div><div id="guests-empty" class="hidden py-12 text-center text-mist-500">No guests found.</div>
    </div>
</section>

<!-- MEMBER MODAL -->
<div id="member-modal" class="hidden fixed inset-0 z-50 overflow-y-auto"><div class="min-h-screen flex items-start justify-center p-4 pt-10"><div class="fixed inset-0 bg-black/40" data-close="member"></div><div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-6">
    <div class="flex justify-between mb-5"><h3 id="member-modal-title" class="text-xl font-semibold text-royal-900">Add Member</h3><button data-close="member" class="text-mist-500">✕</button></div>
    <form id="member-form" class="grid grid-cols-1 md:grid-cols-2 gap-3"><input type="hidden" name="id">
        <label class="text-sm">First name<input name="first_name" required class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm">Last name<input name="last_name" required class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm">Gender<select name="gender" required class="mt-1 w-full rounded-xl border px-3 py-2"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></label>
        <label class="text-sm">Phone<input name="phone" required class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm">Email<input type="email" name="email" class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm">Date of birth<input type="date" name="date_of_birth" class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm">Join date<input type="date" name="join_date" class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm">Marital status<select name="marital_status" class="mt-1 w-full rounded-xl border px-3 py-2"><option value="">Select</option><option value="single">Single</option><option value="married">Married</option><option value="widowed">Widowed</option><option value="divorced">Divorced</option></select></label>
        <label class="text-sm">Ward<input name="ward" class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm">District<input name="district" class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm">Region<input name="region" class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm">Alternative phone<input name="alt_phone" class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm">Status<select name="member_status" class="mt-1 w-full rounded-xl border px-3 py-2"><option value="active">Active</option><option value="inactive">Inactive</option><option value="transferred">Transferred</option><option value="deceased">Deceased</option></select></label><label class="text-sm">Baptism date<input type="date" name="baptism_date" class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm md:col-span-2">Physical address<input name="physical_address" class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm md:col-span-2">Notes<textarea name="notes" rows="2" class="mt-1 w-full rounded-xl border px-3 py-2"></textarea></label>
        <div class="md:col-span-2 flex justify-end gap-2"><button type="button" data-close="member" class="px-4 py-2 rounded-xl bg-mist-100">Cancel</button><button class="px-5 py-2 rounded-xl bg-royal-600 text-white font-semibold">Save Member</button></div>
    </form><p id="member-feedback" class="hidden mt-3 text-sm"></p>
</div></div></div>

<!-- GUEST MODAL -->
<div id="guest-modal" class="hidden fixed inset-0 z-50 overflow-y-auto"><div class="min-h-screen flex items-start justify-center p-4 pt-10"><div class="fixed inset-0 bg-black/40" data-close="guest"></div><div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6">
    <div class="flex justify-between mb-5"><h3 id="guest-modal-title" class="text-xl font-semibold text-royal-900">Register Guest</h3><button data-close="guest" class="text-mist-500">✕</button></div>
    <form id="guest-form" class="grid grid-cols-1 md:grid-cols-2 gap-3"><input type="hidden" name="id">
        <label class="text-sm">First name<input name="first_name" required class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm">Last name<input name="last_name" required class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm">Phone<input name="phone" required class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm">Location / Area<input name="location" required class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm">Email<input type="email" name="email" class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm">Service date<input type="date" name="service_date" class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm">Visit type<select name="visit_type" class="mt-1 w-full rounded-xl border px-3 py-2"><option value="first_time">First time</option><option value="returning">Returning</option><option value="referred">Referred</option></select></label>
        <label class="text-sm">Age group<select name="age_group" class="mt-1 w-full rounded-xl border px-3 py-2"><option value="">Not specified</option><option value="child">Child</option><option value="teen">Teen</option><option value="youth">Youth</option><option value="adult">Adult</option><option value="senior">Senior</option></select></label>
        <label class="text-sm">Invited by<input name="invited_by_name" class="mt-1 w-full rounded-xl border px-3 py-2"></label><label class="text-sm">Follow-up date<input type="date" name="follow_up_date" class="mt-1 w-full rounded-xl border px-3 py-2"></label>
        <label class="text-sm md:col-span-2">Notes<textarea name="notes" rows="2" class="mt-1 w-full rounded-xl border px-3 py-2"></textarea></label>
        <div class="md:col-span-2 flex justify-end gap-2"><button type="button" data-close="guest" class="px-4 py-2 rounded-xl bg-mist-100">Cancel</button><button class="px-5 py-2 rounded-xl bg-royal-600 text-white font-semibold">Save Guest</button></div>
    </form><p id="guest-feedback" class="hidden mt-3 text-sm"></p>
</div></div></div>

<script>
const MB = '<?= htmlspecialchars($B, ENT_QUOTES, 'UTF-8') ?>';
let members = [], guests = [];
const esc = v => { const d=document.createElement('div'); d.textContent=v ?? ''; return d.innerHTML; };
const api = async (url, options={}) => { const r=await fetch(MB+url, options); const j=await r.json().catch(()=>({success:false,message:'Invalid server response'})); if(!r.ok||j.success===false) throw new Error(j.message||'Request failed'); return j; };

function switchTab(tab){
    document.querySelectorAll('.tab-content').forEach(x=>x.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(x=>{x.classList.remove('bg-royal-600','text-white');x.classList.add('bg-mist-100','text-mist-700');});
    document.getElementById('tab-content-'+tab).classList.remove('hidden');
    const b=document.getElementById('btn-tab-'+tab); b.classList.remove('bg-mist-100','text-mist-700'); b.classList.add('bg-royal-600','text-white');
    if(tab==='guests') loadGuests();
}
document.querySelectorAll('.tab-btn').forEach(b=>b.addEventListener('click',()=>switchTab(b.dataset.tab)));

async function loadMemberStats(){try{const j=await api('/api/v1/members/stats');const s=j.data||{};['total','active','inactive','transferred'].forEach(k=>document.getElementById('member-'+k).textContent=s[k]??0);}catch(e){console.error(e);}}
async function loadMembers(){
    const p=new URLSearchParams(); const q=document.getElementById('member-search').value.trim(),s=document.getElementById('member-status').value,g=document.getElementById('member-gender').value,r=document.getElementById('member-region').value;
    if(q)p.set('search',q);if(s)p.set('status',s);if(g)p.set('gender',g);if(r)p.set('region',r);
    try{const j=await api('/api/v1/members?'+p);members=j.data||[];renderMembers();const regions=[...new Set(members.map(x=>x.region).filter(Boolean))].sort();const sel=document.getElementById('member-region'),cur=sel.value;sel.innerHTML='<option value="">All Regions</option>'+regions.map(x=>`<option value="${esc(x)}">${esc(x)}</option>`).join('');sel.value=cur;}catch(e){console.error(e);}}
function renderMembers(){const tb=document.getElementById('members-tbody');document.getElementById('member-count').textContent=members.length+' record'+(members.length===1?'':'s');document.getElementById('members-empty').classList.toggle('hidden',members.length>0);tb.innerHTML=members.map(m=>`<tr class="hover:bg-mist-50"><td class="px-4 py-3 font-mono text-xs">${esc(m.member_code)}</td><td class="px-4 py-3 font-semibold">${esc(m.first_name+' '+m.last_name)}</td><td class="px-4 py-3">${esc(m.phone)}</td><td class="px-4 py-3 capitalize">${esc(m.gender)}</td><td class="px-4 py-3">${esc(m.region||'-')}</td><td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs bg-mist-100">${esc(m.member_status)}</span></td><td class="px-4 py-3 text-right"><button class="edit-member text-royal-600 font-semibold mr-2" data-id="${m.id}">Edit</button><button class="delete-member text-red-600 font-semibold" data-id="${m.id}">Delete</button></td></tr>`).join('');tb.querySelectorAll('.edit-member').forEach(b=>b.onclick=()=>openMember(Number(b.dataset.id)));tb.querySelectorAll('.delete-member').forEach(b=>b.onclick=()=>deleteMember(Number(b.dataset.id)));}
function openMember(id=null){const f=document.getElementById('member-form');f.reset();f.id.value=id||'';document.getElementById('member-modal-title').textContent=id?'Edit Member':'Add Member';if(id){const m=members.find(x=>x.id===id);if(!m)return;Object.keys(m).forEach(k=>{const e=f.elements[k];if(e)e.value=m[k]??'';});}document.getElementById('member-modal').classList.remove('hidden');}
async function deleteMember(id){const m=members.find(x=>x.id===id);if(!m||!confirm('Delete '+m.first_name+' '+m.last_name+'?'))return;try{await api('/api/v1/members/'+id,{method:'DELETE'});await Promise.all([loadMembers(),loadMemberStats()]);}catch(e){alert(e.message);}}

document.getElementById('btn-add-member').onclick=()=>openMember();document.getElementById('member-form').onsubmit=async e=>{e.preventDefault();const f=new FormData(e.target),id=f.get('id');f.delete('id');const body=JSON.stringify(Object.fromEntries(f));try{const j=await api('/api/v1/members'+(id?'/'+id:''),{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body});showFeedback('member',j.message||'Saved');document.getElementById('member-modal').classList.add('hidden');await Promise.all([loadMembers(),loadMemberStats()]);}catch(x){showFeedback('member',x.message,true);}};

async function loadGuests(){const p=new URLSearchParams(),q=document.getElementById('guest-search').value.trim(),s=document.getElementById('guest-status').value;if(q)p.set('search',q);if(s)p.set('status',s);try{const j=await api('/api/v1/attendance/guests?'+p);guests=j.data||[];renderGuests();}catch(e){console.error(e);}}
function renderGuests(){const tb=document.getElementById('guests-tbody');document.getElementById('guest-count').textContent=guests.length+' record'+(guests.length===1?'':'s');document.getElementById('guests-empty').classList.toggle('hidden',guests.length>0);const today=new Date().toISOString().slice(0,10);const follow=guests.filter(g=>g.follow_up_date&&g.follow_up_date<=today&&g.status!=='converted').length;document.getElementById('guest-total').textContent=guests.length;document.getElementById('guest-registered').textContent=guests.filter(g=>g.status==='registered').length;document.getElementById('guest-visited').textContent=guests.filter(g=>g.status==='visited').length;document.getElementById('guest-converted').textContent=guests.filter(g=>g.status==='converted').length;document.getElementById('guest-followup').textContent=follow;tb.innerHTML=guests.map(g=>`<tr class="hover:bg-mist-50"><td class="px-4 py-3 font-mono text-xs">${esc(g.guest_code)}</td><td class="px-4 py-3 font-semibold">${esc(g.first_name+' '+g.last_name)}</td><td class="px-4 py-3">${esc(g.phone)}</td><td class="px-4 py-3">${esc(g.service_date)}</td><td class="px-4 py-3">${esc(g.visit_type).replace('_',' ')}</td><td class="px-4 py-3"><select class="guest-status-update rounded-lg border px-2 py-1 text-xs" data-id="${g.id}"><option ${g.status==='registered'?'selected':''}>registered</option><option ${g.status==='visited'?'selected':''}>visited</option><option ${g.status==='converted'?'selected':''}>converted</option><option ${g.status==='inactive'?'selected':''}>inactive</option></select></td><td class="px-4 py-3 text-xs">${esc(g.follow_up_date||'-')}</td><td class="px-4 py-3 text-right"><button class="edit-guest text-royal-600 font-semibold" data-id="${g.id}">Edit</button></td></tr>`).join('');tb.querySelectorAll('.guest-status-update').forEach(s=>s.onchange=async()=>{try{await api('/api/v1/attendance/guests/'+s.dataset.id,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({status:s.value})});await loadGuests();}catch(e){alert(e.message);}});tb.querySelectorAll('.edit-guest').forEach(b=>b.onclick=()=>openGuest(Number(b.dataset.id)));}
function openGuest(id=null){const f=document.getElementById('guest-form');f.reset();f.id.value=id||'';document.getElementById('guest-modal-title').textContent=id?'Edit Guest':'Register Guest';if(id){const g=guests.find(x=>x.id===id);if(!g)return;Object.keys(g).forEach(k=>{const e=f.elements[k];if(e)e.value=g[k]??'';});}document.getElementById('guest-modal').classList.remove('hidden');}
document.getElementById('btn-add-guest').onclick=()=>openGuest();document.getElementById('guest-form').onsubmit=async e=>{e.preventDefault();const f=new FormData(e.target),id=f.get('id');f.delete('id');const data=Object.fromEntries(f);try{if(id){await api('/api/v1/attendance/guests/'+id,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({follow_up_date:data.follow_up_date,notes:data.notes})});}else{await api('/api/v1/attendance/register-guest',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});}document.getElementById('guest-modal').classList.add('hidden');await loadGuests();}catch(x){showFeedback('guest',x.message,true);}};

function showFeedback(type,msg,error=false){const e=document.getElementById(type+'-feedback');e.textContent=msg;e.className='mt-3 text-sm '+(error?'text-red-700':'text-emerald-700');}

document.querySelectorAll('[data-close]').forEach(b=>b.onclick=()=>document.getElementById(b.dataset.close+'-modal').classList.add('hidden'));
['member-search','member-status','member-gender','member-region'].forEach(id=>document.getElementById(id).addEventListener(id==='member-search'?'input':'change',loadMembers));document.getElementById('member-clear').onclick=()=>{document.getElementById('member-search').value='';document.getElementById('member-status').value='';document.getElementById('member-gender').value='';document.getElementById('member-region').value='';loadMembers();};
['guest-search','guest-status'].forEach(id=>document.getElementById(id).addEventListener(id==='guest-search'?'input':'change',loadGuests));document.getElementById('guest-clear').onclick=()=>{document.getElementById('guest-search').value='';document.getElementById('guest-status').value='';loadGuests();};

document.getElementById('btn-export-members').onclick=()=>downloadCsv('members.csv',members,['member_code','first_name','last_name','gender','phone','email','region','member_status','join_date']);document.getElementById('btn-export-guests').onclick=()=>downloadCsv('guests.csv',guests,['guest_code','first_name','last_name','phone','email','location','service_date','visit_type','status','follow_up_date','notes']);
function downloadCsv(name,rows,cols){if(!rows.length){alert('No records to export.');return;}const csv=[cols.join(','),...rows.map(r=>cols.map(c=>JSON.stringify(r[c]??'')).join(','))].join('\n');const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));a.download=name;a.click();URL.revokeObjectURL(a.href);}

// The standalone Guests navigation is intentionally hidden; Guests is a tab of Members.
document.querySelectorAll('nav a').forEach(a=>{if(a.textContent.trim()==='Guests')a.style.display='none';});
Promise.all([loadMemberStats(),loadMembers()]);
const initialTab=new URLSearchParams(location.search).get('tab');switchTab(initialTab==='guests'?'guests':'members');
</script>
