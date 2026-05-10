<x-layouts::app title="Entries">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Entries</h1>
                <p class="text-sm text-zinc-500 mt-0.5">{{ $entries->total() }} total</p>
            </div>
            <div class="flex gap-3 text-sm">
                <a href="{{ route('admin.stats') }}" class="text-zinc-500 hover:text-zinc-900 transition-colors">Stats</a>
                <a href="{{ route('admin.submissions.index') }}" class="text-zinc-500 hover:text-zinc-900 transition-colors">Submissions</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <th class="text-left px-4 py-3 font-medium">Entry</th>
                        <th class="text-left px-4 py-3 font-medium hidden sm:table-cell">Category</th>
                        <th class="text-left px-4 py-3 font-medium">Status</th>
                        <th class="text-left px-4 py-3 font-medium hidden sm:table-cell">Submissions</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($entries as $entry)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $entry->name }}</div>
                                <a href="{{ $entry->url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-zinc-400 hover:text-zinc-600 truncate block max-w-xs">
                                    {{ $entry->url }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-500 hidden sm:table-cell">{{ $entry->category->label() }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ match($entry->status) {
                                        \App\Enums\IndexStatus::Active => 'bg-green-50 text-green-800',
                                        \App\Enums\IndexStatus::Inactive => 'bg-yellow-50 text-yellow-800',
                                        \App\Enums\IndexStatus::Dead => 'bg-red-50 text-red-800',
                                    } }}">
                                    {{ $entry->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-500 hidden sm:table-cell">
                                {{ $entry->accepts_submissions ? 'Yes' : 'No' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.indexes.edit', $entry) }}" class="text-xs text-zinc-500 hover:text-zinc-900 font-medium">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>
            {{ $entries->links() }}
        </div>
    </div>
</x-layouts::app>
