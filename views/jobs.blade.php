<!DOCTYPE html>
<html lang="en">

<head>
    <title>Jobs</title>
    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">

</head>

<body class="font-sans p-6 pb-64 bg-blue-100">

    <h1 class="mb-6 text-5xl text-blue-900 font-bold">
        Queue Monitor
    </h1>

    <div class="overflow-x-auto shadow-lg">

        <table class="w-full rounded whitespace-no-wrap">

            <thead class="bg-gray-100">

                <tr>
                    <th class="px-3 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">Status</th>
                    <th class="px-3 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">Job</th>
                    <th class="px-3 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">Details</th>
                    <th class="px-3 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">Progress</th>
                    <th class="px-3 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">Duration</th>
                    <th class="px-3 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">Error</th>
                </tr>

            </thead>

            <tbody class="bg-white">

                @forelse($jobs as $job)

                    <tr class="font-sm leading-relaxed">

                        <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">

                            @if(!$job->is_finished)

                                <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-blue-200 text-blue-800">
                                    Running
                                </div>

                            @elseif($job->has_succeeded)

                                <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-green-200 text-green-800">
                                    Success
                                </div>

                            @else

                                <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-red-200 text-red-800">
                                    Failed
                                </div>

                            @endif

                        </td>

                        <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">
                            {{ $job->id }}
                        </td>

                        <td class="p-4 text-gray-800 text-sm leading-5 font-medium border-b border-gray-200">

                            {{ $job->getBaseName() }}

                            <span class="ml-1 text-xs text-gray-600">
                                #{{ $job->job_id }}
                            </span>

                        </td>

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
                            {{ $job->started_at->diffForHumans() }}
                        </td>

                        <td class="p-4 text-gray-800 text-sm leading-5 border-b border-gray-200">

                            @if($job->has_failed)

                                <textarea rows="4" class="w-64 text-xs p-1 border rounded" readonly>{{ $job->exception }}</textarea>

                            @else
                                -
                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="100" class="">

                            <div class="my-6">

                                <div class="text-center">

                                    <div class="text-gray-500 text-lg">
                                        No Jobs
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
                                Showing
                                @if($jobs->total() > 0)
                                    <span class="font-medium">{{ $jobs->firstItem() }}</span> to
                                    <span class="font-medium">{{ $jobs->lastItem() }}</span> of
                                @endif
                                <span class="font-medium">{{ $jobs->total() }}</span> result
                            </div>

                            <div>

                                <a class="py-2 px-4 mx-1 text-xs font-medium @if(!$jobs->onFirstPage()) bg-gray-200 hover:bg-gray-300 cursor-pointer @else text-gray-600 bg-gray-100 cursor-not-allowed @endif rounded"
                                   @if(!$jobs->onFirstPage()) href="{{ $jobs->previousPageUrl() }}" @endif>
                                    Previous
                                </a>

                                <a class="py-2 px-4 mx-1 text-xs font-medium @if($jobs->hasMorePages()) bg-gray-200 hover:bg-gray-300 cursor-pointer @else text-gray-600 bg-gray-100 cursor-not-allowed @endif rounded"
                                   @if($jobs->hasMorePages()) href="{{ $jobs->url($jobs->currentPage() + 1) }}" @endif>
                                    Next
                                </a>

                            </div>

                        </div>

                    </td>

                </tr>

            </tfoot>

        </table>

    </div>

</body>

</html>
