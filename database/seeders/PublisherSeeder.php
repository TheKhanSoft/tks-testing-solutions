<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Publisher;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Publisher::create([
            'name' => 'Tech Publishing House',
            'address' => '123 Tech Street, Silicon Valley, CA',
            'website' => 'https://techpublishinghouse.com',
        ]);

        Publisher::create([
            'name' => 'Science Daily Publishers',
            'address' => '456 Science Avenue, Cambridge, MA',
            'website' => 'https://sciencedailypublishers.com',
        ]);

        Publisher::create([
            'name' => 'Innovative Research Press',
            'address' => '789 Innovation Road, Austin, TX',
            'website' => 'https://innovativeresearchpress.com',
        ]);

        Publisher::create([
            'name' => 'Future Insights Publishing',
            'address' => '101 Future Blvd, Seattle, WA',
            'website' => 'https://futureinsightspublishing.com',
        ]);

        Publisher::create([
            'name' => 'Global Knowledge Publishers',
            'address' => '202 Global Lane, New York, NY',
            'website' => 'https://globalknowledgepublishers.com',
        ]);
    }
}
