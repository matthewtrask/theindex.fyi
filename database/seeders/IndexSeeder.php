<?php

namespace Database\Seeders;

use App\Enums\Category;
use App\Enums\IndexStatus;
use App\Models\Index;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IndexSeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            // Curated Directories
            [
                'name' => 'ooh.directory',
                'url' => 'https://ooh.directory',
                'description' => 'Yahoo-style taxonomy of 2,300+ blogs by topic',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Blogscroll',
                'url' => 'https://blogscroll.com',
                'description' => 'Open directory of personal sites maintained on GitHub',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Ye Olde Blogroll',
                'url' => 'https://blogroll.org',
                'description' => 'Human-curated list of 1,000+ personal and independent blogs',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Blogroll Club',
                'url' => 'https://blogroll.club',
                'description' => 'Home for everyone\'s blogrolls and post rolls',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Indieseek',
                'url' => 'https://indieseek.xyz',
                'description' => 'Searchable human-curated indie web directory',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'PersonalSit.es',
                'url' => 'https://personalsit.es',
                'description' => 'Directory of personal sites submitted by their owners',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Blogosphere',
                'url' => 'https://blogosphere.app',
                'description' => 'Aggregates 1,000+ independent and personal blogs',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Blogs Are Back',
                'url' => 'https://www.blogsareback.com/explore',
                'description' => 'Hand-curated directory of indie blogs',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Minifeed',
                'url' => 'https://minifeed.net',
                'description' => 'Small personal web feed aggregator',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Mataroa Collection',
                'url' => 'https://collection.mataroa.blog',
                'description' => 'Directory of blogs hosted on Mataroa',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Web Jamboree',
                'url' => 'https://www.webjamboree.net',
                'description' => 'Directory of links to help navigate the web',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'The Big List of Personal Websites',
                'url' => 'http://biglist.terraaeon.com',
                'description' => 'Flat list of personal sites',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'HN Personal Websites',
                'url' => 'https://hnpwd.github.io',
                'description' => 'Personal sites of Hacker News users',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => false,
            ],
            [
                'name' => 'blogs.hn',
                'url' => 'https://blogs.hn',
                'description' => 'Tiny tech blog directory',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'refined.blog',
                'url' => 'https://refined.blog',
                'description' => 'Personal software blogs with HN scores as quality signal, OPML export',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => false,
            ],
            [
                'name' => 'blogblog.es',
                'url' => 'https://blogblog.es',
                'description' => 'Blog directory for Spanish-language writers',
                'category' => Category::CuratedDirectories,
                'accepts_submissions' => true,
            ],

            // RSS & Feed Aggregators
            [
                'name' => 'powRSS',
                'url' => 'https://powrss.com',
                'description' => 'Public RSS aggregator with shuffle and random features',
                'category' => Category::RssFeedAggregators,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'feedle',
                'url' => 'https://feedle.world',
                'description' => 'Search engine for RSS feeds from blogs and podcasts',
                'category' => Category::RssFeedAggregators,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Scour',
                'url' => 'https://scour.ing',
                'description' => 'Surfaces posts from feeds based on stated interests',
                'category' => Category::RssFeedAggregators,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'RSS.Social',
                'url' => 'https://rss.social',
                'description' => 'RSS-based social discovery',
                'category' => Category::RssFeedAggregators,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'indieblog.page',
                'url' => 'https://indieblog.page',
                'description' => 'Random indie blog button; 4,900+ sites indexed',
                'category' => Category::RssFeedAggregators,
                'accepts_submissions' => true,
            ],

            // Search Engines
            [
                'name' => 'Search My Site',
                'url' => 'https://searchmysite.net',
                'description' => 'Search posts from 3,400+ independent sites',
                'category' => Category::SearchEngines,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Marginalia Search',
                'url' => 'https://marginalia-search.com',
                'description' => 'Search engine for content-rich, non-commercial, lightweight sites',
                'category' => Category::SearchEngines,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Wiby',
                'url' => 'https://wiby.me',
                'description' => 'Search engine for older-style pages reminiscent of the early web',
                'category' => Category::SearchEngines,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Kagi Small Web',
                'url' => 'https://kagi.com/smallweb',
                'description' => 'Discovers personal blogs, indie YouTube channels, and webcomics',
                'category' => Category::SearchEngines,
                'accepts_submissions' => false,
            ],
            [
                'name' => 'blogsearch.io',
                'url' => 'https://blogsearch.io',
                'description' => 'Blog-focused search engine',
                'category' => Category::SearchEngines,
                'accepts_submissions' => true,
            ],

            // Random Discovery
            [
                'name' => 'Blog of the Day',
                'url' => 'https://blogofthe.day',
                'description' => 'A different IndieWeb blog featured every day, with RSS feed',
                'category' => Category::RandomDiscovery,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'The Forest',
                'url' => 'https://theforest.link',
                'description' => 'Get lost on the web — random site discovery',
                'category' => Category::RandomDiscovery,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Read Something Interesting',
                'url' => 'https://www.readsomethinginteresting.com',
                'description' => 'Surfaces great writing from around the web',
                'category' => Category::RandomDiscovery,
                'accepts_submissions' => false,
            ],

            // Constraint-Based Clubs
            [
                'name' => '512KB Club',
                'url' => 'https://512kb.club',
                'description' => 'Sites under 512 kilobytes',
                'category' => Category::ConstraintBasedClubs,
                'accepts_submissions' => true,
            ],
            [
                'name' => '250KB Club',
                'url' => 'https://250kb.club',
                'description' => 'Sites under 250 kilobytes',
                'category' => Category::ConstraintBasedClubs,
                'accepts_submissions' => true,
            ],
            [
                'name' => '1MB Club',
                'url' => 'https://1mb.club',
                'description' => 'Sites under 1 megabyte',
                'category' => Category::ConstraintBasedClubs,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'no-js.club',
                'url' => 'https://no-js.club/members/',
                'description' => 'Sites with no JavaScript',
                'category' => Category::ConstraintBasedClubs,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Dark Theme Club',
                'url' => 'https://darktheme.club',
                'description' => 'Sites using dark themes by default',
                'category' => Category::ConstraintBasedClubs,
                'accepts_submissions' => true,
            ],

            // IndieWeb Infrastructure
            [
                'name' => 'IndieWeb Directory',
                'url' => 'https://indieweb.org/directory',
                'description' => 'The IndieWeb wiki\'s index of personal sites',
                'category' => Category::IndiewebInfrastructure,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'omg.lol directory',
                'url' => 'https://home.omg.lol',
                'description' => 'Directory of personal/IndieWeb sites via omg.lol',
                'category' => Category::IndiewebInfrastructure,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'Now Now Now',
                'url' => 'https://nownownow.com',
                'description' => 'Directory of people with /now pages, by Derek Sivers',
                'category' => Category::IndiewebInfrastructure,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'uses.tech',
                'url' => 'https://uses.tech',
                'description' => 'Directory of /uses pages',
                'category' => Category::IndiewebInfrastructure,
                'accepts_submissions' => true,
            ],
            [
                'name' => 'IndieWeb Webring',
                'url' => 'https://xn--sr8hvo.ws',
                'description' => 'Webring of IndieWeb community members',
                'category' => Category::IndiewebInfrastructure,
                'accepts_submissions' => true,
            ],
        ];

        foreach ($entries as $entry) {
            $slug = Str::slug($entry['name']);
            Index::firstOrCreate(
                ['slug' => $slug],
                [...$entry, 'slug' => $slug, 'status' => IndexStatus::Active]
            );
        }
    }
}
