<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'Quantum Computing',
            'description' => 'Research and advancements in quantum computing technology.',
        ]);

        Category::create([
            'name' => 'Artificial Intelligence',
            'description' => 'Developments and applications of artificial intelligence.',
        ]);

        Category::create([
            'name' => 'Blockchain',
            'description' => 'Exploring the uses of blockchain technology in various fields.',
        ]);

        Category::create([
            'name' => 'Climate Science',
            'description' => 'Studies and models related to climate change and predictions.',
        ]);

        Category::create([
            'name' => 'Ethics',
            'description' => 'Ethical considerations in technology and science.',
        ]);

        Category::create([
            'name' => 'Cybersecurity',
            'description' => 'Security challenges and solutions in the digital age.',
        ]);

        Category::create([
            'name' => 'Renewable Energy',
            'description' => 'Advancements in renewable energy sources and technologies.',
        ]);

        Category::create([
            'name' => 'Genomics',
            'description' => 'Research in genomics and personalized medicine.',
        ]);

        Category::create([
            'name' => 'Big Data',
            'description' => 'Applications and implications of big data analytics.',
        ]);

        Category::create([
            'name' => 'Genetic Engineering',
            'description' => 'Ethical and practical aspects of genetic engineering.',
        ]);
    }
}