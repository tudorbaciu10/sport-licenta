{{-- <x-bottom-nav /> — phone tab bar (hidden ≥ 1024px, where the top nav takes over).
     "Meciurile mele" shows a short label so all five fit on one line at 360px; the full name stays for screen readers. --}}
<nav {{ $attributes->class('bottom-nav') }} aria-label="{{ __('ui.main_nav') }}">
    @foreach (\App\Support\Navigation::items() as $item)
        <a href="{{ $item['url'] }}" @class(['bottom-nav__create' => $item['key'] === 'create']) @if ($item['active']) aria-current="page" @endif>
            <x-dynamic-component :component="'icon.'.$item['icon']" />
            @if ($item['key'] === 'my_matches')
                <span aria-hidden="true">{{ __('ui.nav_short.my_matches') }}</span>
                <span class="sr-only">{{ __('ui.nav.my_matches') }}</span>
            @else
                <span>{{ __('ui.nav.'.$item['key']) }}</span>
            @endif
        </a>
    @endforeach
</nav>
