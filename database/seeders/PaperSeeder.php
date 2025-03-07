<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paper;

class PaperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Paper::create([
            'title' => 'Introduction to Quantum Computing',
            'content' => 'Quantum computing is a type of computation that harnesses the collective properties of quantum states, such as superposition, interference, and entanglement, to perform calculations. Quantum computers are believed to be able to solve certain computational problems substantially faster than classical computers.',
            'published_at' => '2023-01-15',
        ]);

        Paper::create([
            'title' => 'Advancements in Artificial Intelligence',
            'content' => 'AI has seen significant advancements in recent years, particularly in the domains of deep learning, natural language processing, and computer vision. This paper explores the current state of AI technology and potential future developments.',
            'published_at' => '2023-02-20',
        ]);

        Paper::create([
            'title' => 'Blockchain Technology in Healthcare',
            'content' => 'This paper examines the potential applications of blockchain technology in healthcare systems, including secure patient data management, supply chain integrity for pharmaceuticals, and streamlined claims processing.',
            'published_at' => '2023-03-10',
        ]);

        Paper::create([
            'title' => 'Machine Learning Approaches to Climate Modeling',
            'content' => 'Climate models are becoming increasingly complex as scientists attempt to incorporate more variables. This paper discusses how machine learning techniques can be utilized to improve climate predictions and manage the increasing complexity of models.',
            'published_at' => '2023-04-05',
        ]);

        Paper::create([
            'title' => 'The Ethics of Autonomous Vehicles',
            'content' => 'As autonomous vehicle technology advances, numerous ethical questions arise regarding decision-making in potential accident scenarios, privacy concerns, and liability issues. This paper provides a framework for addressing these ethical challenges.',
            'published_at' => '2023-05-12',
        ]);

        // Additional seed data
        Paper::create([
            'title' => 'Cybersecurity in the Age of IoT',
            'content' => 'The Internet of Things (IoT) brings convenience but also introduces new security challenges. This paper explores the cybersecurity risks associated with IoT devices and proposes strategies to mitigate these risks.',
            'published_at' => '2023-06-18',
        ]);

        Paper::create([
            'title' => 'The Future of Renewable Energy',
            'content' => 'Renewable energy sources such as solar, wind, and hydro are becoming increasingly important in the fight against climate change. This paper discusses the latest advancements in renewable energy technology and their potential impact on the global energy landscape.',
            'published_at' => '2023-07-22',
        ]);

        Paper::create([
            'title' => 'Genomics and Personalized Medicine',
            'content' => 'Advances in genomics are paving the way for personalized medicine, where treatments can be tailored to an individual\'s genetic makeup. This paper examines the current state of genomics research and its implications for the future of healthcare.',
            'published_at' => '2023-08-30',
        ]);

        Paper::create([
            'title' => 'The Role of Big Data in Business Decision Making',
            'content' => 'Big data analytics is transforming the way businesses make decisions by providing insights that were previously unattainable. This paper explores the various applications of big data in business and the challenges associated with its implementation.',
            'published_at' => '2023-09-15',
        ]);

        Paper::create([
            'title' => 'Ethical Considerations in Genetic Engineering',
            'content' => 'Genetic engineering holds great promise for improving human health and agriculture, but it also raises ethical questions. This paper discusses the ethical considerations surrounding genetic engineering and the potential risks and benefits.',
            'published_at' => '2023-10-05',
        ]);
    }
}