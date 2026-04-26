<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Requests\UpdateCalendarEventRequest;
use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CalendarEvent::query()->with('creator')->orderBy('event_date')->orderBy('title');

            if ($search = trim((string) $request->get('search', ''))) {
                $query->where(function ($calendarQuery) use ($search) {
                    $calendarQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($type = trim((string) $request->get('event_type', ''))) {
                $query->where('event_type', $type);
            }

            if ($from = trim((string) $request->get('start_date', ''))) {
                $query->whereDate('event_date', '>=', $from);
            }

            if ($to = trim((string) $request->get('end_date', ''))) {
                $query->whereDate('event_date', '<=', $to);
            }

            $perPage = max(1, min((int) $request->get('per_page', 15), 50));
            $paginated = $query->paginate($perPage);

            return response()->json([
                'data' => $paginated->items(),
                'filtered_total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error loading calendar events', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not load calendar events.'], 500);
        }
    }

    public function store(StoreCalendarEventRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $event = CalendarEvent::query()->create([
                ...$validated,
                'is_system' => false,
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);
            $event->load('creator');

            return response()->json([
                'data' => $event,
                'message' => 'Calendar event saved.',
            ], 201);
        } catch (\Throwable $exception) {
            Log::error('Error saving calendar event', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not save calendar event.'], 500);
        }
    }

    public function update(UpdateCalendarEventRequest $request, int $id): JsonResponse
    {
        try {
            $event = CalendarEvent::query()->findOrFail($id);

            if ($event->is_system) {
                return response()->json(['message' => 'Seeded legal holidays cannot be edited.'], 422);
            }

            $event->update($request->validated());
            $event->load('creator');

            return response()->json([
                'data' => $event,
                'message' => 'Calendar event updated.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error updating calendar event', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not update calendar event.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $event = CalendarEvent::query()->findOrFail($id);

            if ($event->is_system) {
                return response()->json(['message' => 'Seeded legal holidays cannot be deleted.'], 422);
            }

            $event->delete();

            return response()->json(['message' => 'Calendar event deleted.']);
        } catch (\Throwable $exception) {
            Log::error('Error deleting calendar event', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not delete calendar event.'], 500);
        }
    }
}
