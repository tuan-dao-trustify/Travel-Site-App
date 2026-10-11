<?php

use App\Models\User;

test('guests get a 401 json response from the me endpoint', function () {
    $this->getJson('/me')->assertUnauthorized();
});

test('the me endpoint returns the logged in user as json', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/me')
        ->assertOk()
        ->assertExactJson(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]);
});
