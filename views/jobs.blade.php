<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if(config('queue-monitor.ui.refresh_interval'))
        <meta http-equiv="refresh" content="{{ config('queue-monitor.ui.refresh_interval') }}">
    @endif
    <title>@lang('Queue Monitor')</title>
    <link href="{{ asset('vendor/queue-monitor/app.css') }}" rel="stylesheet">
</head>

<body class="font-sans pb-64 bg-white dark:bg-gray-800 dark:text-white">

    <nav class="flex items-center py-4 border-b border-gray-100 dark:border-gray-600">
        <h1 class="px-4 w-full font-semibold text-lg">
            @lang('Queue Monitor')
        </h1>
        <div class="w-[24rem] px-4 text-sm text-gray-700 font-light">
            Statistics
        </div>
    </nav>

    <main class="flex">

        <article class="w-full p-4">
            <h2 class="mb-4 text-gray-800 text-sm font-medium">
                @lang('Filter')
            </h2>

            @include('queue-monitor::partials.filter', [
                'filters' => $filters,
            ])

            <h2 class="mb-4 text-gray-800 text-sm font-medium">
                @lang('Jobs')
            </h2>

            @include('queue-monitor::partials.table', [
                'jobs' => $jobs,
            ])

            @if(config('queue-monitor.ui.allow_purge'))
                <div class="mt-12">
                    <form action="{{ route('queue-monitor::purge') }}" method="post">
                        @csrf
                        @method('delete')
                        <button class="py-2 px-4 bg-red-50 dark:bg-red-200 hover:dark:bg-red-300 hover:bg-red-100 text-red-800 text-xs font-medium rounded-md transition-colors duration-150">
                            @lang('Delete all entries')
                        </button>
                    </form>
                </div>
            @endif
        </article>

        <aside class="flex flex-col gap-4 w-[24rem] p-4">
            @foreach($metrics->all() as $metric)
                @include('queue-monitor::partials.metrics-card', [
                    'metric' => $metric,
                ])
            @endforeach
        </aside>

    </main>

</body>

</html>
