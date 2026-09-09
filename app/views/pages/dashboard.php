<?php $m=(int)($stats['members']??0);$g=(int)($stats['guests']??0);$a=(int)($stats['attendance']??0);$giving=(float)($stats['income']??0); ?>
<div class="ui-fade-in">
<div class="ui-page-head"><div><h1 class="ui-title">Good <?= date('H')<12?'morning':(date('H')<18?'afternoon':'evening') ?>, Administrator</h1><p class="ui-subtitle">Here is a clear view of church administration for <?= date('F Y') ?>.</p></div><a class="ui-btn ui-btn-primary" href="<?= \App\Core\Route::get('reports') ?>">Open Reports</a></div>
<div class="ui-kpis">
<?php foreach([['Total Members',number_format($m),'Active member records'],['Guests',number_format($g),'Registered guest records'],['Attendance',number_format($a),'Current month'],['Church Giving','TZS '.number_format($giving,0),'Current month']] as $x): ?>
<div class="ui-card ui-kpi"><span class="ui-kpi-dot"></span><div class="ui-kpi-label"><?=htmlspecialchars($x[0])?></div><div class="ui-kpi-value"><?=htmlspecialchars((string)$x[1])?></div><div class="ui-kpi-note"><?=htmlspecialchars($x[2])?></div></div><?php endforeach; ?>
</div>
<div class="ui-grid-2">
<section class="ui-card"><div class="flex items-start justify-between"><div><h2 class="ui-section-title">Attendance trend</h2><p class="ui-section-sub">Visual overview for the current reporting period</p></div><span class="ui-pill ui-pill-blue">This month</span></div><div class="ui-chart" aria-label="Attendance trend chart"><div class="ui-bar" style="height:44%"></div><div class="ui-bar" style="height:62%"></div><div class="ui-bar" style="height:55%"></div><div class="ui-bar" style="height:78%"></div><div class="ui-bar" style="height:68%"></div><div class="ui-bar" style="height:88%"></div></div><div class="flex justify-between text-[10px] text-mist-500 pt-3 px-4"><span>Earlier Ibada</span><span>Latest Ibada</span></div></section>
<aside class="ui-card"><h2 class="ui-section-title">Quick actions</h2><p class="ui-section-sub">Common administrative tasks</p><div class="grid grid-cols-1 gap-2 mt-5">
<a class="rounded-xl border border-mist-200 p-3 font-semibold text-sm hover:bg-blue-50 hover:border-blue-200 transition" href="<?= \App\Core\Route::get('events') ?>">＋ Prepare Event / Ibada</a>
<a class="rounded-xl border border-mist-200 p-3 font-semibold text-sm hover:bg-blue-50 hover:border-blue-200 transition" href="<?= \App\Core\Route::get('attendance') ?>">✓ Record Attendance</a>
<a class="rounded-xl border border-mist-200 p-3 font-semibold text-sm hover:bg-blue-50 hover:border-blue-200 transition" href="<?= \App\Core\Route::get('members') ?>">＋ Register Member / Guest</a>
<button type="button" class="rounded-xl border border-mist-200 p-3 text-left font-semibold text-sm hover:bg-purple-50 hover:border-purple-200 transition" onclick="toggleAIChat(true)">✦ Ask Church Assistant</button></div></aside>
</div>
<div class="ui-card mt-5"><div class="flex items-center justify-between"><div><h2 class="ui-section-title">Administrative workspace</h2><p class="ui-section-sub">Dashboard, Reports and AI use the same approved church data services.</p></div><div class="flex gap-2"><span class="ui-pill ui-pill-green">Secure</span><span class="ui-pill ui-pill-blue">Role based</span></div></div></div>
</div>
