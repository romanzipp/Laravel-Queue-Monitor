<div class="w-full md:w-1/3 px-4 mb-4">

    <div class="h-full flex flex-col justify-between p-6 bg-white rounded shadow-md">

        <div class="font-semibold text-sm text-gray-600">
            {{ $metric->title }}
        </div>

        <div>

            <div class="mt-2 text-3xl">
                {{ $metric->format($metric->value) }}
            </div>

            @if($metric->previousValue !== null)

                <div class="mt-2 text-sm font-semibold {{ $metric->hasChanged() ? ($metric->hasIncreased() ? 'text-green-700' : 'text-red-800') : 'text-gray-800' }}">

                    @if($metric->hasChanged())
                        @if($metric->hasIncreased())
                            Up from
                        @else
                            Down from
                        @endif
                    @else
                        No change from
                    @endif

                    {{ $metric->format($metric->previousValue) }}
                </div>

            @endif

        </div>

    </div>

</div>
