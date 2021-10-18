<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if(config('queue-monitor.ui.refresh_interval'))
        <meta http-equiv="refresh" content="{{ config('queue-monitor.ui.refresh_interval') }}">
    @endif

    <title>@lang('Queue Monitor')</title>

    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">

</head>

<body class="font-sans p-6 pb-64 bg-gray-100">

    <h1 class="mb-6 text-5xl text-blue-900 font-bold">
        @lang('Queue Monitor')
    </h1>

    @isset($metrics)

        <div class="flex flex-wrap -mx-4 mb-2">

            @foreach($metrics->all() as $metric)

                @include('queue-monitor::partials.metrics-card', [
                    'metric' => $metric,
                ])

            @endforeach

        </div>

    @endisset

    <div class="px-6 py-4 mb-6 pl-4 bg-white rounded-md shadow-md">

        <h2 class="mb-4 text-2xl font-bold text-blue-900">
            @lang('Filter')
        </h2>

        <form action="" method="get">

            <div class="flex items-center my-2 -mx-2">

                <div class="px-2 w-1/4">
                    <label for="filter_show" class="block mb-1 text-xs uppercase font-semibold text-gray-600">
                        @lang('Show jobs')
                    </label>
                    <select name="type" id="filter_show" class="w-full p-2 bg-gray-200 border-2 border-gray-300 rounded">
                        <option @if($filters['type'] === 'all') selected @endif value="all">@lang('All')</option>
                        <option @if($filters['type'] === 'running') selected @endif value="running">@lang('Running')</option>
                        <option @if($filters['type'] === 'failed') selected @endif value="failed">@lang('Failed')</option>
                        <option @if($filters['type'] === 'succeeded') selected @endif value="succeeded">@lang('Succeeded')</option>
                    </select>
                </div>

                <div class="px-2 w-1/4">
                    <label for="filter_queues" class="block mb-1 text-xs uppercase font-semibold text-gray-600">
                        @lang('Queues')
                    </label>
                    <select name="queue" id="filter_queues" class="w-full p-2 bg-gray-200 border-2 border-gray-300 rounded">
                        <option value="all">All</option>
                        @foreach($queues as $queue)
                            <option @if($filters['queue'] === $queue) selected @endif value="{{ $queue }}">
                                {{ __($queue) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="mt-4">

                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-xs font-medium uppercase tracking-wider text-white rounded">
                    @lang('Filter')
                </button>

            </div>

        </form>

    </div>

    <div class="overflow-x-auto shadow-lg">

        <table class="w-full rounded whitespace-no-wrap">

            <thead class="bg-gray-200">

                <tr>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">@lang('Status')</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">@lang('Job')</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">@lang('Details')</th>

                    @if(config('queue-monitor.ui.show_custom_data'))
                        <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">@lang('Custom Data')</th>
                    @endif

                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">@lang('Progress')</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">@lang('Duration')</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">@lang('Started')</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">@lang('Error')</th>

                    @if(config('queue-monitor.ui.allow_deletion'))
                        <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">@lang('Action')</th>
                    @endif
                </tr>

            </thead>

            <tbody class="bg-white">

                @forelse($jobs as $job)

                    <tr class="font-sm leading-relaxed">

                        <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">
                            @include('queue-monitor::partials.job-status', ['status' => $job->status])
                        </td>

                        <td class="p-4 text-gray-800 text-sm leading-5 font-medium border-b border-gray-200">

                            {{ $job->getBaseName() }}

                            <span class="ml-1 text-xs text-gray-600">
                                #{{ $job->job_id }}
                            </span>

                        </td>

                        <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">

                            <div class="text-xs">
                                <span class="text-gray-600 font-medium">@lang('Queue'):</span>
                                <span class="font-semibold">{{ $job->queue }}</span>
                            </div>

                            <div class="text-xs">
                                <span class="text-gray-600 font-medium">@lang('Attempt'):</span>
                                <span class="font-semibold">{{ $job->attempt }}</span>
                            </div>

                        </td>

                        @if(config('queue-monitor.ui.show_custom_data'))

                            <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">

                                    <textarea rows="4"
                                              class="w-64 text-xs p-1 border rounded"
                                              readonly>{{ json_encode($job->getData(), JSON_PRETTY_PRINT) }}
                                    </textarea>

                            </td>

                        @endif

                        <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">

                            @if($job->progress !== null)

                                <div class="w-32">

                                    <div class="flex items-stretch h-3 rounded-full bg-gray-300 overflow-hidden">
                                        <div class="h-full bg-green-500" style="width: {{ $job->progress }}%"></div>
                                    </div>

                                    <div class="flex justify-center mt-1 text-xs text-gray-800 font-semibold">
                                        {{ $job->progress }}%
                                    </div>

                                </div>

                            @else
                                -
                            @endif

                        </td>

                        <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">
                            {{ $job->getElapsedInterval()->format('%H:%I:%S') }}
                        </td>

                        <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">
                            {{ $job->started_at->diffForHumans() }}
                        </td>

                        <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">

                            @if($job->hasFailed() && $job->exception_message !== null)

                                <textarea rows="4" class="w-64 text-xs p-1 border rounded" readonly>{{ $job->exception_message }}</textarea>

                            @else
                                -
                            @endif

                        </td>

                        @if(config('queue-monitor.ui.allow_deletion'))

                            <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">

                                <form action="{{ route('queue-monitor::destroy', [$job]) }}" method="post">

                                    @csrf
                                    @method('delete')

                                    <button class="px-3 py-1 bg-red-200 hover:bg-red-300 text-red-800 text-xs font-medium uppercase tracking-wider text-white rounded">
                                        @lang('Delete')
                                    </button>

                                </form>

                            </td>

                        @endif

                    </tr>

                @empty

                    <tr>

                        <td colspan="100" class="">

                            <div class="my-6">

                                <div class="text-center">

                                    <div class="text-gray-500 text-lg">
                                        @lang('No Jobs')
                                    </div>

                                </div>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

            <tfoot class="bg-white">

                <tr>

                    <td colspan="100" class="px-6 py-4 text-gray-700 font-sm border-t-2 border-gray-200">

                        <div class="flex justify-between">

                            <div>
                                @lang('Showing')
                                @if($jobs->total() > 0)
                                    <span class="font-medium">{{ $jobs->firstItem() }}</span> @lang('to')
                                    <span class="font-medium">{{ $jobs->lastItem() }}</span> @lang('of')
                                @endif
                                <span class="font-medium">{{ $jobs->total() }}</span> @lang('result')
                            </div>

                            <div>

                                <a class="py-2 px-4 mx-1 text-xs font-medium @if(!$jobs->onFirstPage()) bg-gray-200 hover:bg-gray-300 cursor-pointer @else text-gray-600 bg-gray-100 cursor-not-allowed @endif rounded"
                                   @if(!$jobs->onFirstPage()) href="{{ $jobs->previousPageUrl() }}" @endif>
                                    @lang('Previous')
                                </a>

                                <a class="py-2 px-4 mx-1 text-xs font-medium @if($jobs->hasMorePages()) bg-gray-200 hover:bg-gray-300 cursor-pointer @else text-gray-600 bg-gray-100 cursor-not-allowed @endif rounded"
                                   @if($jobs->hasMorePages()) href="{{ $jobs->url($jobs->currentPage() + 1) }}" @endif>
                                    @lang('Next')
                                </a>

                            </div>

                        </div>

                    </td>

                </tr>

            </tfoot>

        </table>

    </div>

    @if(config('queue-monitor.ui.allow_purge'))

        <div class="mt-12">

            <form action="{{ route('queue-monitor::purge') }}" method="post">

                @csrf
                @method('delete')

                <button class="px-3 py-1 bg-red-200 hover:bg-red-300 text-red-800 text-xs font-medium uppercase tracking-wider text-white rounded">
                    @lang('Delete all entries')
                </button>

            </form>

        </div>

    @endif

</body>

</html>
