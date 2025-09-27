<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /* Ini untuk mengatur field mana saja yang boleh diinsert */
    protected $fillable = [
        'name',
        'username',
        'password',
        'security_question',
        'security_answer',
        'role', // Ditambahkan
    ];

    /* Ini untuk menyembunyikan field mana yang akan dihide saat data users ditampilkan */
    protected $hidden = [
        'password',
    ];

    /* Ini untuk mengatur secara otomatis format yang diinginkan di field. Misalkan password hashed berarti ketika disimpan otomatis format hashed bukan string biasa lagi */
    protected $casts = [
        'password' => 'hashed',
    ];
}