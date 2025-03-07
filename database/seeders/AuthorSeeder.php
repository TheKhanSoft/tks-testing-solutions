<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Author::create([
            'name' => 'Alice Johnson',
            'email' => 'alice.johnson@example.com',
            'bio' => 'Alice Johnson is a renowned computer scientist specializing in quantum computing and artificial intelligence.',
        ]);

        Author::create([
            'name' => 'Bob Smith',
            'email' => 'bob.smith@example.com',
            'bio' => 'Bob Smith is a leading expert in blockchain technology and its applications in various industries.',
        ]);

        Author::create([
            'name' => 'Carol White',
            'email' => 'carol.white@example.com',
            'bio' => 'Carol White is a climate scientist with a focus on machine learning approaches to climate modeling.',
        ]);

        Author::create([
            'name' => 'David Brown',
            'email' => 'david.brown@example.com',
            'bio' => 'David Brown is an ethicist specializing in the ethical implications of autonomous vehicles and genetic engineering.',
        ]);

        Author::create([
            'name' => 'Eve Davis',
            'email' => 'eve.davis@example.com',
            'bio' => 'Eve Davis is a cybersecurity expert with a focus on the Internet of Things (IoT) and big data analytics.',
        ]);
    }
}