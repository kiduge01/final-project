<div class="ui-fade-in" id="settings-app">
  <div class="ui-page-head">
    <div><h1 class="ui-title">Settings & Security</h1><p class="ui-subtitle">Manage your account, users, access control, church profile, communication and security preferences.</p></div>
  </div>

  <div class="space-y-5">
    <nav id="settings-tabs" class="settings-tabs no-print">
      <button class="settings-tab is-active" data-tab="account">My Account</button>
      <button class="settings-tab" data-tab="users">Users</button>
      <button class="settings-tab" data-tab="roles">Roles & Permissions</button>
      <button class="settings-tab" data-tab="system">Church & System</button>
      <button class="settings-tab" data-tab="communication">SMS & Email</button>
      <button class="settings-tab" data-tab="security">Security</button>
      <button class="settings-tab" data-tab="audit">Audit Log</button>
    </nav>

    <div class="space-y-5">
      <section class="settings-panel ui-card" data-panel="account">
        <div class="mb-5"><h2 class="ui-section-title">My Account</h2><p class="ui-section-sub">Update your profile and account credentials.</p></div>
        <form id="account-form" class="grid md:grid-cols-2 gap-4">
          <div><label class="ui-label">Full name</label><input id="account-name" class="ui-input" required></div>
          <div><label class="ui-label">Phone</label><input id="account-phone" class="ui-input" required></div>
          <div><label class="ui-label">Email</label><input id="account-email" type="email" class="ui-input"></div>
          <div><label class="ui-label">Role</label><input id="account-role" class="ui-input bg-slate-50" disabled></div>
          <div class="md:col-span-2 flex justify-end"><button class="ui-btn ui-btn-primary">Save profile</button></div>
        </form>
        <hr class="my-7 border-slate-200">
        <div class="mb-4"><h3 class="font-semibold text-slate-900">Change Password</h3><p class="text-sm text-slate-500 mt-1">Passwords are never displayed. Enter your current password to set a new one.</p></div>
        <form id="password-form" class="grid md:grid-cols-3 gap-4">
          <div><label class="ui-label">Current password</label><input id="current-password" type="password" class="ui-input" required></div>
          <div><label class="ui-label">New password</label><input id="new-password" type="password" minlength="8" class="ui-input" required></div>
          <div><label class="ui-label">Confirm password</label><input id="confirm-password" type="password" minlength="8" class="ui-input" required></div>
          <div class="md:col-span-3 flex justify-end"><button class="ui-btn ui-btn-secondary">Change password</button></div>
        </form>
      </section>

      <section class="settings-panel ui-card hidden" data-panel="users">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5"><div><h2 class="ui-section-title">System Users</h2><p class="ui-section-sub">Create, edit, activate and deactivate administrative accounts.</p></div><button id="add-user" class="ui-btn ui-btn-primary">+ Add User</button></div>
        <div class="overflow-x-auto"><table class="ui-table"><thead><tr><th>User</th><th>Contact</th><th>Role</th><th>Status</th><th>Last login</th><th>Actions</th></tr></thead><tbody id="users-body"><tr><td colspan="6">Loading…</td></tr></tbody></table></div>
      </section>

      <section class="settings-panel ui-card hidden" data-panel="roles">
        <div class="mb-4"><h2 class="ui-section-title">Role Permissions</h2><p class="ui-section-sub">Click toggles to grant or revoke access per role, then save.</p></div>
        <div id="role-tabs" class="role-tabs mb-5"><div class="ui-skeleton h-10 w-40"></div></div>
        <div id="roles-grid"><div class="ui-skeleton h-64"></div></div>
      </section>

      <section class="settings-panel ui-card hidden" data-panel="system">
        <div class="mb-5"><h2 class="ui-section-title">Church & System</h2><p class="ui-section-sub">Configure church identity and default application preferences.</p></div>
        <form id="system-form" class="grid md:grid-cols-2 gap-4">
          <div><label class="ui-label">Church name</label><input id="church-name" class="ui-input"></div>
          <div><label class="ui-label">Church phone</label><input id="church-phone" class="ui-input"></div>
          <div><label class="ui-label">Church email</label><input id="church-email" type="email" class="ui-input"></div>
          <div><label class="ui-label">Timezone</label><input id="timezone" class="ui-input" value="Africa/Dar_es_Salaam"></div>
          <div class="md:col-span-2"><label class="ui-label">Church address</label><input id="church-address" class="ui-input"></div>
          <div><label class="ui-label">Default report period</label><select id="report-default-period" class="ui-select"><option value="this_week">This Week</option><option value="this_month">This Month</option><option value="last_month">Last Month</option><option value="this_quarter">This Quarter</option><option value="this_year">This Year</option></select></div>
          <div class="flex items-end justify-end"><button class="ui-btn ui-btn-primary">Save system settings</button></div>
        </form>
      </section>

      <section class="settings-panel ui-card hidden" data-panel="communication">
        <div class="mb-5"><h2 class="ui-section-title">SMS & Email Settings</h2><p class="ui-section-sub">Configure communication identity. Secret API keys should remain in environment configuration, not in the browser.</p></div>
        <form id="communication-settings-form" class="grid md:grid-cols-2 gap-4">
          <div><label class="ui-label">SMS provider</label><input id="sms-provider" class="ui-input" placeholder="Configured provider name"></div>
          <div><label class="ui-label">SMS sender ID</label><input id="sms-sender-id" class="ui-input" placeholder="TCRIC"></div>
          <div><label class="ui-label">Email from name</label><input id="email-from-name" class="ui-input" placeholder="The City of Refuge"></div>
          <div><label class="ui-label">Email from address</label><input id="email-from-address" type="email" class="ui-input"></div>
          <div class="md:col-span-2 rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">API keys and SMTP passwords are intentionally not displayed here. Keep secrets in the server <code>.env</code> or secure hosting environment.</div>
          <div class="md:col-span-2 flex justify-end"><button class="ui-btn ui-btn-primary">Save communication settings</button></div>
        </form>
      </section>

      <section class="settings-panel ui-card hidden" data-panel="security">
        <div class="mb-5"><h2 class="ui-section-title">Security Preferences</h2><p class="ui-section-sub">Control session and notification behavior without exposing sensitive credentials.</p></div>
        <form id="security-form" class="grid md:grid-cols-2 gap-4">
          <div><label class="ui-label">Session timeout (minutes)</label><input id="session-timeout" type="number" min="5" max="1440" class="ui-input" value="60"></div>
          <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4"><input id="notifications-enabled" type="checkbox" class="h-4 w-4" checked><span><strong class="block text-sm">System notifications</strong><span class="text-xs text-slate-500">Allow approved reminders and system notices.</span></span></label>
          <div class="md:col-span-2 flex justify-end"><button class="ui-btn ui-btn-primary">Save security preferences</button></div>
        </form>
      </section>

      <section class="settings-panel ui-card hidden" data-panel="audit">
        <div class="mb-5"><h2 class="ui-section-title">Audit Log</h2><p class="ui-section-sub">Recent administrative changes and AI actions.</p></div>
        <div class="overflow-x-auto"><table class="ui-table"><thead><tr><th>Date</th><th>User</th><th>Module</th><th>Action</th><th>Description</th></tr></thead><tbody id="audit-body"><tr><td colspan="5">Loading…</td></tr></tbody></table></div>
      </section>
  </div>
