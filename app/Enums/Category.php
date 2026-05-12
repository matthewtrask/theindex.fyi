<?php

namespace App\Enums;

enum Category: string
{
    case CuratedDirectories = 'curated_directories';
    case RssFeedAggregators = 'rss_feed_aggregators';
    case SearchEngines = 'search_engines';
    case RandomDiscovery = 'random_discovery';
    case ConstraintBasedClubs = 'constraint_based_clubs';
    case IndiewebInfrastructure = 'indieweb_infrastructure';

    public function label(): string
    {
        return match ($this) {
            self::CuratedDirectories => 'Curated Directories',
            self::RssFeedAggregators => 'RSS & Feed Aggregators',
            self::SearchEngines => 'Search Engines',
            self::RandomDiscovery => 'Random Discovery',
            self::ConstraintBasedClubs => 'Constraint-Based Clubs',
            self::IndiewebInfrastructure => 'IndieWeb Infrastructure',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CuratedDirectories => 'Human-maintained lists of blogs and personal sites. Good for submitting your own site or browsing by topic.',
            self::RssFeedAggregators => 'Feed-centric indexes that surface posts from indie blogs in real time. Good for following the small web like a stream.',
            self::SearchEngines => 'Actually crawl and index the small web. Good for finding specific content or authors.',
            self::RandomDiscovery => 'Send you somewhere unexpected. Good for when you want to find something new without knowing what you\'re looking for.',
            self::ConstraintBasedClubs => 'Indexes defined by a technical or creative constraint (page weight, no JS, dark theme, etc.). Good for finding deliberately minimal sites.',
            self::IndiewebInfrastructure => 'Webrings, /now pages, webmention networks, and other connective tissue of the IndieWeb. Good for plugging into the broader community.',
        };
    }

    // warm: amber, orange, rose — cool: sky, indigo, cyan
    public function labelClass(): string
    {
        return match ($this) {
            self::CuratedDirectories => 'text-amber-600 dark:text-amber-400',
            self::RssFeedAggregators => 'text-sky-600 dark:text-sky-400',
            self::SearchEngines => 'text-indigo-600 dark:text-indigo-400',
            self::RandomDiscovery => 'text-orange-600 dark:text-orange-400',
            self::ConstraintBasedClubs => 'text-cyan-700 dark:text-cyan-400',
            self::IndiewebInfrastructure => 'text-rose-600 dark:text-rose-400',
        };
    }

    public function activeFilterClass(): string
    {
        return match ($this) {
            self::CuratedDirectories => 'bg-amber-600 text-white border-amber-600',
            self::RssFeedAggregators => 'bg-sky-600 text-white border-sky-600',
            self::SearchEngines => 'bg-indigo-600 text-white border-indigo-600',
            self::RandomDiscovery => 'bg-orange-600 text-white border-orange-600',
            self::ConstraintBasedClubs => 'bg-cyan-700 text-white border-cyan-700',
            self::IndiewebInfrastructure => 'bg-rose-600 text-white border-rose-600',
        };
    }

    public function linkHoverClass(): string
    {
        return match ($this) {
            self::CuratedDirectories => 'hover:text-amber-700 dark:hover:text-amber-400',
            self::RssFeedAggregators => 'hover:text-sky-700 dark:hover:text-sky-400',
            self::SearchEngines => 'hover:text-indigo-700 dark:hover:text-indigo-400',
            self::RandomDiscovery => 'hover:text-orange-700 dark:hover:text-orange-400',
            self::ConstraintBasedClubs => 'hover:text-cyan-700 dark:hover:text-cyan-400',
            self::IndiewebInfrastructure => 'hover:text-rose-700 dark:hover:text-rose-400',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CuratedDirectories => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            self::RssFeedAggregators => 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400',
            self::SearchEngines => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
            self::RandomDiscovery => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
            self::ConstraintBasedClubs => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
            self::IndiewebInfrastructure => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
        };
    }
}
