@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'mb-4 rounded-md bg-red-50 dark:bg-red-900/50 p-4']) }}>
        <div class="font-medium text-red-800 dark:text-red-200">
            {{ __('Whoops! Something went wrong.') }}
        </div>
        <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-300">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