</div>
</div>

<div id="user-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4">
  <div class="ui-card w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-5"><div><h2 id="user-modal-title" class="ui-section-title">Add User</h2><p class="ui-section-sub">Set account details and role.</p></div><button type="button" id="close-user-modal" class="text-slate-500 text-xl">×</button></div>
    <form id="user-form" class="grid md:grid-cols-2 gap-4">
      <input type="hidden" id="user-id">
      <div><label class="ui-label">Full name</label><input id="user-name" class="ui-input" required></div>
      <div><label class="ui-label">Phone</label><input id="user-phone" class="ui-input" required></div>
      <div><label class="ui-label">Email</label><input id="user-email" type="email" class="ui-input"></div>
      <div><label class="ui-label">Role</label><select id="user-role" class="ui-select" required></select></div>
      <div><label class="ui-label">Password <span class="text-slate-400" id="password-note">(required)</span></label><input id="user-password" type="password" minlength="8" class="ui-input"></div>
      <label class="flex items-center gap-2 pt-7"><input id="user-active" type="checkbox" checked> <span class="text-sm">Account active</span></label>
      <div class="md:col-span-2 flex justify-end gap-2"><button type="button" id="cancel-user" class="ui-btn ui-btn-secondary">Cancel</button><button class="ui-btn ui-btn-primary">Save user</button></div>
    </form>
  </div>
