<x-layouts::app title="Stats">
    <div class="p-4 sm:p-12 max-w-5xl space-y-14">

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Stats</h1>
            <div class="flex gap-4 text-sm text-zinc-500">
                <a href="{{ route('admin.submissions.index') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Submissions</a>
                <a href="{{ route('admin.indexes.index') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Entries</a>
            </div>
        </div>

        {{-- Visitor summary --}}
        <section>
            <h2 class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-5">Visitors</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ([['label' => 'Today', 'value' => $viewsToday], ['label' => 'Last 7 days', 'value' => $viewsWeek], ['label' => 'All time', 'value' => $viewsTotal]] as $stat)
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 px-5 py-4">
                        <p class="text-3xl font-semibold tracking-tight">{{ number_format($stat['value']) }}</p>
                        <p class="text-sm text-zinc-500 mt-1">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Top pages + referrers --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <section>
                <h2 class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-5">Top Pages</h2>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($topPaths as $row)
                        <div class="flex items-center justify-between px-5 py-4">
                            <span class="font-mono text-sm">{{ $row->path }}</span>
                            <span class="text-sm text-zinc-500 tabular-nums">{{ number_format($row->hits) }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-zinc-400">No data yet</p>
                    @endforelse
                </div>
            </section>

            <section>
                <h2 class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-5">Top Referrers</h2>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($topReferrers as $row)
                        <div class="flex items-center justify-between px-5 py-4">
                            <span class="font-mono text-sm">{{ $row->referrer }}</span>
                            <span class="text-sm text-zinc-500 tabular-nums">{{ number_format($row->hits) }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-zinc-400">No referrers yet</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Click stats --}}
        <section>
            <div class="flex items-baseline gap-3 mb-5">
                <h2 class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Clicks</h2>
                <span class="text-xs text-zinc-400">{{ number_format($totalClicks) }} total</span>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                            <th class="text-left px-5 py-4 font-medium">Entry</th>
                            <th class="text-left px-5 py-4 font-medium hidden sm:table-cell">Category</th>
                            <th class="text-right px-5 py-4 font-medium">Clicks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($entries as $entry)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="font-medium">{{ $entry->name }}</div>
                                    <div class="text-xs text-zinc-400 mt-0.5 truncate max-w-xs">{{ $entry->url }}</div>
                                </td>
                                <td class="px-5 py-4 text-zinc-500 hidden sm:table-cell">{{ $entry->category->label() }}</td>
                                <td class="px-5 py-4 text-right font-mono tabular-nums">{{ number_format($entry->clicks_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</x-layouts::app>
