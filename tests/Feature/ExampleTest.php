<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A guest user should be redirected to the login page.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * An authenticated user should be able to view the dashboard.
     */
    public function test_authenticated_user_can_view_dashboard(): void
    {
        // Create user
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        // Seed a sample country to prevent any query errors
        Country::create([
            'name' => 'United States',
            'code' => 'US',
            'currency' => 'USD',
            'region' => 'Americas',
            'latitude' => 37.0902,
            'longitude' => -95.7129,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Supply Chain Risks');
    }

    /**
     * Test the login page loads successfully.
     */
    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    /**
     * Test user login authentication.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'secret-password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test registration creates a user and redirects to the dashboard.
     */
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => 'admin',
        ]);
    }
}
