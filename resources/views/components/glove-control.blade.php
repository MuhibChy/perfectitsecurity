{{-- Foreground glove control (authenticated users only). Guests always see
     moving mode and get no control. Single persistence request per user
     action — never during dragging. Hidden entirely if WebGL is down. --}}
@auth
<div id="glove-control" data-glove-ui class="fixed left-4 bottom-4 z-40 hidden" aria-label="3D glove controls">
    <div class="dark-island rounded-2xl border border-[#00FF00]/30 bg-black px-3.5 py-3 w-52">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-400">3D Glove</span>
            <span class="flex items-center gap-1.5 text-[11px] font-medium text-white">
                <span id="glove-dot" class="w-2 h-2 rounded-full bg-[#00FF00] animate-pulse inline-block"></span>
                <span id="glove-status">Moving</span>
            </span>
        </div>
        <div id="glove-hint" class="hidden text-[11px] leading-snug text-gray-500 mb-2">Drag the glove to your preferred position, then save.</div>
        <div id="glove-msg" class="hidden text-[11px] leading-snug mb-2" role="status"></div>
        <div class="flex flex-wrap gap-1.5">
            <button id="glove-fix" type="button" class="px-3 py-1.5 text-[11px] font-medium border border-[#00FF00]/30 text-white hover:bg-[#00FF00]/10 transition-colors rounded">Fix Position</button>
            <button id="glove-save" type="button" class="px-3 py-1.5 text-[11px] font-medium bg-[#00FF00] text-black hover:bg-[#00FF00]/80 transition-colors rounded hidden">Save Position</button>
            <button id="glove-cancel" type="button" class="px-3 py-1.5 text-[11px] font-medium border border-gray-700 text-gray-400 hover:bg-gray-800 transition-colors rounded hidden">Cancel</button>
            <button id="glove-resume" type="button" class="px-3 py-1.5 text-[11px] font-medium border border-[#00FF00]/30 text-white hover:bg-[#00FF00]/10 transition-colors rounded hidden">Resume Moving</button>
            <button id="glove-reset" type="button" class="px-3 py-1.5 text-[11px] font-medium border border-gray-700 text-gray-400 hover:bg-gray-800 transition-colors rounded hidden">Reset Position</button>
        </div>
    </div>
</div>
<script>
(function () {
    const root = document.getElementById('glove-control');
    if (!root) return;
    // app.js (deferred module) builds the scene after parsing — poll briefly.
    let tries = 0;
    const timer = setInterval(() => {
        const scene = window._global3D;
        if (scene && scene.objects && scene.objects.shieldGroup) {
            clearInterval(timer);
            initGloveControl(scene);
        } else if (++tries > 40) {
            clearInterval(timer); // No WebGL glove → no control, site untouched.
        }
    }, 250);

function initGloveControl(scene) {
    root.classList.remove('hidden');

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const el = (id) => document.getElementById(id);
    const statusEl = el('glove-status'), dot = el('glove-dot'), hint = el('glove-hint'), msg = el('glove-msg');
    const btn = { fix: el('glove-fix'), save: el('glove-save'), cancel: el('glove-cancel'), resume: el('glove-resume'), reset: el('glove-reset') };
    let mode = document.body.getAttribute('data-glove-mode') === 'fixed' ? 'fixed' : 'moving';
    let positioning = false;

    function showMsg(text, ok) {
        msg.textContent = text;
        msg.classList.remove('hidden', 'text-[#00FF00]', 'text-red-400');
        msg.classList.add(ok ? 'text-[#00FF00]' : 'text-red-400');
        clearTimeout(showMsg.t);
        showMsg.t = setTimeout(() => msg.classList.add('hidden'), 4000);
    }

    function render() {
        statusEl.textContent = positioning ? 'Positioning' : (mode === 'fixed' ? 'Fixed' : 'Moving');
        dot.className = 'w-2 h-2 rounded-full inline-block ' + (positioning ? 'bg-yellow-400 animate-pulse' : (mode === 'fixed' ? 'bg-blue-400' : 'bg-[#00FF00] animate-pulse'));
        hint.classList.toggle('hidden', !positioning);
        btn.fix.classList.toggle('hidden', positioning || mode === 'fixed');
        btn.save.classList.toggle('hidden', !positioning);
        btn.cancel.classList.toggle('hidden', !positioning);
        btn.resume.classList.toggle('hidden', positioning || mode !== 'fixed');
        btn.reset.classList.toggle('hidden', positioning || mode !== 'fixed');
    }

    async function persist(payload, method, url) {
        const res = await fetch(url, {
            method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: payload ? JSON.stringify(payload) : undefined,
        });
        if (!res.ok) throw new Error('Request failed (' + res.status + ')');
        return res.json();
    }

    btn.fix.addEventListener('click', () => {
        if (!scene.beginPositioning()) { showMsg('Glove is unavailable right now.', false); return; }
        positioning = true;
        render();
    });

    btn.cancel.addEventListener('click', () => {
        scene.cancelPositioning();
        positioning = false;
        render();
    });

    btn.save.addEventListener('click', async () => {
        const target = scene.positioningTarget();
        if (!target) { showMsg('Drag the glove first, then save.', false); return; }
        try {
            const saved = await persist({ mode: 'fixed', x: target.x, y: target.y }, 'PATCH', '{{ route('glove-preference.update') }}');
            scene.setGloveMode('fixed', saved.x, saved.y);
            document.body.setAttribute('data-glove-mode', 'fixed');
            document.body.setAttribute('data-glove-x', saved.x);
            document.body.setAttribute('data-glove-y', saved.y);
            mode = 'fixed';
            positioning = false;
            render();
            showMsg('Position saved.', true);
        } catch (e) {
            showMsg('Could not save. Previous position kept — try again.', false);
        }
    });

    btn.resume.addEventListener('click', async () => {
        try {
            const cur = scene.gloveCtl;
            await persist({ mode: 'moving', x: cur.x, y: cur.y }, 'PATCH', '{{ route('glove-preference.update') }}');
            scene.setGloveMode('moving', cur.x, cur.y);
            document.body.setAttribute('data-glove-mode', 'moving');
            mode = 'moving';
            positioning = false;
            render();
            showMsg('Automatic movement resumed.', true);
        } catch (e) {
            showMsg('Could not resume. Try again.', false);
        }
    });

    btn.reset.addEventListener('click', async () => {
        try {
            await persist(null, 'POST', '{{ route('glove-preference.reset') }}');
            scene.setGloveMode('moving', null, null);
            document.body.setAttribute('data-glove-mode', 'moving');
            document.body.removeAttribute('data-glove-x');
            document.body.removeAttribute('data-glove-y');
            mode = 'moving';
            positioning = false;
            render();
            showMsg('Default configuration restored.', true);
        } catch (e) {
            showMsg('Could not reset. Try again.', false);
        }
    });

    render();
} // initGloveControl
})();
</script>
@endauth
