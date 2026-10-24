{{-- <x-action-bar>
         <x-slot:summary>…price…</x-slot:summary>      optional, left
         <x-slot:actions>…one or two buttons…</x-slot:actions>
     </x-action-bar>
     Phone-only bar fixed to the bottom (Figma "Bottom Bar"); hidden from 1024px. Use on pages that set
     @section('no_tabbar', true). One button: summary left, button right. Two buttons: they share the width. --}}
<div {{ $attributes->class('action-bar-m m-only') }}>
    @isset($summary)
        <div class="action-bar-m__summary">{{ $summary }}</div>
    @endisset
    <div class="action-bar-m__actions">{{ $actions }}</div>
</div>
