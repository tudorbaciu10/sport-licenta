<?php

use App\Support\Consent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function consentCookie(bool $preferences, int $version = Consent::VERSION): string
{
    return json_encode(['v' => $version, 'preferences' => $preferences, 'date' => '2026-10-08']);
}

it('shows the cookie banner and the footer links on every page', function () {
    $this->get('/rooms')->assertOk()
        ->assertSee('Ținem la confidențialitatea ta')
        ->assertSee('Acceptă toate')
        ->assertSee('Respinge toate')
        ->assertSee('Personalizează')
        ->assertSee('href="'.route('legal.cookies').'"', false)
        ->assertSee('href="'.route('legal.privacy').'"', false)
        ->assertSee('Setări cookie');
});

it('lists the real cookies on the cookie policy page, in Romanian and Russian', function () {
    $this->get('/cookies')->assertOk()
        ->assertSee('Politica cookie')
        ->assertSee(config('session.cookie'))
        ->assertSee('XSRF-TOKEN')
        ->assertSee(Consent::COOKIE)
        ->assertSee(Consent::LOCALE_COOKIE)
        ->assertSee(config('session.lifetime').' minute');

    $this->withSession(['locale' => 'ru'])->get('/cookies')->assertOk()
        ->assertSee('Политика cookie')->assertDontSee('legal.cookies.');
});

it('renders the privacy policy with the contact email', function () {
    $this->get('/confidentialitate')->assertOk()
        ->assertSee('Politica de confidențialitate')
        ->assertSee('mailto:'.config('app.contact_email'), false)
        ->assertSee('GDPR');

    $this->withSession(['locale' => 'ru'])->get('/confidentialitate')->assertOk()
        ->assertSee('Политика конфиденциальности')->assertDontSee('legal.privacy.');
});

it('remembers the language in a cookie when preferences are accepted', function () {
    $cookie = $this->withUnencryptedCookie(Consent::COOKIE, consentCookie(true))
        ->get('/locale/ru')->assertRedirect()
        ->assertPlainCookie(Consent::LOCALE_COOKIE, 'ru')
        ->getCookie(Consent::LOCALE_COOKIE, false);

    expect($cookie->isHttpOnly())->toBeFalse();   // the banner can delete it when consent is withdrawn
});

it('does not set the language cookie when preferences are rejected', function () {
    $this->withUnencryptedCookie(Consent::COOKIE, consentCookie(false))
        ->get('/locale/ru')->assertRedirect()
        ->assertCookieMissing(Consent::LOCALE_COOKIE);
});

it('does not set the language cookie before any choice', function () {
    $this->get('/locale/ru')->assertRedirect()->assertCookieMissing(Consent::LOCALE_COOKIE);
});

it('uses the remembered language for a new session only with consent', function () {
    $this->withUnencryptedCookie(Consent::COOKIE, consentCookie(true))
        ->withUnencryptedCookie(Consent::LOCALE_COOKIE, 'ru')
        ->get('/rooms')->assertSee('lang="ru"', false);

    $this->withUnencryptedCookie(Consent::COOKIE, consentCookie(false))
        ->withUnencryptedCookie(Consent::LOCALE_COOKIE, 'ru')
        ->get('/rooms')->assertSee('lang="ro"', false);

    // An old banner version is ignored, so the visitor is asked again
    $this->withUnencryptedCookie(Consent::COOKIE, consentCookie(true, 0))
        ->withUnencryptedCookie(Consent::LOCALE_COOKIE, 'ru')
        ->get('/rooms')->assertSee('lang="ro"', false);
});
