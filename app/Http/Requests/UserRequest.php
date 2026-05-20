<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = is_object($user) ? $user->id : $user;

        return [
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'name' => 'required|string|max:255',
            'fullname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => 'required|string|min:8',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
