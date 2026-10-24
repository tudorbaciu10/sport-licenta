{{-- Day + time picker of the create form (state lives in createMatch, public/assets/js/create-match.js).
     Laptops: the right column. Phones use the two parts on their own (week strip + slots, month in a sheet). $id keeps ids unique. --}}
<div class="when-picker">
    @include('rooms.partials.when-calendar', ['id' => $id])
    @include('rooms.partials.when-slots', ['id' => $id])
</div>