<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('defaults settings to USD and accepts CAD', function () {
    $this->getJson('/api/settings')->assertOk()->assertJsonPath('data.default_currency', 'USD');
    $this->putJson('/api/settings', ['default_currency' => 'CAD'])
        ->assertOk()->assertJsonPath('data.default_currency', 'CAD');
});


it('rejects unsupported IDR settings', function () {
    $this->putJson('/api/settings', ['default_currency' => 'IDR'])
        ->assertUnprocessable()->assertJsonValidationErrors('default_currency');
});
