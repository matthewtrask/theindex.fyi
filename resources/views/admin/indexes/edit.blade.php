<x-layouts::app :title="'Edit: '.$index->name">
    <div class="p-6 max-w-xl space-y-6">
        <div>
            <a href="{{ route('admin.indexes.index') }}" class="text-sm text-zinc-500 hover:text-zinc-900 transition-colors">← Entries</a>
            <h1 class="text-xl font-semibold mt-2">{{ $index->name }}</h1>
        </div>

        @if (session('success'))
            <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.indexes.update', $index) }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium mb-1.5">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $index->name) }}" required
                    class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 px-3 py-2 text-sm bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400" />
            </div>

            <div>
                <label for="url" class="block text-sm font-medium mb-1.5">URL</label>
                <input type="url" id="url" name="url" value="{{ old('url', $index->url) }}" required
                    class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 px-3 py-2 text-sm bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400" />
            </div>

            <div>
                <label for="description" class="block text-sm font-medium mb-1.5">Description</label>
                <input type="text" id="description" name="description" value="{{ old('description', $index->description) }}" required maxlength="500"
                    class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 px-3 py-2 text-sm bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400" />
            </div>

            <div>
                <label for="category" class="block text-sm font-medium mb-1.5">Category</label>
                <select id="category" name="category" required
                    class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 px-3 py-2 text-sm bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400">
                    @foreach ($categories as $category)
                        <option value="{{ $category->value }}" {{ old('category', $index->category->value) === $category->value ? 'selected' : '' }}>
                            {{ $category->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium mb-1.5">Status</label>
                <select id="status" name="status" required
                    class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 px-3 py-2 text-sm bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ old('status', $index->status->value) === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="accepts_submissions" value="0" />
                <input type="checkbox" id="accepts_submissions" name="accepts_submissions" value="1"
                    {{ old('accepts_submissions', $index->accepts_submissions) ? 'checked' : '' }}
                    class="rounded border-zinc-300" />
                <label for="accepts_submissions" class="text-sm">Accepts submissions</label>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="rounded-md bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 transition-colors">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
