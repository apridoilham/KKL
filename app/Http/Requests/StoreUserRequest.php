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
        // Menggunakan Gate yang sudah terdefinisi untuk konsistensi
        return auth()->user()->can('manage-users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Mengambil ID pengguna dari parameter route, bukan input.
        // Misal: route('users.update', $user) -> /users/1
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($userId)],
            'role' => 'required|in:admin,produksi,pengiriman',
            'password' => [
                // 'required' jika ini adalah request pembuatan (userId null), 'nullable' jika edit.
                Rule::requiredIf(!$userId),
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],
        ];
    }
}