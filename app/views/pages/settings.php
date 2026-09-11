<div class="ui-fade-in" id="settings-app">
  <div class="ui-page-head">
    <div><h1 class="ui-title">Settings & Security</h1><p class="ui-subtitle">Manage your account, users, access control, church profile, communication and security preferences.</p></div>
  </div>

  <div class="grid lg:grid-cols-[240px_1fr] gap-5">
    <aside class="ui-card p-2 h-fit no-print">
      <nav id="settings-tabs" class="space-y-1">
        <button class="settings-tab is-active" data-tab="account">My Account</button>
        <button class="settings-tab" data-tab="users">Users</button>
        <button class="settings-tab" data-tab="roles">Roles & Permissions</button>
        <button class="settings-tab" data-tab="system">Church & System</button>
        <button class="settings-tab" data-tab="communication">SMS & Email</button>
        <button class="settings-tab" data-tab="security">Security</button>
        <button class="settings-tab" data-tab="audit">Audit Log</button>
      </nav>
    </aside>

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
        <div class="mb-5"><h2 class="ui-section-title">Roles & Permissions</h2><p class="ui-section-sub">Review role-based access to the active system functions.</p></div>
        <div id="roles-grid" class="grid md:grid-cols-2 gap-4"><div class="ui-skeleton h-32"></div><div class="ui-skeleton h-32"></div></div>
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
.settings-tab{width:100%;text-align:left;padding:.75rem .9rem;border-radius:.75rem;font-size:.875rem;font-weight:600;color:#64748b;transition:.18s}.settings-tab:hover{background:#f8fafc;color:#0f172a}.settings-tab.is-active{background:#eef2ff;color:#3344a5}.settings-panel{min-height:300px}
</style>
<script>
const api = (p,o={}) => fetch(`${BASE_URL}/api/v1/${p}`,o).then(async r=>{const j=await r.json();if(!r.ok||j.success===false)throw new Error(j.message||'Request failed');return j});
const toast=m=>{const e=document.createElement('div');e.className='fixed right-5 top-5 z-[70] rounded-xl bg-slate-900 px-4 py-3 text-sm text-white shadow-xl';e.textContent=m;document.body.appendChild(e);setTimeout(()=>e.remove(),2800)};
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
let roles=[];let users=[];
document.querySelectorAll('.settings-tab').forEach(b=>b.addEventListener('click',()=>{document.querySelectorAll('.settings-tab').forEach(x=>x.classList.remove('is-active'));b.classList.add('is-active');document.querySelectorAll('.settings-panel').forEach(x=>x.classList.add('hidden'));document.querySelector(`[data-panel="${b.dataset.tab}"]`)?.classList.remove('hidden');if(b.dataset.tab==='users')loadUsers();if(b.dataset.tab==='roles')loadRoles();if(b.dataset.tab==='audit')loadAudit();}));
async function loadAccount(){try{const d=(await api('settings/account')).data||{};accountName.value=d.full_name||'';accountPhone.value=d.phone||'';accountEmail.value=d.email||'';accountRole.value=d.role_name||'';}catch(e){toast(e.message)}}
const accountName=document.getElementById('account-name'),accountPhone=document.getElementById('account-phone'),accountEmail=document.getElementById('account-email'),accountRole=document.getElementById('account-role');
document.getElementById('account-form').addEventListener('submit',async e=>{e.preventDefault();try{await api('settings/account',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({full_name:accountName.value,phone:accountPhone.value,email:accountEmail.value})});toast('Account updated');}catch(x){toast(x.message)}});
document.getElementById('password-form').addEventListener('submit',async e=>{e.preventDefault();try{await api('settings/account/password',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({current_password:document.getElementById('current-password').value,new_password:document.getElementById('new-password').value,confirm_password:document.getElementById('confirm-password').value})});e.target.reset();toast('Password changed');}catch(x){toast(x.message)}});
async function loadRoles(){try{roles=(await api('settings/roles')).data||[];const all=(await api('settings/permissions')).data||[];document.getElementById('roles-grid').innerHTML=roles.map(r=>{const selected=new Set((r.permissions||[]).map(p=>Number(p.permission_id)));const grouped={};all.forEach(p=>(grouped[p.module]??=[]).push(p));return `<article class="rounded-xl border border-slate-200 p-4"><div class="flex items-start justify-between gap-3"><div><strong>${esc(r.name)}</strong><p class="text-xs text-slate-500 mt-1">${esc(r.description||'Role-based system access')}</p></div><span class="ui-pill">${selected.size} permissions</span></div><div class="mt-4 max-h-64 overflow-y-auto space-y-3">${Object.entries(grouped).map(([module,ps])=>`<div><div class="text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">${esc(module)}</div><div class="grid gap-1">${ps.map(p=>`<label class="flex items-center gap-2 text-xs"><input type="checkbox" class="role-perm-${r.id}" value="${p.id}" ${selected.has(Number(p.id))?'checked':''}> <span>${esc(p.name)}</span></label>`).join('')}</div></div>`).join('')}</div><button type="button" class="ui-btn ui-btn-secondary mt-4 w-full" onclick="saveRolePermissions(${r.id})">Save ${esc(r.name)} permissions</button></article>`}).join('');fillRoleSelect();}catch(e){document.getElementById('roles-grid').innerHTML=`<p>${esc(e.message)}</p>`}}
window.saveRolePermissions=async roleId=>{const ids=[...document.querySelectorAll(`.role-perm-${roleId}:checked`)].map(x=>Number(x.value));try{await api(`settings/roles/${roleId}/permissions`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({permission_ids:ids})});toast('Role permissions updated');loadRoles()}catch(e){toast(e.message)}};
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
