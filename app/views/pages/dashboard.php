<?php
$statMembers=(int)($stats['members']??0); $statGuests=(int)($stats['guests']??0); $statAttendance=(int)($stats['attendance']??0); $income=(float)($stats['income']??0); $expenses=(float)($stats['expenses']??0);
?>
<div class="mb-6"><h1 class="text-3xl font-heading font-semibold text-royal-900">Church Administration Dashboard</h1><p class="text-mist-600 text-sm mt-1">A centralized view of members, guests, attendance, giving and assets.</p></div>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
<?php foreach ([['Active Members',$statMembers,'text-blue-700'],['Guests',$statGuests,'text-emerald-700'],['Attendance',$statAttendance,'text-purple-700'],['Monthly Giving','TZS '.number_format($income,0),'text-emerald-700'],['Monthly Expenses','TZS '.number_format($expenses,0),'text-red-700']] as $c): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5"><p class="text-xs uppercase tracking-wider text-mist-500"><?= htmlspecialchars($c[0]) ?></p><p class="text-2xl font-bold <?= $c[2] ?> mt-2"><?= htmlspecialchars((string)$c[1]) ?></p></div>
<?php endforeach; ?>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
<section class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5"><h2 class="text-lg font-semibold text-mist-900">Quick Actions</h2><div class="grid grid-cols-2 gap-3 mt-4"><a class="rounded-xl bg-royal-50 p-4 text-royal-800 font-semibold" href="<?= \App\Core\Route::get('members') ?>">Manage Members</a><a class="rounded-xl bg-emerald-50 p-4 text-emerald-800 font-semibold" href="<?= \App\Core\Route::get('guests') ?>">Register Guest</a><a class="rounded-xl bg-purple-50 p-4 text-purple-800 font-semibold" href="<?= \App\Core\Route::get('attendance') ?>">Record Attendance</a><a class="rounded-xl bg-amber-50 p-4 text-amber-800 font-semibold" href="<?= \App\Core\Route::get('ai') ?>">Ask AI Assistant</a></div></section>
<section class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5"><h2 class="text-lg font-semibold text-mist-900">AI-Assisted Administration</h2><p class="text-sm text-mist-600 mt-2 leading-6">Use the controlled AI assistant to ask questions about approved church records and generate administrative summaries.</p><a href="<?= \App\Core\Route::get('ai') ?>" class="inline-flex mt-4 px-4 py-2.5 rounded-xl bg-royal-600 text-white font-semibold">Open AI Assistant</a></section>
</div>
