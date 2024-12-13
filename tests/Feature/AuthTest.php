<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_a_user_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'gouda',
            'email' => 'gouda@gmail.com',
            'password' => '12345678',
            'role' => 'author',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'gouda@gmail.com',
        ]);
    }

    /** @test */
    public function it_logs_in_a_user_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                    'token',
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'itest@gmail.com',
            'password' => '1234567',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 400,
            ]);
    }

    /** @test */
    public function it_logs_out_a_user_successfully()
    {
        $user = User::factory()->create();

        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Logout successful',
            ]);
    }


}
