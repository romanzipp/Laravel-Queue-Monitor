<div class="px-6 py-4 mb-6 pl-4 bg-white dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">

    <form action="" method="get">

        <div class="flex items-center my-2 -mx-2">

            <div class="px-2 w-1/4">
                <label for="filter_name"
                       class="block mb-1 text-xs font-light text-gray-500">
                    @lang('Job name')
                </label>
                <input type="text"
                       id="filter_name"
                       name="name"
                       value="{{ $filters['name'] ?? null }}"
                       placeholder="ExampleJob"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-sm">
            </div>

            @if(config('queue-monitor.ui.show_custom_data'))
                <div class="px-2 w-1/4">
                    <label for="filter_custom_data"
                           class="block mb-1 text-xs font-light text-gray-500">
                        @lang('Custom Data')
                    </label>
                    <input type="text"
                           id="filter_custom_data"
                           name="custom_data"
                           value="{{ $filters['custom_data'] ?? null }}"
                           placeholder="Example Custom Data"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-sm">
                </div>
            @endif

            <div class="px-2 w-1/4">
                <label for="filter_status"
                       class="block mb-1 text-xs font-light text-gray-500">
                    @lang('Status')
                </label>
                <select name="status"
                        id="filter_status"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-sm appearance-none">

                    <option @if($filters['status'] === null) selected @endif value="">
                        @lang('All')
                    </option>

                    @foreach($statuses as $status => $statusName)
                        <option @if($filters['status'] === $status) selected @endif value="{{ $status }}">
                            {{ $statusName }}
                        </option>

                    @endforeach
                </select>
            </div>

            <div class="px-2 w-1/4">
                <label for="filter_queues"
                       class="block mb-1 text-xs font-light text-gray-500">
                    @lang('Queues')
                </label>
                <select name="queue"
                        id="filter_queues"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-sm appearance-none">

                    <option value="all">
                        All
                    </option>

                    @foreach($queues as $queue)
                        <option @if($filters['queue'] === $queue) selected @endif value="{{ $queue }}">
                            {{ e($queue) }}
                        </option>
                    @endforeach

                </select>
            </div>

        </div>

        <div class="flex justify-between mt-4">
            <button type="submit"
                    class="py-2 px-4 bg-blue-50 dark:bg-blue-200 dark:hover:bg-blue-300 hover:bg-blue-100 text-blue-800 text-xs font-medium rounded-md transition-colors duration-150">
                @lang('Apply Filter')
            </button>

            <a href="{{ route('queue-monitor::index') }}"
               class="py-2 px-4 bg-gray-50 dark:bg-gray-200 dark:hover:bg-gray-300 hover:bg-gray-100 text-gray-800 text-xs font-medium rounded-md transition-colors duration-150">
                @lang('Reset Filter')
            </a>
        </div>

    </form>

</div>
