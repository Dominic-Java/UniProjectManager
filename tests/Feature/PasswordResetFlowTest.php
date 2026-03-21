<?php

namespace Tests\Feature;

use App\Mail\PasswordResetLinkMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_email_and_stores_token(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'student@example.com',
            'role' => 'student',
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])
            ->assertRedirect()
            ->assertSessionHas('status');

        Mail::assertSent(PasswordResetLinkMail::class, function (PasswordResetLinkMail $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_admin_can_send_password_reset_link_from_settings(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'profesor',
        ]);

        $target = User::factory()->create([
            'email' => 'target@example.com',
            'role' => 'student',
        ]);

        config()->set('uniprojectmanager.admin_emails', [$admin->email]);

        $this->actingAs($admin)
            ->post(route('settings.users.password-reset-link', $target))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(PasswordResetLinkMail::class, function (PasswordResetLinkMail $mail) use ($target): bool {
            return $mail->hasTo($target->email);
        });

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $target->email,
        ]);
    }

    public function test_authenticated_user_can_open_reset_page_from_email_link(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'role' => 'student',
        ]);

        $this->actingAs($user)
            ->get(route('password.reset', [
                'token' => 'test-token-123',
                'email' => $user->email,
            ]))
            ->assertOk()
            ->assertSee('Seteaza parola noua');
    }

    public function test_password_reset_logs_out_current_session_and_updates_password(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'role' => 'student',
            'password_hash' => Hash::make('OldPass123!'),
        ]);

        $plainToken = 'reset-token-abc';

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', $plainToken),
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('password.update'), [
                'token' => $plainToken,
                'email' => $user->email,
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertGuest();
        $this->assertTrue(Hash::check('NewPass123!', $user->fresh()->password_hash));
    }
}
