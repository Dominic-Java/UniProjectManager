<?php

namespace Tests\Feature;

use App\Mail\AccountWelcomeMail;
use App\Models\User;
use App\Services\Auth\PasswordResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SettingsWelcomeAndAdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creating_account_sends_welcome_email(): void
    {
        Mail::fake();

        config()->set('uniprojectmanager.admin_emails', ['admin@ulbs.ro']);
        $admin = User::factory()->create([
            'email' => 'admin@ulbs.ro',
            'role' => 'profesor',
        ]);

        $this->actingAs($admin)
            ->post(route('settings.users.store'), [
                'first_name' => 'Student',
                'last_name' => 'Nou',
                'email' => 'student.nou@ulbs.ro',
                'password' => 'ParolaNoua123!',
                'password_confirmation' => 'ParolaNoua123!',
                'role' => 'student',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(AccountWelcomeMail::class, function (AccountWelcomeMail $mail) use ($admin): bool {
            $queryString = parse_url($mail->setupUrl, PHP_URL_QUERY);
            if (!is_string($queryString) || $queryString === '') {
                return false;
            }

            parse_str($queryString, $queryParams);
            $token = $queryParams['token'] ?? null;
            $email = $queryParams['email'] ?? null;
            if (!is_string($token) || $token === '' || !is_string($email)) {
                return false;
            }

            $record = DB::table('password_reset_tokens')
                ->where('email', 'student.nou@ulbs.ro')
                ->first();

            return $mail->hasTo('student.nou@ulbs.ro')
                && $mail->createdBy?->id === $admin->id
                && $mail->user->email === 'student.nou@ulbs.ro'
                && $email === 'student.nou@ulbs.ro'
                && $mail->expiresInMinutes === PasswordResetService::TOKEN_EXPIRATION_MINUTES
                && $record !== null
                && hash('sha256', $token) === $record->token;
        });
    }

    public function test_admin_cannot_delete_another_admin(): void
    {
        config()->set('uniprojectmanager.admin_emails', ['admin.one@ulbs.ro', 'admin.two@ulbs.ro']);
        $adminOne = User::factory()->create([
            'email' => 'admin.one@ulbs.ro',
            'role' => 'profesor',
        ]);
        $adminTwo = User::factory()->create([
            'email' => 'admin.two@ulbs.ro',
            'role' => 'profesor',
        ]);

        $this->actingAs($adminOne)
            ->delete(route('settings.users.destroy', $adminTwo))
            ->assertRedirect()
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $adminTwo->id]);
    }

    public function test_changing_role_regenerates_member_code_with_matching_prefix(): void
    {
        config()->set('uniprojectmanager.admin_emails', ['admin@ulbs.ro']);
        $admin = User::factory()->create([
            'email' => 'admin@ulbs.ro',
            'role' => 'profesor',
            'member_code' => 'PROF-ADM001',
        ]);

        $user = User::factory()->create([
            'email' => 'student.switch@ulbs.ro',
            'role' => 'student',
            'member_code' => 'STU-ABC123',
        ]);

        $this->actingAs($admin)
            ->put(route('settings.users.update', $user), [
                'role' => 'profesor',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('profesor', $user->role);
        $this->assertStringStartsWith('PROF-', $user->member_code);
        $this->assertNotSame('STU-ABC123', $user->member_code);

        $firstUpdatedCode = $user->member_code;

        $this->actingAs($admin)
            ->put(route('settings.users.update', $user), [
                'role' => 'student',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('student', $user->role);
        $this->assertStringStartsWith('STU-', $user->member_code);
        $this->assertNotSame($firstUpdatedCode, $user->member_code);
    }

    public function test_saving_same_role_fixes_member_code_prefix_when_inconsistent(): void
    {
        config()->set('uniprojectmanager.admin_emails', ['admin@ulbs.ro']);
        $admin = User::factory()->create([
            'email' => 'admin@ulbs.ro',
            'role' => 'profesor',
            'member_code' => 'PROF-ADM001',
        ]);

        $user = User::factory()->create([
            'email' => 'prof.inconsistent@ulbs.ro',
            'role' => 'profesor',
            'member_code' => 'STU-OLD123',
        ]);

        $this->actingAs($admin)
            ->put(route('settings.users.update', $user), [
                'role' => 'profesor',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('profesor', $user->role);
        $this->assertStringStartsWith('PROF-', $user->member_code);
        $this->assertNotSame('STU-OLD123', $user->member_code);
    }
}
