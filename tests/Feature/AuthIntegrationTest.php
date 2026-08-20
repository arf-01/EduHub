<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class AuthIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_registration_page_is_accessible(): void
    {
        $response = $this->get(route('registration'));
        $response->assertStatus(200);
    }

    public function test_teacher_can_register_successfully(): void
    {
        $userData = [
            'name' => 'Professor Smith',
            'email' => 'prof.smith.' . uniqid() . '@example.com',
            'password' => 'secret123',
            'room_name' => 'ROOM_' . strtoupper(uniqid()),
        ];

        $response = $this->post(route('registration.post'), $userData);

        $response->assertRedirect(route('teacher.view'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'room_name' => $userData['room_name'],
            'name' => 'Professor Smith',
        ]);
    }

    public function test_teacher_cannot_register_with_duplicate_email(): void
    {
        $existing = User::factory()->create([
            'email' => 'duplicate@example.com',
            'room_name' => 'ROOM_EXISTING',
        ]);

        $response = $this->post(route('registration.post'), [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'secret123',
            'room_name' => 'ROOM_NEW',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_teacher_cannot_register_with_duplicate_room_name(): void
    {
        $existing = User::factory()->create([
            'email' => 'unique1@example.com',
            'room_name' => 'SHARED_ROOM',
        ]);

        $response = $this->post(route('registration.post'), [
            'name' => 'New User',
            'email' => 'unique2@example.com',
            'password' => 'secret123',
            'room_name' => 'SHARED_ROOM',
        ]);

        $response->assertSessionHasErrors('room_name');
    }

    public function test_teacher_can_login_with_valid_credentials(): void
    {
        $email = 'teacher.' . uniqid() . '@example.com';
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => $email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('teacher.view'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_teacher_login_fails_with_invalid_password(): void
    {
        $email = 'teacher.' . uniqid() . '@example.com';
        User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_teacher_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('logout'));

        $response->assertRedirect('/TorS?role=teacher');
        $this->assertGuest();
    }
}
