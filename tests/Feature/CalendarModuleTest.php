<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\User;
use Database\Seeders\CalendarEventSeeder;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionsSeeder::class);
    }

    public function test_admin_can_update_and_delete_manual_calendar_events(): void
    {
        $admin = $this->makeAdminUser();

        $event = CalendarEvent::query()->create([
            'event_date' => '2026-09-15',
            'title' => 'Suspension Due to Weather',
            'event_type' => 'work_suspension',
            'description' => 'Original description.',
            'is_system' => false,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/calendar/{$event->id}", [
                'event_date' => '2026-09-16',
                'title' => 'Suspension Due to Severe Weather',
                'event_type' => 'work_suspension',
                'description' => 'Updated description.',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Suspension Due to Severe Weather')
            ->assertJsonPath('data.event_date', '2026-09-16T00:00:00.000000Z');

        $event->refresh();

        $this->assertSame('Suspension Due to Severe Weather', $event->title);
        $this->assertSame('2026-09-16', $event->event_date?->toDateString());
        $this->assertSame('Updated description.', $event->description);

        $this->actingAs($admin)
            ->deleteJson("/api/calendar/{$event->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Calendar event deleted.');

        $this->assertSoftDeleted('calendar_events', [
            'id' => $event->id,
        ]);
    }

    public function test_admin_can_toggle_manual_calendar_event_active_status(): void
    {
        $admin = $this->makeAdminUser();

        $event = CalendarEvent::query()->create([
            'event_date' => '2026-10-12',
            'title' => 'Special Local Holiday',
            'event_type' => 'local_holiday',
            'description' => 'Can be toggled on and off.',
            'is_system' => false,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/calendar/{$event->id}", [
                'event_date' => '2026-10-12',
                'title' => 'Special Local Holiday',
                'event_type' => 'local_holiday',
                'description' => 'Can be toggled on and off.',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $event->refresh();

        $this->assertFalse($event->is_active);
    }

    public function test_seeded_system_holidays_cannot_be_updated_or_deleted(): void
    {
        $admin = $this->makeAdminUser();

        $this->seed(CalendarEventSeeder::class);

        $event = CalendarEvent::query()->where('title', 'Maundy Thursday')->firstOrFail();

        $this->actingAs($admin)
            ->putJson("/api/calendar/{$event->id}", [
                'event_date' => '2026-04-02',
                'title' => 'Modified Holiday',
                'event_type' => 'legal_holiday',
                'description' => 'Should not be saved.',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Seeded legal holidays cannot be edited.');

        $this->actingAs($admin)
            ->deleteJson("/api/calendar/{$event->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Seeded legal holidays cannot be deleted.');

        $this->assertDatabaseHas('calendar_events', [
            'id' => $event->id,
            'title' => 'Maundy Thursday',
            'is_system' => true,
        ]);
    }

    private function makeAdminUser(): User
    {
        $user = User::factory()->createOne([
            'username' => fake()->unique()->userName(),
        ]);
        $user->assignRole('admin');

        return $user;
    }
}
