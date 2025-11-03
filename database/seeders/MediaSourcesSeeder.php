<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MediaSource;

class MediaSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            // Tech-specifieke Nederlandse media
            [
                'name' => 'Tweakers.net',
                'url' => 'https://feeds.feedburner.com/tweakers/mixed',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Bright.nl',
                'url' => 'https://www.bright.nl/rss',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Computer!Totaal',
                'url' => 'https://computertotaal.nl/rss',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Tech.eu',
                'url' => 'https://tech.eu/feed/',
                'source_type' => 'tech',
                'country' => 'EU',
                'language' => 'en',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'StartupJuncture',
                'url' => 'https://startupjuncture.com/feed/',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'en',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Dutch IT Channel',
                'url' => 'https://dutchitchannel.nl/feed',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Emerce',
                'url' => 'https://www.emerce.nl/feed',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'AG Connect',
                'url' => 'https://www.agconnect.nl/rss',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            
            // Tech-specifieke Belgische media
            [
                'name' => 'Data News (BE)',
                'url' => 'https://datanews.be/rss.xml',
                'source_type' => 'tech',
                'country' => 'BE',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'TechPulse (BE)',
                'url' => 'https://techpulse.be/feed/',
                'source_type' => 'tech',
                'country' => 'BE',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Bloovi',
                'url' => 'https://www.bloovi.be/rss',
                'source_type' => 'tech',
                'country' => 'BE',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            
            // Algemene Nederlandse media (tech secties)
            [
                'name' => 'NOS Tech',
                'url' => 'https://feeds.nos.nl/nosnieuwstech',
                'source_type' => 'news',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'NU.nl Tech',
                'url' => 'https://www.nu.nl/rss/Tech',
                'source_type' => 'news',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'RTL Nieuws Tech',
                'url' => 'https://www.rtlnieuws.nl/tech/rss.xml',
                'source_type' => 'news',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'De Telegraaf Tech',
                'url' => 'https://www.telegraaf.nl/rss/tech.xml',
                'source_type' => 'news',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'AD Tech',
                'url' => 'https://www.ad.nl/tech/rss.xml',
                'source_type' => 'news',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'De Volkskrant Tech',
                'url' => 'https://www.volkskrant.nl/tech/rss.xml',
                'source_type' => 'news',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'NRC Tech',
                'url' => 'https://www.nrc.nl/rss/categorie/tech/',
                'source_type' => 'news',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Trouw Wetenschap',
                'url' => 'https://www.trouw.nl/wetenschap/rss.xml',
                'source_type' => 'news',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Het Parool Tech',
                'url' => 'https://www.parool.nl/tech/rss.xml',
                'source_type' => 'news',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            
            // Algemene Belgische media (tech secties)
            [
                'name' => 'De Standaard Tech',
                'url' => 'https://www.standaard.be/rss/section/e70ccf13-a2f0-42b0-8bd3-e32d424a0aa0',
                'source_type' => 'news',
                'country' => 'BE',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'De Morgen Tech',
                'url' => 'https://www.demorgen.be/tech/rss.xml',
                'source_type' => 'news',
                'country' => 'BE',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Het Laatste Nieuws Tech',
                'url' => 'https://www.hln.be/tech/rss.xml',
                'source_type' => 'news',
                'country' => 'BE',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Het Nieuwsblad Tech',
                'url' => 'https://www.nieuwsblad.be/tech/rss.xml',
                'source_type' => 'news',
                'country' => 'BE',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'VRT NWS Tech',
                'url' => 'https://www.vrt.be/vrtnws/nl/rss/technologie/',
                'source_type' => 'news',
                'country' => 'BE',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'De Tijd Tech',
                'url' => 'https://www.tijd.be/rss/technologie.xml',
                'source_type' => 'news',
                'country' => 'BE',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            
            // Business & Financial media met tech coverage
            [
                'name' => 'Het Financieele Dagblad Tech',
                'url' => 'https://fd.nl/rss/tech',
                'source_type' => 'business',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'MT/Sprout',
                'url' => 'https://www.mt.nl/feed',
                'source_type' => 'business',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Computable',
                'url' => 'https://www.computable.nl/rss',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'ICT Magazine',
                'url' => 'https://www.ictmagazine.nl/feed/',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'nl',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            
            // Internationale tech media (Engels)
            [
                'name' => 'TechCrunch',
                'url' => 'https://techcrunch.com/feed/',
                'source_type' => 'tech',
                'country' => 'US',
                'language' => 'en',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'The Verge',
                'url' => 'https://www.theverge.com/rss/index.xml',
                'source_type' => 'tech',
                'country' => 'US',
                'language' => 'en',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Wired',
                'url' => 'https://www.wired.com/feed/rss',
                'source_type' => 'tech',
                'country' => 'US',
                'language' => 'en',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'Ars Technica',
                'url' => 'https://feeds.arstechnica.com/arstechnica/index',
                'source_type' => 'tech',
                'country' => 'US',
                'language' => 'en',
                'feed_type' => 'rss',
                'is_active' => true
            ],
            [
                'name' => 'The Next Web',
                'url' => 'https://thenextweb.com/feed',
                'source_type' => 'tech',
                'country' => 'NL',
                'language' => 'en',
                'feed_type' => 'rss',
                'is_active' => true
            ],
        ];
        
        foreach ($sources as $sourceData) {
            // Map fields to match database columns
            $data = [
                'name' => $sourceData['name'],
                'rss_url' => $sourceData['url'],
                'category' => $sourceData['source_type'], // source_type -> category
                'language' => $sourceData['language'],
                'is_active' => $sourceData['is_active'],
                'check_frequency' => 30, // default 30 minutes
                'reliability_score' => 100 // default high reliability
            ];
            
            MediaSource::updateOrCreate(
                ['rss_url' => $data['rss_url']],
                $data
            );
        }
        
        $this->command->info('Media sources seeded successfully! Total: ' . count($sources));
    }
}