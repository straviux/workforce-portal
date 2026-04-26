<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SyncSwaTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'tasks' => ['required', 'array', 'size:5'],
            'tasks.*.sort_order' => ['required', 'integer', 'between:1,5', 'distinct'],
            'tasks.*.task_name' => ['required', 'string', 'max:255'],
            'tasks.*.task_type' => ['required', Rule::in(['countable', 'check_blank'])],
        ];
    }
}
