<x-layouts::public title="About">

    <h1 class="text-xl font-semibold tracking-tight mb-6">About</h1>

    <div class="space-y-4 text-sm text-stone-700 dark:text-stone-300 leading-relaxed max-w-xl">
        <p>
            Blog directories and indie web indexes have proliferated in the last few years, but they're scattered.
            Finding them requires already knowing about them, or stumbling across a blog post that goes stale.
            There is no single, well-organized, maintained reference for this space.
        </p>
        <p>
            theindex.fyi is that reference. It was born from a Hacker News comment thread where someone listed their
            favorite blog indexes and someone else asked "are there any indexes which index all of these?" and the
            answer was: not really.
        </p>
        <p>
            This is a personal project, maintained manually. Entries are checked periodically for status.
            If you know of an index that belongs here, <a href="{{ route('submit') }}" class="underline underline-offset-2 hover:text-stone-900 dark:hover:text-stone-100 transition-colors">submit it</a>.
        </p>
    </div>

</x-layouts::public>
