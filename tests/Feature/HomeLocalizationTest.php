<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_home_is_fully_english_when_locale_is_en(): void
    {
        $professor = User::factory()->create([
            'role' => 'profesor',
            'locale_preference' => 'en',
        ]);

        $this->actingAs($professor)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Quick actions')
            ->assertSee('Monitoring dashboard')
            ->assertDontSee('Actiuni rapide')
            ->assertDontSee('Panou de monitorizare');
    }

    public function test_student_home_is_fully_english_when_locale_is_en(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'locale_preference' => 'en',
        ]);

        $this->actingAs($student)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Your courses')
            ->assertSee('Academic calendar')
            ->assertDontSee('Cursurile tale')
            ->assertDontSee('Calendar academic');
    }
}
