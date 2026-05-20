<?php

namespace App\Http\Requests;

class UpdateUserRequest extends UserRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['password'] = 'nullable|string|min:8';

        return $rules;
    }
}
