<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MediaSource;
use App\Models\RssFeedCache;
use App\Jobs\AnalyzeMediaMention;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CollectRssFeeds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'media:collect-feeds {--source=} {--force}';

    /**
     * The console command description.
     */
    protected $description = 'Collect RSS feeds from media sources and cache them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sourceId = $this->option('source');
        $force = $this->option('force');
        
        // Get sources to process
        $query = MediaSource::where('is_active', true);
        
        if ($sourceId) {
            $query->where('id', $sourceId);
        }
        
        $sources = $query->get();
        
        if ($sources->isEmpty()) {
            $this->warn('No active media sources found.');
            return 0;
        }
        
        $this->info("Processing {$sources->count()} media sources...");
        
        foreach ($sources as $source) {
            $this->processSource($source, $force);
        }
        
        $this->info('RSS feed collection completed!');
        
        // Trigger analysis for new items
        $this->triggerAnalysis();
        
        return 0;
    }
    
    /**
     * Process a single RSS source
     */
    private function processSource(MediaSource $source, bool $force = false)
    {
        // Skip if recently checked (unless forced)
        if (!$force && $source->last_checked_at && $source->last_checked_at->gt(now()->subMinutes($source->check_frequency))) {
            $this->info("Skipping {$source->name} (recently checked)");
            return;
        }
        
        $this->info("Fetching RSS from {$source->name}...");
        
        try {
            // Fetch RSS feed
            $response = Http::timeout(30)->get($source->rss_url);
            
            if (!$response->successful()) {
                throw new \Exception("HTTP error: " . $response->status());
            }
            
            // Parse RSS/Atom feed (default to RSS if not specified)
            $feedType = in_array($source->category, ['atom']) ? 'atom' : 'rss';
            $items = $this->parseRssFeed($response->body(), $feedType);
            
            $newItems = 0;
            $updatedItems = 0;
            
            foreach ($items as $item) {
                // Check if item already exists
                $existing = RssFeedCache::where('source_id', $source->id)
                    ->where('guid', $item['guid'])
                    ->first();
                
                if ($existing) {
                    // Update if content changed
                    if ($existing->title !== $item['title'] || $existing->description !== $item['description']) {
                        $existing->update($item);
                        $updatedItems++;
                    }
                } else {
                    // Create new cache entry
                    RssFeedCache::create(array_merge($item, [
                        'source_id' => $source->id,
                        'processed' => false
                    ]));
                    $newItems++;
                }
            }
            
            // Update source fetch info
            $source->markAsChecked();
            
            $this->info("  → Found {$newItems} new items, updated {$updatedItems} items");
            
        } catch (\Exception $e) {
            $source->markAsChecked();
            
            $this->error("  → Error: " . $e->getMessage());
            Log::error('RSS fetch failed', [
                'source_id' => $source->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Parse RSS/Atom feed content
     */
    private function parseRssFeed(string $content, string $feedType): array
    {
        $items = [];
        
        try {
            // Use SimpleXML to parse the feed
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            
            if ($xml === false) {
                $errors = libxml_get_errors();
                throw new \Exception('XML parsing failed: ' . ($errors[0]->message ?? 'Unknown error'));
            }
            
            if ($feedType === 'atom') {
                // Parse Atom feed
                foreach ($xml->entry as $entry) {
                    $items[] = [
                        'guid' => (string)($entry->id ?? $entry->link['href'] ?? uniqid()),
                        'title' => (string)$entry->title,
                        'link' => (string)($entry->link['href'] ?? $entry->link ?? ''),
                        'description' => (string)($entry->summary ?? $entry->content ?? ''),
                        'pub_date' => $this->parseDate((string)($entry->published ?? $entry->updated)),
                        'author' => (string)($entry->author->name ?? ''),
                        'raw_content' => (string)($entry->content ?? '')
                    ];
                }
            } else {
                // Parse RSS feed (RSS 2.0)
                $channel = $xml->channel ?? $xml;
                
                foreach ($channel->item as $item) {
                    $items[] = [
                        'guid' => (string)($item->guid ?? $item->link ?? uniqid()),
                        'title' => (string)$item->title,
                        'link' => (string)$item->link,
                        'description' => (string)($item->description ?? ''),
                        'pub_date' => $this->parseDate((string)($item->pubDate ?? $item->published ?? '')),
                        'author' => (string)($item->author ?? $item->{'dc:creator'} ?? ''),
                        'raw_content' => (string)($item->{'content:encoded'} ?? '')
                    ];
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Feed parsing failed', [
                'error' => $e->getMessage(),
                'feed_type' => $feedType
            ]);
            throw $e;
        }
        
        return $items;
    }
    
    /**
     * Parse various date formats
     */
    private function parseDate(?string $dateString): ?Carbon
    {
        if (empty($dateString)) {
            return null;
        }
        
        try {
            // Try common date formats
            $formats = [
                'D, d M Y H:i:s O',     // RFC 2822
                'Y-m-d\TH:i:sP',        // ISO 8601
                'Y-m-d\TH:i:s\Z',       // ISO 8601 UTC
                'Y-m-d H:i:s',
                'd M Y H:i:s'
            ];
            
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateString);
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Fallback to Carbon's parser
            return Carbon::parse($dateString);
            
        } catch (\Exception $e) {
            Log::warning('Date parsing failed', [
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Trigger analysis for unprocessed items
     */
    private function triggerAnalysis()
    {
        $unprocessed = RssFeedCache::unprocessed()
            ->recent(3)  // Only process items from last 3 days
            ->limit(100)
            ->get();
        
        if ($unprocessed->isEmpty()) {
            $this->info('No unprocessed items to analyze.');
            return;
        }
        
        $this->info("Queuing {$unprocessed->count()} items for analysis...");
        
        foreach ($unprocessed as $item) {
            AnalyzeMediaMention::dispatch($item);
        }
    }
}