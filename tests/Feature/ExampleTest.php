<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Guests hitting the root are redirected to the login screen.
     */
    public function test_root_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    /**
     * The login screen renders for guests.
     */
    public function test_login_screen_is_accessible(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in', false);
    }
}
