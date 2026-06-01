<?php

it('redirects the root route to the admin dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin/dashboard');
});
