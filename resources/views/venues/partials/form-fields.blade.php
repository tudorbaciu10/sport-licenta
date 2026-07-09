@php($venue = $venue ?? null)

<div>
    <x-input-label for="name" :value="__('venues.field_name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                  :value="old('name', $venue?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="venue_category_id" :value="__('venues.field_category')" />
        <select name="venue_category_id" id="venue_category_id" required
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">—</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('venue_category_id', $venue?->venue_category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('venue_category_id')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="surface" :value="__('venues.field_surface')" />
        <select name="surface" id="surface"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">—</option>
            @foreach (['synthetic', 'grass', 'hard', 'parquet'] as $surface)
                <option value="{{ $surface }}" @selected(old('surface', $venue?->surface) === $surface)>{{ __('venues.surface_'.$surface) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('surface')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="description" :value="__('venues.field_description')" />
    <textarea id="description" name="description" rows="3"
              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $venue?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="city" :value="__('venues.field_city')" />
        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                      :value="old('city', $venue?->city)" required />
        <x-input-error :messages="$errors->get('city')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="country" :value="__('venues.field_country')" />
        <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                      :value="old('country', $venue?->country ?? 'Moldova')" />
        <x-input-error :messages="$errors->get('country')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="locality" :value="__('venues.field_locality')" />
        <x-text-input id="locality" name="locality" type="text" class="mt-1 block w-full"
                      :value="old('locality', $venue?->locality)" />
        <x-input-error :messages="$errors->get('locality')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="address" :value="__('venues.field_address')" />
    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full"
                  :value="old('address', $venue?->address)" />
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="capacity" :value="__('venues.field_capacity')" />
        <x-text-input id="capacity" name="capacity" type="number" min="1" class="mt-1 block w-full"
                      :value="old('capacity', $venue?->capacity)" />
        <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="price_per_hour" :value="__('venues.field_price')" />
        <x-text-input id="price_per_hour" name="price_per_hour" type="number" min="0" step="0.01" class="mt-1 block w-full"
                      :value="old('price_per_hour', $venue?->price_per_hour)" />
        <x-input-error :messages="$errors->get('price_per_hour')" class="mt-2" />
    </div>
    <div class="flex items-end pb-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_indoor" value="1" @checked(old('is_indoor', $venue?->is_indoor))
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span>{{ __('venues.field_indoor') }}</span>
        </label>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="contact_phone" :value="__('venues.field_phone')" />
        <x-text-input id="contact_phone" name="contact_phone" type="text" class="mt-1 block w-full"
                      :value="old('contact_phone', $venue?->contact_phone)" />
        <x-input-error :messages="$errors->get('contact_phone')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="contact_email" :value="__('venues.field_email')" />
        <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full"
                      :value="old('contact_email', $venue?->contact_email)" />
        <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="photo" :value="__('venues.field_photo')" />
    @if ($venue?->photo_path)
        <img src="{{ $venue->photoUrl() }}" alt="" class="h-24 rounded-md my-2 object-cover">
    @endif
    <input type="file" id="photo" name="photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-600">
    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
</div>
