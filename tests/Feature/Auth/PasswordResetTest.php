<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $this->markTestSkipped('Сброс пароля отключён в приложении.');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $this->markTestSkipped('Сброс пароля отключён в приложении.');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $this->markTestSkipped('Сброс пароля отключён в приложении.');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $this->markTestSkipped('Сброс пароля отключён в приложении.');
    }
}
