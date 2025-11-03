<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MediaSource;

class FixRemainingFeedsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable feeds that consistently fail
        $disableFeeds = [
            'Computer!Totaal', // Rate limiting (429)
            'AG Connect', // 404
            'Data News (BE)', // 403 Forbidden
            'De Standaard Tech', // 404
            'Dutch IT Channel', // Not working
            'Bloovi' // Site down
        ];
        
        foreach ($disableFeeds as $name) {
            MediaSource::where('name', $name)
                ->update(['is_active' => false]);
            $this->command->info("Disabled: {$name}");
        }
        
        // Fix De Volkskrant - needs special handling
        MediaSource::where('name', 'De Volkskrant Tech')
            ->update([
                'is_active' => false // Disable due to date parsing issues
            ]);
        
        // Add more reliable Dutch tech feeds
        $additionalFeeds = [
            [
                'name' => 'Security.NL',
                'rss_url' => 'https://www.security.nl/rss/headlines.xml',
                'category' => 'tech',
                'language' => 'nl',
                'is_active' => true,
                'check_frequency' => 30,
                'reliability_score' => 100
            ],
            [
                'name' => 'Automatisering Gids',
                'rss_url' => 'https://www.automatiseringgids.nl/rss',
                'category' => 'tech',
                'language' => 'nl',
                'is_active' => true,
                'check_frequency' => 30,
                'reliability_score' => 100
            ],
            [
                'name' => 'Techpulse News',
                'rss_url' => 'https://www.techpulse.nl/rss',
                'category' => 'tech',
                'language' => 'nl',
                'is_active' => true,
                'check_frequency' => 30,
                'reliability_score' => 100
            ],
            [
                'name' => 'Marketingfacts',
                'rss_url' => 'https://www.marketingfacts.nl/rss',
                'category' => 'tech',
                'language' => 'nl',
                'is_active' => true,
                'check_frequency' => 30,
                'reliability_score' => 100
            ]
        ];
        
        foreach ($additionalFeeds as $feed) {
            MediaSource::updateOrCreate(
                ['name' => $feed['name']],
                $feed
            );
            $this->command->info("Added: {$feed['name']}");
        }
        
        $activeCount = MediaSource::where('is_active', true)->count();
        $this->command->info("Total active RSS feeds: {$activeCount}");
    }
}