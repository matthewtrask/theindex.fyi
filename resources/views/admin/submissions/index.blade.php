<x-layouts::app title="Submissions">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Submissions</h1>
                <p class="text-sm text-zinc-500 mt-0.5">{{ $submissions->total() }} total</p>
            </div>
            <div class="flex gap-3 text-sm">
                <a href="{{ route('admin.stats') }}" class="text-zinc-500 hover:text-zinc-900 transition-colors">Stats</a>
                <a href="{{ route('admin.indexes.index') }}" class="text-zinc-500 hover:text-zinc-900 transition-colors">Entries</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <th class="text-left px-4 py-3 font-medium">Entry</th>
                        <th class="text-left px-4 py-3 font-medium">Category</th>
                        <th class="text-left px-4 py-3 font-medium">Submitter</th>
                        <th class="text-left px-4 py-3 font-medium">Status</th>
                        <th class="text-left px-4 py-3 font-medium">Submitted</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($submissions as $submission)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $submission->name }}</div>
                                <a href="{{ $submission->url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-zinc-400 hover:text-zinc-600 truncate block max-w-xs">
                                    {{ $submission->url }}
                                </a>
                                <p class="text-xs text-zinc-500 mt-1">{{ $submission->description }}</p>
                            </td>
                            <td class="px-4 py-3 text-zinc-500 text-xs">
                                {{ $submission->category?->label() ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-500">
                                {{ $submission->submitted_by_email ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ match($submission->status) {
                                        \App\Enums\SubmissionStatus::Pending => 'bg-yellow-50 text-yellow-800',
                                        \App\Enums\SubmissionStatus::Approved => 'bg-green-50 text-green-800',
                                        \App\Enums\SubmissionStatus::Rejected => 'bg-red-50 text-red-800',
                                    } }}">
                                    {{ $submission->status->value }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-400">
                                {{ $submission->created_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-end">
                                    @if ($submission->status !== \App\Enums\SubmissionStatus::Approved)
                                        <form method="POST" action="{{ route('admin.submissions.update', $submission) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved" />
                                            <button type="submit" class="text-xs text-green-700 hover:text-green-900 font-medium">Approve</button>
                                        </form>
                                    @endif
                                    @if ($submission->status !== \App\Enums\SubmissionStatus::Rejected)
                                        <form method="POST" action="{{ route('admin.submissions.update', $submission) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected" />
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Reject</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-400">No submissions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $submissions->links() }}
        </div>
    </div>
</x-layouts::app>
