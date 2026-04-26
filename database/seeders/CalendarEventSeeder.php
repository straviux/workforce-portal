<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use Illuminate\Database\Seeder;

class CalendarEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            ['event_date' => '2026-01-01', 'title' => "New Year's Day"],
            ['event_date' => '2026-02-17', 'title' => 'Chinese New Year'],
            ['event_date' => '2026-03-20', 'title' => "Eid'l Fitr"],
            ['event_date' => '2026-04-02', 'title' => 'Maundy Thursday'],
            ['event_date' => '2026-04-03', 'title' => 'Good Friday'],
            ['event_date' => '2026-04-04', 'title' => 'Black Saturday'],
            ['event_date' => '2026-04-09', 'title' => 'Araw ng Kagitingan'],
            ['event_date' => '2026-05-01', 'title' => 'Labor Day'],
            ['event_date' => '2026-05-28', 'title' => 'Eidul Adha'],
            ['event_date' => '2026-06-12', 'title' => 'Independence Day'],
            ['event_date' => '2026-08-21', 'title' => 'Ninoy Aquino Day'],
            ['event_date' => '2026-08-31', 'title' => "National Heroes' Day"],
            ['event_date' => '2026-11-01', 'title' => "All Saints' Day"],
            ['event_date' => '2026-11-02', 'title' => "All Souls' Day"],
            ['event_date' => '2026-11-30', 'title' => 'Bonifacio Day'],
            ['event_date' => '2026-12-08', 'title' => 'Feast of the Immaculate Conception'],
            ['event_date' => '2026-12-24', 'title' => 'Christmas Eve'],
            ['event_date' => '2026-12-25', 'title' => 'Christmas Day'],
            ['event_date' => '2026-12-30', 'title' => 'Rizal Day'],
            ['event_date' => '2026-12-31', 'title' => "New Year's Eve"],
        ];

        foreach ($events as $event) {
            CalendarEvent::query()->updateOrCreate(
                [
                    'event_date' => $event['event_date'],
                    'title' => $event['title'],
                ],
                [
                    'event_type' => 'legal_holiday',
                    'description' => 'Seeded nationwide Philippine holiday for SWA calendar blocking.',
                    'is_system' => true,
                    'is_active' => true,
                ],
            );
        }
    }
}
