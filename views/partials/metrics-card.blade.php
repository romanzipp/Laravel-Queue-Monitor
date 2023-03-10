<div class="flex flex-col justify-between p-6 bg-gray-50 border border-gray-200 rounded-md shadow-sm">

    <div class="font-semibold text-sm text-gray-600"
         title="{{ __('Last :days days', ['days' => config('queue-monitor.ui.metrics_time_frame') ?? 14]) }}">
        {{ __($metric->title) }}
    </div>

    <div>

        <div class="mt-2 text-3xl">
            {{ $metric->format($metric->value) }}
        </div>

        @if($metric->previousValue !== null)

            <div class="mt-2 text-sm font-semibold {{ $metric->hasChanged() ? ($metric->hasIncreased() ? 'text-green-700' : 'text-red-800') : 'text-gray-800' }}">

                @if($metric->hasChanged())
                    @if($metric->hasIncreased())
                        @lang('Up from')
                    @else
                        @lang('Down from')
                    @endif
                @else
                    @lang('No change from')
                @endif

                {{ $metric->format($metric->previousValue) }}
            </div>

        @endif

    </div>

</div>
