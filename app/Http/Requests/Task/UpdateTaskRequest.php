<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $this->user()->can('update', $task);
    }

    public function rules(): array
    {
        $task = $this->route('task');
        $workspaceId = $task ? $task->project->workspace_id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', new Enum(TaskPriority::class)],
            'status' => ['required', new Enum(TaskStatus::class)],
            'deadline' => ['nullable', 'date'],
            'assigned_to' => [
                'nullable',
                Rule::exists('workspace_members', 'user_id')->where(function ($query) use ($workspaceId) {
                    return $query->where('workspace_id', $workspaceId);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.exists' => 'User yang ditugaskan harus merupakan anggota dari workspace ini.',
        ];
    }
}
