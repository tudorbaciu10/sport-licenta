{{-- Landing search: one bar with text / date / time + submit, filter pills underneath.
     Plain GET to /rooms, so it works without JS; Alpine adds the pills and the
     "typed a city/sport name → use the real filter" shortcut. Ctrl/⌘ K or "/" focuses it. --}}
<form class="msearch" method="GET" action="{{ $payload['action'] }}" role="search" aria-label="Caută meciuri"
      x-data="matchSearch(@js($payload))" @keydown.window="hotkey($event)" @submit="prepare($event)">
    <div class="msearch-bar">
        <label class="msearch-field msearch-q">
            <svg class="msearch-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/></svg>
            <span class="sr">Oraș, sport sau teren</span>
            <input type="search" name="q" x-ref="q" x-model="q" list="msearch-suggest" autocomplete="off" maxlength="80"
                   placeholder="Oraș, sport sau teren">
            <kbd class="msearch-kbd" x-text="isMac ? '⌘K' : 'Ctrl K'" aria-hidden="true">Ctrl K</kbd>
        </label>
        <datalist id="msearch-suggest">
            @foreach ($payload['cities'] as $city)<option value="{{ $city['name'] }}">@endforeach
            @foreach ($payload['sports'] as $sport)<option value="{{ $sport['name'] }}">@endforeach
        </datalist>

        <label class="msearch-field msearch-date">
            <span class="msearch-label">Data</span>
            <input type="date" name="date" min="{{ $payload['today'] }}">
        </label>
        <label class="msearch-field msearch-time">
            <span class="msearch-label">De la ora</span>
            <input type="time" name="time" step="1800">
        </label>

        <button type="submit" class="msearch-go">
            <svg class="msearch-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/></svg>
            Caută
        </button>
    </div>

    <div class="msearch-pills" role="group" aria-label="Filtre">
        @foreach ($venueTypes as $value => $label)
            <button type="button" class="msearch-pill" :aria-pressed="venue === '{{ $value }}'"
                    @click="venue = venue === '{{ $value }}' ? '' : '{{ $value }}'">{{ $label }}</button>
        @endforeach
        <button type="button" class="msearch-pill" :aria-pressed="free" @click="free = !free">Cu locuri libere</button>
        <span class="msearch-live"><i></i>{{ $liveLabel }}</span>
    </div>

    {{-- Filled from the pills / typed shortcut; disabled when empty so the URL stays clean --}}
    <input type="hidden" name="venue" :value="venue" :disabled="!venue">
    <input type="hidden" name="free" value="1" :disabled="!free">
    <input type="hidden" name="city" :value="city" :disabled="!city">
    <input type="hidden" name="sport" :value="sport" :disabled="!sport">
</form>

@once
    <script>
        function matchSearch(data) {
            const norm = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').trim().toLowerCase();
            return {
                q: '', venue: '', free: false, city: '', sport: '',
                isMac: /Mac|iPhone|iPad/.test(navigator.platform),
                hotkey(e) {
                    const typing = /input|textarea|select/i.test(e.target.tagName);
                    if ((e.key === 'k' || e.key === 'K') && (e.metaKey || e.ctrlKey)) { e.preventDefault(); this.$refs.q.focus(); }
                    else if (e.key === '/' && !typing) { e.preventDefault(); this.$refs.q.focus(); }
                },
                // "Bălți" or "fotbal" typed in the box becomes the exact city/sport filter instead of a text search.
                prepare(e) {
                    const t = norm(this.q);
                    const city = data.cities.find((c) => norm(c.name) === t);
                    const sport = data.sports.find((s) => norm(s.name) === t);
                    this.city = city?.slug ?? '';
                    this.sport = sport?.slug ?? '';
                    // Write the hidden fields directly: the form data is read before Alpine re-renders.
                    for (const [name, value] of [['city', this.city], ['sport', this.sport]]) {
                        const field = e.target.querySelector(`input[type=hidden][name="${name}"]`);
                        field.value = value;
                        field.disabled = !value;
                    }
                    const box = e.target.querySelector('[name="q"]');
                    box.disabled = !t || !!(city || sport);
                    e.target.querySelectorAll('input[type=date], input[type=time]').forEach((i) => { i.disabled = !i.value; });
                    // Re-enable after the browser has read the form, in case the user comes back via history.
                    setTimeout(() => e.target.querySelectorAll('input').forEach((i) => { if (i.type !== 'hidden') i.disabled = false; }));
                },
            };
        }
    </script>
@endonce
