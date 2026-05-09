<x-layouts::public title="Submit an entry" description="Suggest a new indie web or small web index for inclusion in theindex.fyi. Submissions are reviewed manually.">

    <h1 class="text-xl font-semibold tracking-tight mb-2">Suggest an entry</h1>
    <p class="text-sm text-stone-500 dark:text-stone-400 mb-8">
        Submissions are reviewed manually before being added. Focused on indie web / small web indexes only,
        not individual blogs.
    </p>

    @if (session('success'))
        <div role="status" class="mb-6 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('submit.store') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium mb-1.5">
                Name <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                aria-required="true"
                @error('name') aria-describedby="name-error" aria-invalid="true" @enderror
                class="w-full rounded-md border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 placeholder-stone-400 dark:placeholder-stone-500 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-400 dark:focus:ring-stone-500 focus:border-transparent @error('name') border-red-400 dark:border-red-500 @enderror"
                placeholder="e.g. ooh.directory"
            />
            @error('name')
                <p id="name-error" class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="url" class="block text-sm font-medium mb-1.5">
                URL <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <input
                type="url"
                id="url"
                name="url"
                value="{{ old('url') }}"
                required
                aria-required="true"
                @error('url') aria-describedby="url-error" aria-invalid="true" @enderror
                class="w-full rounded-md border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 placeholder-stone-400 dark:placeholder-stone-500 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-400 dark:focus:ring-stone-500 focus:border-transparent @error('url') border-red-400 dark:border-red-500 @enderror"
                placeholder="https://"
            />
            @error('url')
                <p id="url-error" class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium mb-1.5">
                Description <span aria-hidden="true" class="text-red-500">*</span>
                <span class="text-stone-400 font-normal ml-1">one sentence</span>
            </label>
            <input
                type="text"
                id="description"
                name="description"
                value="{{ old('description') }}"
                required
                aria-required="true"
                maxlength="500"
                @error('description') aria-describedby="description-error" aria-invalid="true" @enderror
                class="w-full rounded-md border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 placeholder-stone-400 dark:placeholder-stone-500 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-400 dark:focus:ring-stone-500 focus:border-transparent @error('description') border-red-400 dark:border-red-500 @enderror"
                placeholder="What it is and who it's for"
            />
            @error('description')
                <p id="description-error" class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category" class="block text-sm font-medium mb-1.5">
                Category <span class="text-stone-400 font-normal ml-1">optional</span>
            </label>
            <select
                id="category"
                name="category"
                class="w-full rounded-md border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-400 dark:focus:ring-stone-500 focus:border-transparent"
            >
                <option value="">Not sure</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->value }}" {{ old('category') === $category->value ? 'selected' : '' }}>
                        {{ $category->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="submitted_by_email" class="block text-sm font-medium mb-1.5">
                Your email <span class="text-stone-400 font-normal ml-1">optional, won't be published</span>
            </label>
            <input
                type="email"
                id="submitted_by_email"
                name="submitted_by_email"
                value="{{ old('submitted_by_email') }}"
                @error('submitted_by_email') aria-describedby="email-error" aria-invalid="true" @enderror
                class="w-full rounded-md border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 placeholder-stone-400 dark:placeholder-stone-500 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-400 dark:focus:ring-stone-500 focus:border-transparent @error('submitted_by_email') border-red-400 dark:border-red-500 @enderror"
                placeholder="you@example.com"
            />
            @error('submitted_by_email')
                <p id="email-error" class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button
                type="submit"
                class="rounded-md bg-teal-700 dark:bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800 dark:hover:bg-teal-700 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-stone-900"
            >
                Submit entry
            </button>
        </div>
    </form>

</x-layouts::public>
