<?php

test('homepage is available before auth pages', function () {
    $response = $this->get('/');

    $response
        ->assertSuccessful()
        ->assertSee('Create Account');
});
