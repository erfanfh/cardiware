@foreach($rows as $row)
    @if($row['layout_type'] === 'split' && count($row['areas']) === 2)
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            @foreach($row['areas'] as $area)
                @php
                    $widthClass = match ((int) $area['width']) {
                        1 => 'lg:col-span-1',
                        2 => 'lg:col-span-2',
                        3 => 'lg:col-span-3',
                        4 => 'lg:col-span-4',
                        5 => 'lg:col-span-5',
                        6 => 'lg:col-span-6',
                        7 => 'lg:col-span-7',
                        8 => 'lg:col-span-8',
                        9 => 'lg:col-span-9',
                        10 => 'lg:col-span-10',
                        11 => 'lg:col-span-11',
                        default => 'lg:col-span-12',
                    };
                @endphp

                @if($area['key'] === 'content_sidebar')
                    <aside class="{{ $widthClass }} flex flex-col gap-4">
                        @foreach($area['elements'] as $element)
                            @include($element['view'], ['layoutElement' => $element])
                        @endforeach
                    </aside>
                @else
                    <div class="{{ $widthClass }}">
                        @foreach($area['elements'] as $element)
                            @include($element['view'], ['layoutElement' => $element])
                        @endforeach
                    </div>
                @endif
            @endforeach
        </section>
    @else
        @foreach($row['areas'] as $area)
            @foreach($area['elements'] as $element)
                @include($element['view'], ['layoutElement' => $element])
            @endforeach
        @endforeach
    @endif
@endforeach
