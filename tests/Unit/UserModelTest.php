<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_user_has_fillable_attributes(): void
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'room_name' => 'ROOM101',
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('ROOM101', $user->room_name);
    }

    public function test_user_hidden_attributes(): void
    {
        $user = new User([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'remember_token' => 'token123',
        ]);

        $array = $user->toArray();
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
}
