<table class="w-full rounded-md whitespace-no-wrap rounded-md border border-separate border-spacing-0">

    <thead class="rounded-t-md">

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
                <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200"></th>
            @endif
        </tr>

    </thead>

    <tbody class="bg-gray-50">

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

                    <td class="p-4 eading-5 border-b border-gray-200">
                        <form action="{{ route('queue-monitor::destroy', [$job]) }}" method="post">
                            @csrf
                            @method('delete')
                            <button class="px-3 py-2 bg-transparent hover:bg-red-100 text-red-800 text-xs font-medium rounded transition-colors duration-150">
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
            <td colspan="100" class="px-2 py-4">
                <div class="flex justify-between">
                    <div class="pl-2 text-sm text-gray-600">
                        @lang('Showing')
                        @if($jobs->total() > 0)
                            <span class="font-medium">{{ $jobs->firstItem() }}</span> @lang('to')
                            <span class="font-medium">{{ $jobs->lastItem() }}</span> @lang('of')
                        @endif
                        <span class="font-medium">{{ $jobs->total() }}</span> @lang('results')
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
