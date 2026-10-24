{{-- <x-locale-switch /> — RO / RU. In the header on laptops; in Profil and on login / register on phones (no header there). --}}
<div {{ $attributes->class('locale-switch') }} role="group" aria-label="{{ __('ui.language') }}">
    @foreach (\App\Http\Middleware\SetLocale::SUPPORTED as $locale)
        <a href="{{ route('locale', $locale) }}" lang="{{ $locale }}" hreflang="{{ $locale }}"
           aria-current="{{ app()->getLocale() === $locale ? 'true' : 'false' }}">{{ strtoupper($locale) }}</a>
    @endforeach
</div>
