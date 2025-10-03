<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi bisa diatur di sini, misalnya hanya admin yang boleh
        return auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Mendapatkan userId dari route atau input untuk aturan unique
        $userId = $this->input('userId'); 

        $rules = [
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($userId)],
            'role' => 'required|in:admin,staff',
        ];

        if ($userId) { // Mode edit
            $rules['password'] = 'nullable|string|min:6|confirmed';
        } else { // Mode create
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        return $rules;
    }
}