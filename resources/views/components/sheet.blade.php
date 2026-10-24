{{-- <x-sheet name="filters" :title="__('…')">…</x-sheet>                 bottom sheet (phones)
     <x-sheet name="home-calendar" :title="…" inline-desktop>…</x-sheet>  bottom sheet below 1024px, plain inline content from 1024px
     Opened with $dispatch('open-sheet', 'filters'), closed with Esc, the backdrop, the close button or 'close-sheet'.
     Logic: public/assets/js/sheet.js (focus trap, focus return, body scroll lock). --}}
@props(['name', 'title', 'inlineDesktop' => false])
@php($id = 'sheet-'.$name)

@once
    @push('scripts')
        <script src="{{ \App\Support\Asset::url('assets/js/sheet.js') }}"></script>
    @endpush
@endonce

<div {{ $attributes->class(['sheet', 'sheet--inline-desktop' => $inlineDesktop]) }} id="{{ $id }}"
     x-data="sheet(@js($name), @js((bool) $inlineDesktop))" :class="open && 'is-open'"
     x-on:open-sheet.window="openIf($event.detail)" x-on:close-sheet.window="close()"
     x-on:keydown.escape.window="open && close()">
    <div class="sheet__backdrop" x-on:click="close()" aria-hidden="true"></div>
    <div class="sheet__panel" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" tabindex="-1"
         x-ref="panel" x-on:keydown.tab="trap($event)">
        <header class="sheet__head">
            <h2 id="{{ $id }}-title" class="t-headline">{{ $title }}</h2>
            <button type="button" class="sheet__close" x-on:click="close()" aria-label="{{ __('components.sheet.close') }}"><x-icon.x /></button>
        </header>
        <div class="sheet__body">{{ $slot }}</div>
    </div>
</div>
