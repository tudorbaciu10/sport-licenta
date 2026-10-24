{{-- <x-bottom-nav :unread="3" /> — phone tab bar (hidden ≥ 1024px, where the top nav takes over).
     "Meciurile mele" shows a short label so all five fit on one line at 360px; the full name stays for screen readers.
     $unread: unread chat messages; a dot on "Ale mele" (there is no header with the chat icon on phones). --}}
@props(['unread' => 0])
<nav {{ $attributes->class('bottom-nav') }} aria-label="{{ __('ui.main_nav') }}">
    @foreach (\App\Support\Navigation::items() as $item)
        <a href="{{ $item['url'] }}" @class(['bottom-nav__create' => $item['key'] === 'create']) @if ($item['active']) aria-current="page" @endif>
            <span class="bottom-nav__icon">
                <x-dynamic-component :component="'icon.'.$item['icon']" />
                @if ($item['key'] === 'my_matches' && $unread > 0)
                    <span class="bottom-nav__dot" aria-hidden="true"></span>
                @endif
            </span>
            @if ($item['key'] === 'my_matches')
                <span aria-hidden="true">{{ __('ui.nav_short.my_matches') }}</span>
                <span class="sr-only">{{ __('ui.nav.my_matches') }}@if ($unread > 0), {{ trans_choice('chat.nav_unread', $unread, ['count' => $unread]) }}@endif</span>
            @else
                <span>{{ __('ui.nav.'.$item['key']) }}</span>
            @endif
        </a>
    @endforeach
</nav>