</div>

<style>
.settings-tabs{display:flex;align-items:center;gap:.45rem;overflow-x:auto;border-bottom:1px solid #dbe3ef;padding-bottom:.65rem;scrollbar-width:thin}
.settings-tab{display:inline-flex;align-items:center;justify-content:center;min-height:40px;white-space:nowrap;padding:.65rem 1rem;border-radius:.5rem;background:#0f7f95!important;font-size:.875rem;font-weight:700;color:#fff!important;transition:.18s}
.settings-tab:hover{background:#0b6f83!important;color:#fff!important}
.settings-tab.is-active{background:#0f766e!important;color:#fff!important;box-shadow:inset 0 -3px 0 rgba(255,255,255,.35)}
.settings-panel{min-height:300px}
.role-tabs{display:flex;align-items:center;gap:.55rem;overflow-x:auto;padding-bottom:.35rem;scrollbar-width:thin}
.role-tab{display:inline-flex;align-items:center;justify-content:center;min-height:40px;white-space:nowrap;padding:.65rem 1rem;border-radius:.5rem;background:#0f7f95!important;color:#fff!important;font-size:.875rem;font-weight:800;transition:.18s}
.role-tab:hover{background:#0b6f83!important;color:#fff!important}
.role-tab.is-active{background:#0f766e!important;color:#fff!important;box-shadow:0 8px 18px rgba(15,118,110,.16)}
.permission-shell{border:1px solid #dbe3ef;border-radius:1rem;background:#fff;padding:1.25rem}
.permission-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem}
.permission-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.85rem}
.permission-card{overflow:hidden;border:1px solid #dbe3ef;border-radius:.75rem;background:#fff}
.permission-card-head{display:flex;align-items:center;gap:.45rem;padding:.72rem .9rem;border-bottom:1px solid currentColor;font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
.permission-card-body{display:grid}
.permission-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.72rem .9rem;border-bottom:1px solid #edf2f7;font-size:.9rem}
.permission-row:last-child{border-bottom:0}
.permission-row.is-off{color:#94a3b8;background:#f8fafc}
.perm-switch input{appearance:none!important;width:48px!important;height:24px!important;min-width:48px!important;border:0!important;border-radius:999px!important;background:#cbd5e1!important;box-shadow:none!important;cursor:pointer;position:relative;transition:.18s}
.perm-switch input:before{content:"";position:absolute;width:20px;height:20px;left:2px;top:2px;border-radius:999px;background:#fff;box-shadow:0 1px 3px rgba(15,23,42,.18);transition:.18s}
.perm-switch input:checked{background:#5dbdb4!important}
.perm-switch input:checked:before{transform:translateX(24px)}
.perm-switch input:disabled{opacity:.7;cursor:not-allowed}
.perm-assets{color:#1f2937;background:#f8fafc}.perm-attendance{color:#008a5a;background:#ecfdf3}.perm-communication{color:#db2777;background:#fdf2f8}.perm-departments{color:#7c3aed;background:#faf5ff}.perm-events{color:#ea580c;background:#fff7ed}.perm-finance{color:#059669;background:#ecfdf5}.perm-members{color:#2563eb;background:#eff6ff}.perm-settings{color:#4f46e5;background:#eef2ff}.perm-reports{color:#0f766e;background:#f0fdfa}.perm-default{color:#475569;background:#f8fafc}
@media(max-width:640px){.settings-tabs{gap:.4rem;padding-bottom:.6rem}.settings-tab{flex:0 0 auto;min-height:38px;padding:.58rem .82rem;font-size:.78rem}}
@media(max-width:1100px){.permission-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){.permission-head{display:block}.permission-head .ui-btn{margin-top:1rem;width:100%}.permission-grid{grid-template-columns:1fr}.role-tab{flex:0 0 auto;font-size:.78rem;padding:.58rem .82rem}}
</style>
<script>
const api = (p,o={}) => fetch(`${BASE_URL}/api/v1/${p}`,o).then(async r=>{const j=await r.json();if(!r.ok||j.success===false)throw new Error(j.message||'Request failed');return j});
const toast=m=>{const e=document.createElement('div');e.className='fixed right-5 top-5 z-[70] rounded-xl bg-slate-900 px-4 py-3 text-sm text-white shadow-xl';e.textContent=m;document.body.appendChild(e);setTimeout(()=>e.remove(),2800)};
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
let roles=[];let users=[];let permissions=[];let activeRoleId=null;
document.querySelectorAll('.settings-tab').forEach(b=>b.addEventListener('click',()=>{document.querySelectorAll('.settings-tab').forEach(x=>x.classList.remove('is-active'));b.classList.add('is-active');document.querySelectorAll('.settings-panel').forEach(x=>x.classList.add('hidden'));document.querySelector(`[data-panel="${b.dataset.tab}"]`)?.classList.remove('hidden');if(b.dataset.tab==='users')loadUsers();if(b.dataset.tab==='roles')loadRoles();if(b.dataset.tab==='audit')loadAudit();}));
async function loadAccount(){try{const d=(await api('settings/account')).data||{};accountName.value=d.full_name||'';accountPhone.value=d.phone||'';accountEmail.value=d.email||'';accountRole.value=d.role_name||'';}catch(e){toast(e.message)}}
const accountName=document.getElementById('account-name'),accountPhone=document.getElementById('account-phone'),accountEmail=document.getElementById('account-email'),accountRole=document.getElementById('account-role');
document.getElementById('account-form').addEventListener('submit',async e=>{e.preventDefault();try{await api('settings/account',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({full_name:accountName.value,phone:accountPhone.value,email:accountEmail.value})});toast('Account updated');}catch(x){toast(x.message)}});
document.getElementById('password-form').addEventListener('submit',async e=>{e.preventDefault();try{await api('settings/account/password',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({current_password:document.getElementById('current-password').value,new_password:document.getElementById('new-password').value,confirm_password:document.getElementById('confirm-password').value})});e.target.reset();toast('Password changed');}catch(x){toast(x.message)}});
function moduleClass(module){const key=String(module||'').toLowerCase().replace(/[^a-z0-9]+/g,'-');return ['assets','attendance','communication','departments','events','finance','members','settings','reports'].includes(key)?`perm-${key}`:'perm-default'}
function permissionLabel(p){const raw=String(p.name||p.action||'Permission');const part=raw.includes('.')?raw.split('.').pop():raw;return part.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())}
function selectedRole(){return roles.find(r=>Number(r.id)===Number(activeRoleId))||roles[0]||null}
function renderRoleTabs(){const tabs=document.getElementById('role-tabs');tabs.innerHTML=roles.map(r=>`<button type="button" class="role-tab ${Number(r.id)===Number(activeRoleId)?'is-active':''}" data-role-id="${r.id}">${esc(r.name)}</button>`).join('');tabs.querySelectorAll('.role-tab').forEach(btn=>btn.addEventListener('click',()=>{activeRoleId=Number(btn.dataset.roleId);renderRolePermissions()}))}
function renderRolePermissions(){const role=selectedRole();const grid=document.getElementById('roles-grid');if(!role){grid.innerHTML='<p class="text-sm text-slate-500">No roles found.</p>';document.getElementById('role-tabs').innerHTML='';return}renderRoleTabs();const isAdmin=String(role.name||'').toLowerCase()==='admin';const selected=new Set((role.permissions||[]).map(p=>Number(p.permission_id||p.id)));const grouped={};permissions.forEach(p=>(grouped[p.module||'General']??=[]).push(p));const granted=p=>isAdmin||selected.has(Number(p.id));const cards=Object.entries(grouped).map(([module,ps])=>{const total=ps.length;const count=ps.filter(granted).length;return `<article class="permission-card"><div class="permission-card-head ${moduleClass(module)}"><span>${esc(module)}</span><span class="text-xs opacity-60">(${count}/${total})</span></div><div class="permission-card-body">${ps.map(p=>`<label class="permission-row ${granted(p)?'':'is-off'}"><span>${esc(permissionLabel(p))}</span><span class="perm-switch"><input type="checkbox" class="role-perm-${role.id}" value="${p.id}" ${granted(p)?'checked':''} ${isAdmin?'disabled':''}></span></label>`).join('')}</div></article>`}).join('');grid.innerHTML=`<article class="permission-shell"><div class="permission-head"><div><h3 class="text-lg font-bold text-slate-900">${esc(role.name)}</h3><p class="text-sm text-slate-500 mt-1">${isAdmin?'Full access - all permissions always granted':esc(role.description||'Role-based system access')}</p></div>${isAdmin?'<p class="text-xs italic text-slate-400">All access - no edits needed</p>':`<button type="button" class="ui-btn ui-btn-primary" onclick="saveRolePermissions(${role.id})">Save ${esc(role.name)}</button>`}</div><div class="permission-grid">${cards}</div></article>`}
async function loadRoles(){try{roles=(await api('settings/roles')).data||[];permissions=(await api('settings/permissions')).data||[];if(!activeRoleId&&roles.length)activeRoleId=Number(roles[0].id);renderRolePermissions();fillRoleSelect();}catch(e){document.getElementById('roles-grid').innerHTML=`<p>${esc(e.message)}</p>`}}
window.saveRolePermissions=async roleId=>{const role=roles.find(r=>Number(r.id)===Number(roleId));if(String(role?.name||'').toLowerCase()==='admin'){toast('Admin keeps full access');return}const ids=[...document.querySelectorAll(`.role-perm-${roleId}:checked`)].map(x=>Number(x.value));try{await api(`settings/roles/${roleId}/permissions`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({permission_ids:ids})});toast('Role permissions updated');loadRoles()}catch(e){toast(e.message)}};
function fillRoleSelect(){document.getElementById('user-role').innerHTML=roles.map(r=>`<option value="${r.id}">${esc(r.name)}</option>`).join('')}
async function loadUsers(){try{if(!roles.length)await loadRoles();users=(await api('settings/users')).data||[];document.getElementById('users-body').innerHTML=users.map(u=>`<tr><td><strong>${esc(u.full_name)}</strong><div class="text-xs text-slate-400">#${u.id}</div></td><td>${esc(u.phone)}<div class="text-xs text-slate-400">${esc(u.email||'')}</div></td><td>${esc(u.role_name||'—')}</td><td><span class="ui-pill ${Number(u.is_active)?'ui-pill-green':''}">${Number(u.is_active)?'Active':'Inactive'}</span></td><td>${esc(u.last_login_at||'Never')}</td><td><button class="text-royal-700 font-semibold text-xs" onclick="editUser(${u.id})">Edit</button>${Number(u.is_active)?` <button class="text-red-600 font-semibold text-xs ml-3" onclick="deactivateUser(${u.id})">Deactivate</button>`:''}</td></tr>`).join('')||'<tr><td colspan="6">No users found.</td></tr>';}catch(e){toast(e.message)}}
const modal=document.getElementById('user-modal');function openUserModal(){modal.classList.remove('hidden');modal.classList.add('flex')}function closeUserModal(){modal.classList.add('hidden');modal.classList.remove('flex')}
document.getElementById('add-user').onclick=()=>{document.getElementById('user-form').reset();document.getElementById('user-id').value='';document.getElementById('user-active').checked=true;document.getElementById('user-modal-title').textContent='Add User';document.getElementById('password-note').textContent='(required)';openUserModal()};document.getElementById('close-user-modal').onclick=closeUserModal;document.getElementById('cancel-user').onclick=closeUserModal;
window.editUser=id=>{const u=users.find(x=>Number(x.id)===Number(id));if(!u)return;document.getElementById('user-id').value=u.id;document.getElementById('user-name').value=u.full_name||'';document.getElementById('user-phone').value=u.phone||'';document.getElementById('user-email').value=u.email||'';document.getElementById('user-role').value=u.role_id||'';document.getElementById('user-password').value='';document.getElementById('user-active').checked=Number(u.is_active)===1;document.getElementById('user-modal-title').textContent='Edit User';document.getElementById('password-note').textContent='(leave blank to keep current)';openUserModal()};
window.deactivateUser=async id=>{if(!confirm('Deactivate this user account?'))return;try{await api(`settings/users/${id}`,{method:'DELETE'});toast('User deactivated');loadUsers()}catch(e){toast(e.message)}};
document.getElementById('user-form').addEventListener('submit',async e=>{e.preventDefault();const id=document.getElementById('user-id').value;const body={full_name:document.getElementById('user-name').value,phone:document.getElementById('user-phone').value,email:document.getElementById('user-email').value,role_id:Number(document.getElementById('user-role').value),is_active:document.getElementById('user-active').checked?1:0};const pw=document.getElementById('user-password').value;if(pw)body.password=pw;if(!id&&!pw){toast('Password is required for a new user');return}try{await api(id?`settings/users/${id}`:'settings/users',{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});closeUserModal();toast(id?'User updated':'User created');loadUsers()}catch(x){toast(x.message)}});
async function loadPrefs(){try{const d=(await api('settings/preferences')).data||{};const map={'church-name':'church_name','church-phone':'church_phone','church-email':'church_email','church-address':'church_address','timezone':'timezone','report-default-period':'report_default_period','sms-provider':'sms_provider','sms-sender-id':'sms_sender_id','email-from-name':'email_from_name','email-from-address':'email_from_address','session-timeout':'session_timeout_minutes'};for(const [id,k] of Object.entries(map)){const e=document.getElementById(id);if(e&&d[k]!==undefined)e.value=d[k]}document.getElementById('notifications-enabled').checked=d.notifications_enabled!=='0';}catch(e){}}
async function savePrefs(keys){const body={};keys.forEach(([id,k])=>{const e=document.getElementById(id);body[k]=e.type==='checkbox'?e.checked:e.value});try{await api('settings/preferences',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});toast('Settings saved')}catch(e){toast(e.message)}}
document.getElementById('system-form').addEventListener('submit',e=>{e.preventDefault();savePrefs([['church-name','church_name'],['church-phone','church_phone'],['church-email','church_email'],['church-address','church_address'],['timezone','timezone'],['report-default-period','report_default_period']])});document.getElementById('communication-settings-form').addEventListener('submit',e=>{e.preventDefault();savePrefs([['sms-provider','sms_provider'],['sms-sender-id','sms_sender_id'],['email-from-name','email_from_name'],['email-from-address','email_from_address']])});document.getElementById('security-form').addEventListener('submit',e=>{e.preventDefault();savePrefs([['session-timeout','session_timeout_minutes'],['notifications-enabled','notifications_enabled']])});
async function loadAudit(){try{const rows=(await api('settings/audit-logs')).data||[];document.getElementById('audit-body').innerHTML=rows.map(r=>`<tr><td>${esc(r.created_at)}</td><td>${esc(r.full_name||'System')}</td><td>${esc(r.module)}</td><td>${esc(r.action)}</td><td>${esc(r.description||'')}</td></tr>`).join('')||'<tr><td colspan="5">No audit records found.</td></tr>'}catch(e){toast(e.message)}}
loadAccount();loadPrefs();loadRoles();
</script>
