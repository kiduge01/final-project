<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-heading font-semibold text-royal-900">AI-Assisted Administration</h1>
        <p class="text-mist-600 text-sm mt-1">Ask administrative questions and generate data-grounded church summaries.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <section class="lg:col-span-2 bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
            <div id="ai-answer" class="min-h-56 rounded-xl bg-mist-50 border border-mist-200 p-5 text-mist-700 leading-7">
                Ask a question to get a response based on approved church data functions.
            </div>
            <form id="ai-form" class="mt-4 flex gap-2">
                <input id="ai-question" required maxlength="500" class="flex-1 rounded-xl border border-mist-200 px-4 py-3" placeholder="e.g. How many active members do we have?">
                <button class="px-5 py-3 rounded-xl bg-royal-600 text-white font-semibold">Ask</button>
            </form>
            <button id="summary-btn" class="mt-3 text-sm font-semibold text-royal-700 hover:underline">Generate church summary</button>
        </section>

        <aside class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
            <h2 class="font-semibold text-mist-900">Example questions</h2>
            <div class="mt-3 space-y-2 text-sm">
                <?php foreach ([
                    'How many active members do we have?',
                    'How many guests require follow-up?',
                    'What is the attendance this month?',
                    'What is the giving for this month?',
                    'How many assets are registered?',
                    'Prepare an administrative summary.'
                ] as $q): ?>
                    <button type="button" class="example-question w-full text-left rounded-lg border border-mist-200 px-3 py-2 hover:bg-mist-50"><?= htmlspecialchars($q) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="mt-5 rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                AI access is controlled by application functions. The assistant does not execute unrestricted SQL generated from user prompts.
            </div>
        </aside>
    </div>
</div>
<script>
const aiUrl = (path) => `${BASE_URL}/api/v1/${path}`;
async function askAI(question) {
    const box = document.getElementById('ai-answer');
    box.textContent = 'Analyzing approved church data...';
    try {
        const r = await fetch(aiUrl('ai/query'), {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({question})});
        const j = await r.json();
        box.textContent = j?.data?.answer || j?.message || 'No response available.';
    } catch(e) { box.textContent = 'Unable to contact the AI service.'; }
}
document.getElementById('ai-form').addEventListener('submit', e => { e.preventDefault(); askAI(document.getElementById('ai-question').value.trim()); });
document.getElementById('summary-btn').addEventListener('click', async () => { document.getElementById('ai-answer').textContent='Generating summary...'; const r=await fetch(aiUrl('ai/summary')); const j=await r.json(); document.getElementById('ai-answer').textContent=j?.data?.answer||j?.message||'No summary available.'; });
document.querySelectorAll('.example-question').forEach(b => b.addEventListener('click', () => { document.getElementById('ai-question').value=b.textContent; askAI(b.textContent); }));
</script>
