<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_root_to_login()
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }
}
