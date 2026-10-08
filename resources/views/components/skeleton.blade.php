{{-- <x-skeleton variant="line|match-card|sport-card" :count="3" />
     Placeholder with the same shape as the real content, so nothing jumps when it loads. --}}
@props(['variant' => 'line', 'count' => 1])
<div {{ $attributes->class(['skeleton-group', 'skeleton-group--'.$variant]) }} role="status">
    <span class="sr-only">{{ __('components.skeleton.loading') }}</span>
    @for ($i = 0; $i < $count; $i++)
        @if ($variant === 'match-card')
            <div class="skeleton-card" aria-hidden="true">
                <div class="skeleton-row">
                    <span class="skeleton skeleton--icon"></span>
                    <span class="skeleton-col"><span class="skeleton skeleton--title"></span><span class="skeleton skeleton--text" style="width: 55%"></span></span>
                </div>
                <span class="skeleton skeleton--text" style="width: 80%"></span>
                <span class="skeleton skeleton--bar"></span>
            </div>
        @elseif ($variant === 'sport-card')
            <div class="skeleton-card skeleton-card--sport" aria-hidden="true">
                <span class="skeleton skeleton--icon-lg"></span>
                <span class="skeleton skeleton--text" style="width: 60%"></span>
                <span class="skeleton skeleton--text" style="width: 40%"></span>
            </div>
        @else
            <span class="skeleton skeleton--text" aria-hidden="true" style="width: {{ [92, 76, 84, 64][$i % 4] }}%"></span>
        @endif
    @endfor
</div>
