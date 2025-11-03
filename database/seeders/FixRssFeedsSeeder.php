<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MediaSource;

class FixRssFeedsSeeder extends Seeder
{
    /**
     * Fix broken RSS feed URLs
     */
    public function run(): void
    {
        $updates = [
            // Update broken feeds with working alternatives or correct URLs
            'Computer!Totaal' => [
                'rss_url' => 'https://www.computertotaal.nl/rss.xml', // Updated URL
                'is_active' => true
            ],
            'Dutch IT Channel' => [
                'rss_url' => 'https://dutchitchannel.nl/rss',
                'is_active' => false // Disable if not working
            ],
            'AG Connect' => [
                'rss_url' => 'https://www.agconnect.nl/rss.xml',
                'is_active' => true
            ],
            'Data News (BE)' => [
                'rss_url' => 'https://datanews.knack.be/feed.rss', // Alternative URL
                'is_active' => true
            ],
            'Bloovi' => [
                'rss_url' => 'https://www.bloovi.be/frontend/rss',
                'is_active' => false // Site seems down
            ],
            'RTL Nieuws Tech' => [
                'rss_url' => 'https://www.rtlnieuws.nl/rss.xml', // General feed (no tech-specific)
                'category' => 'news',
                'is_active' => true
            ],
            'De Telegraaf Tech' => [
                'rss_url' => 'https://www.telegraaf.nl/rss', // General feed
                'category' => 'news',
                'is_active' => true
            ],
            'NRC Tech' => [
                'rss_url' => 'https://www.nrc.nl/rss/', // General feed
                'category' => 'news',
                'is_active' => true
            ],
            'Het Parool Tech' => [
                'rss_url' => 'https://www.parool.nl/rss.xml', // General feed
                'category' => 'news',
                'is_active' => true
            ],
            'De Standaard Tech' => [
                'rss_url' => 'https://www.standaard.be/rss/section/1f53f0d2-f0cd-40ae-8e3c-bcfd40411b20', // Wetenschap & Planeet
                'is_active' => true
            ],
            'De Morgen Tech' => [
                'rss_url' => 'https://www.demorgen.be/rss.xml', // General feed
                'category' => 'news',
                'is_active' => true
            ],
            'Het Nieuwsblad Tech' => [
                'rss_url' => 'https://www.nieuwsblad.be/rss.xml', // General feed
                'category' => 'news',
                'is_active' => true
            ],
            'VRT NWS Tech' => [
                'rss_url' => 'https://www.vrt.be/vrtnws/nl.rss.articles.xml', // General feed
                'category' => 'news',
                'is_active' => true
            ],
            'De Tijd Tech' => [
                'rss_url' => 'https://www.tijd.be/rss/algemeen.xml', // General feed
                'category' => 'news',
                'is_active' => true
            ],
            'Het Financieele Dagblad Tech' => [
                'rss_url' => 'https://fd.nl/rss', // General FD feed
                'category' => 'business',
                'is_active' => true
            ],
            // Fix De Volkskrant that had date parsing issues
            'De Volkskrant Tech' => [
                'rss_url' => 'https://www.volkskrant.nl/rss.xml', // General feed
                'category' => 'news',
                'is_active' => true
            ]
        ];
        
        foreach ($updates as $name => $data) {
            $source = MediaSource::where('name', $name)->first();
            
            if ($source) {
                $source->update($data);
                $this->command->info("Updated: {$name}");
            } else {
                $this->command->warn("Not found: {$name}");
            }
        }
        
        // Add some additional working tech RSS feeds
        $newFeeds = [
            [
                'name' => 'Techzine',
                'rss_url' => 'https://www.techzine.nl/feed/',
                'category' => 'tech',
                'language' => 'nl',
                'is_active' => true,
                'check_frequency' => 30,
                'reliability_score' => 100
            ],
            [
                'name' => 'IT Daily',
                'rss_url' => 'https://itdaily.be/feed/',
                'category' => 'tech',
                'language' => 'nl',
                'is_active' => true,
                'check_frequency' => 30,
                'reliability_score' => 100
            ],
            [
                'name' => 'Webwereld',
                'rss_url' => 'https://webwereld.nl/feed/',
                'category' => 'tech',
                'language' => 'nl',
                'is_active' => true,
                'check_frequency' => 30,
                'reliability_score' => 100
            ],
            [
                'name' => 'Frankwatching',
                'rss_url' => 'https://www.frankwatching.com/feed/',
                'category' => 'tech',
                'language' => 'nl',
                'is_active' => true,
                'check_frequency' => 30,
                'reliability_score' => 100
            ],
            [
                'name' => 'ZDNet Benelux',
                'rss_url' => 'https://www.zdnet.be/feeds/news.xml',
                'category' => 'tech',
                'language' => 'nl',
                'is_active' => true,
                'check_frequency' => 30,
                'reliability_score' => 100
            ]
        ];
        
        foreach ($newFeeds as $feed) {
            MediaSource::updateOrCreate(
                ['rss_url' => $feed['rss_url']],
                $feed
            );
            $this->command->info("Added/Updated: {$feed['name']}");
        }
        
        $this->command->info('RSS feeds fixed and updated!');
    }
}