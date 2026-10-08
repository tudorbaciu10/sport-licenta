<?php

use App\Models\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the styleguide in Romanian by default and switches to Russian', function () {
    $this->get('/styleguide')->assertOk()->assertSee('Ghid de stil')->assertSee('lang="ro"', false);

    $this->get(route('locale', 'ru'))->assertRedirect();

    $this->get('/styleguide')->assertOk()->assertSee('Руководство по стилю')->assertSee('lang="ru"', false);
});

it('rejects unsupported locales', function () {
    $this->get('/locale/en')->assertNotFound();
});

it('keeps sport colors in one place: the model constant mirrors the CSS tokens', function () {
    $tokens = file_get_contents(public_path('assets/css/tokens.css'));

    foreach (Sport::COLORS as $slug => $color) {
        $var = (new Sport(['slug' => $slug]))->cssVar();
        expect($tokens)->toMatch('/'.preg_quote($var, '/').':\s*'.preg_quote($color, '/').';/i');
    }
});
