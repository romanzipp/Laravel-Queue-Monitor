@switch($status)

    @case(\romanzipp\QueueMonitor\Enums\MonitorStatus::RUNNING)
        <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-blue-200 text-blue-800">
            Running
        </div>
        @break

    @case(\romanzipp\QueueMonitor\Enums\MonitorStatus::SUCCEEDED)
        <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-green-200 text-green-800">
            Success
        </div>
        @break

    @case(\romanzipp\QueueMonitor\Enums\MonitorStatus::FAILED)
        <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-red-200 text-red-800">
            Failed
        </div>
        @break

    @case(\romanzipp\QueueMonitor\Enums\MonitorStatus::STALE)
        <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-gray-700 text-gray-200">
            Stale
        </div>
        @break

@endswitch
