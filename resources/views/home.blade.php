<x-layouts::public title="The indie web index directory">

    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight mb-2">The index of indexes.</h1>
        <p class="text-stone-500 dark:text-stone-400 text-sm leading-relaxed max-w-xl">
            A maintained, canonical meta-index of indie web and small web index sites.
            {{ \App\Models\Index::count() }} entries across {{ count(\App\Enums\Category::cases()) }} categories.
            Unless noted, each index accepts submissions.
        </p>
    </div>

    <nav aria-label="Filter by category" class="flex flex-wrap gap-2 mb-10">
        <a
            href="{{ route('home') }}"
            class="text-xs px-2.5 py-1 rounded-full border transition-colors
                {{ !$active ? 'bg-stone-800 dark:bg-stone-200 text-white dark:text-stone-900 border-stone-800 dark:border-stone-200' : 'border-stone-300 dark:border-stone-600 text-stone-500 dark:text-stone-400 hover:border-stone-400 dark:hover:border-stone-500 hover:text-stone-700 dark:hover:text-stone-200' }}"
            {{ !$active ? 'aria-current=true' : '' }}
        >All</a>
        @foreach ($categories as $category)
            <a
                href="{{ route('home', ['category' => $category->value]) }}"
                class="text-xs px-2.5 py-1 rounded-full border transition-colors
                    {{ $active === $category ? $category->activeFilterClass() : 'border-stone-300 dark:border-stone-600 text-stone-500 dark:text-stone-400 hover:border-stone-400 dark:hover:border-stone-500 hover:text-stone-700 dark:hover:text-stone-200' }}"
                {{ $active === $category ? 'aria-current=true' : '' }}
            >{{ $category->label() }}</a>
        @endforeach
    </nav>

    <div class="space-y-14">
        @foreach ($grouped as $item)
            @php $category = $item['category']; @endphp
            @if ($item['entries']->isNotEmpty())
                <section aria-labelledby="category-{{ $category->value }}">
                    <div class="mb-5">
                        <h2 id="category-{{ $category->value }}" class="text-xs font-semibold uppercase tracking-widest {{ $category->labelClass() }} mb-1">{{ $category->label() }}</h2>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $category->description() }}</p>
                    </div>

                    <ul class="divide-y divide-stone-100 dark:divide-stone-800">
                        @foreach ($item['entries'] as $entry)
                            <li class="py-3 flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a
                                            href="{{ route('visit', $entry->slug) }}"
                                            class="font-medium text-sm {{ $category->linkHoverClass() }} transition-colors"
                                            rel="noopener noreferrer"
                                        >{{ $entry->name }}</a>

                                        @if ($entry->language && $entry->language !== 'en')
                                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-stone-200 dark:bg-stone-700 text-stone-600 dark:text-stone-300">{{ $entry->languageName() }}</span>
                                        @endif

                                        @if ($entry->status !== \App\Enums\IndexStatus::Active)
                                            <span class="inline-flex items-center gap-1 text-xs px-1.5 py-0.5 rounded-full font-medium
                                                {{ $entry->status === \App\Enums\IndexStatus::Inactive
                                                    ? 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400'
                                                    : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400' }}">
                                                <span aria-hidden="true" class="w-1.5 h-1.5 rounded-full inline-block
                                                    {{ $entry->status === \App\Enums\IndexStatus::Inactive ? 'bg-yellow-500' : 'bg-red-500' }}"></span>
                                                {{ $entry->status->label() }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">{{ $entry->description }}</p>
                                </div>

                                @if (!$entry->accepts_submissions)
                                    <div class="shrink-0">
                                        <span class="text-xs bg-stone-200 dark:bg-stone-700 text-stone-600 dark:text-stone-300 px-1.5 py-0.5 rounded-full">no submissions</span>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endforeach
    </div>

    <div class="mt-12 pt-6 border-t border-stone-100 dark:border-stone-800 text-sm text-stone-400 dark:text-stone-500">
        Know one that's missing? <a href="{{ route('submit') }}" class="text-teal-700 dark:text-teal-400 hover:text-teal-900 dark:hover:text-teal-300 transition-colors underline underline-offset-2">Suggest an entry.</a>
    </div>

</x-layouts::public>
