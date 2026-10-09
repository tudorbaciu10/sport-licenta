{{-- Live preview of the list card while the form is filled (createMatch state). --}}
<section class="preview" aria-labelledby="preview-{{ $id }}">
    <h3 id="preview-{{ $id }}" class="t-headline">{{ __('match.create.preview') }}</h3>
    <div class="match-card" :style="sports[sport] ? `--c: var(${sports[sport].var})` : ''" aria-hidden="true">
        <div class="match-card__head">
            @foreach ($sports as $sport)
                <template x-if="sport == '{{ $sport->id }}'"><x-sport-icon :sport="$sport" size="md" /></template>
            @endforeach
            <span class="preview__noicon" x-show="!sports[sport]"></span>
            <div class="match-card__when">
                <span class="t-title2 tabular" x-text="previewTime()"></span>
                <span class="t-footnote t-secondary" x-text="subline()"></span>
            </div>
            <x-badge status="open" class="match-card__badge" />
        </div>
        <div class="match-card__body">
            <h4 class="t-headline match-card__title" x-text="title || @js(__('match.create.match_title_ph'))"></h4>
            <p class="t-callout t-secondary match-card__meta">
                <x-icon.map-pin class="icon icon--xs" />
                <span x-text="[location, cities[city]?.name].filter(Boolean).join(', ') || @js(__('match.create.where_ph'))"></span>
            </p>
        </div>
        <div class="match-card__spots">
            <div class="progress"><span :style="`width: ${Math.round(100 / Math.max(players, 1))}%`"></span></div>
            <span class="t-footnote tabular match-card__count"><x-icon.users class="icon icon--xs" /> <span x-text="`1 / ${players || 0}`"></span></span>
        </div>
    </div>
</section>
