<?php

declare(strict_types=1);

namespace App\Http\Requests\RBAC;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $permissionId = $this->route('id');
        return ['name' => ['required', 'string', 'max:255', "unique:permissions,name,{$permissionId}"]];
    }

    public function bodyParameters(): array
    {
        return ['name' => ['description' => 'The new unique name for the permission.', 'example' => 'approve_blogs']];
    }
}
