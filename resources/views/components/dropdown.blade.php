{{-- <x-dropdown :label="__('home.city')" :current="$city->label()" :items="[['label' => 'Bălți', 'href' => '…', 'active' => false], …]" />
     Pill button that opens a rounded list of links (the native <select> list can't be styled).
     Disclosure pattern: button aria-expanded + links. ↑/↓ move between items, Esc closes.
     Use it for pickers that navigate (city on home / match list); form fields keep <x-field type="select">. --}}
@props(['label', 'current', 'items' => [], 'align' => 'end'])
@php($uid = 'dd-'.\Illuminate\Support\Str::random(6))
<div {{ $attributes->class(['dropdown', 'dropdown--start' => $align === 'start']) }}
     x-data="{ open: false,
               toggle() { this.open = !this.open; if (this.open) this.$nextTick(() => this.focusItem(this.$refs.list.querySelector('[aria-current=true]') ? [...this.$refs.list.querySelectorAll('a')].indexOf(this.$refs.list.querySelector('[aria-current=true]')) : 0)); },
               close(back = true) { this.open = false; if (back) this.$refs.button.focus(); },
               focusItem(i) { const items = [...this.$refs.list.querySelectorAll('a')]; items[(i + items.length) % items.length]?.focus(); },
               move(step) { const items = [...this.$refs.list.querySelectorAll('a')]; this.focusItem(items.indexOf(document.activeElement) + step); } }"
     x-on:keydown.escape.prevent="close()" x-on:click.outside="close(false)">
    <button type="button" class="dropdown__button" x-ref="button" x-on:click="toggle()"
            aria-haspopup="true" :aria-expanded="open" aria-controls="{{ $uid }}">
        <span class="sr-only">{{ $label }}:</span>
        <span class="dropdown__current">{{ $current }}</span>
        <x-icon.chevron-down class="icon dropdown__chevron" />
    </button>

    <ul id="{{ $uid }}" class="dropdown__list" role="list" x-ref="list" x-show="open" x-cloak
        x-transition:enter="dropdown-enter" x-transition:enter-start="dropdown-from" x-transition:enter-end="dropdown-to"
        x-on:keydown.down.prevent="move(1)" x-on:keydown.up.prevent="move(-1)"
        x-on:keydown.home.prevent="focusItem(0)" x-on:keydown.end.prevent="focusItem(-1)">
        @foreach ($items as $item)
            <li>
                <a href="{{ $item['href'] }}" class="dropdown__item" @if ($item['active'] ?? false) aria-current="true" @endif>
                    <span>{{ $item['label'] }}</span>
                    @if ($item['active'] ?? false)<x-icon.check class="icon dropdown__check" />@endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
