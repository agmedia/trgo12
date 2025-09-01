<?php

it('redirects to login when accessing admin dashboard', function () {
    $response = $this->get('/admin');
    $response->assertStatus(302);
});
