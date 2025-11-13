<?php

namespace Database\Factories;

use App\Models\Inbox; // Pastikan namespace dan Model Anda benar
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inbox>
 */
class InboxFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inbox::class;

    /**
     * Define the model's default state (state default model).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Nama pengirim
            'name' => $this->faker->name(),
            
            // Email unik dan aman
            'email' => $this->faker->unique()->safeEmail(),
            
            // Nomor telepon (realistis)
            'contact' => $this->faker->phoneNumber(),
            
            // Nama perusahaan palsu
            'company' => $this->faker->company(),
            
            // Alamat lengkap palsu
            'address' => $this->faker->address(),
            
            // Deskripsi/Pesan (menggunakan teks acak, maks 200 karakter)
            'description' => $this->faker->text(200),
            
            // Kolom waktu (jika Model Anda menggunakan timestamps)
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}