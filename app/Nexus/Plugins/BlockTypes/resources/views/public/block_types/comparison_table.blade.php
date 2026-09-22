@php
    $columns = [
        $data->own_column_label ?? null,
        $data->column2_label ?? null,
        $data->column3_label ?? null,
        $data->column4_label ?? null,
    ];

    $rows = [];
    for ($i = 1; $i <= 6; $i++) {
        $feature = $data->{"row{$i}_feature"} ?? null;
        if ($feature) {
            $rows[] = [
                'feature' => $feature,
                'own' => $data->{"row{$i}_own"} ?? null,
                'col2' => $data->{"row{$i}_col2"} ?? null,
                'col3' => $data->{"row{$i}_col3"} ?? null,
                'col4' => $data->{"row{$i}_col4"} ?? null,
            ];
        }
    }
@endphp
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    @if($data->heading ?? null)
        <h2 class="font-outfit text-2xl sm:text-3xl font-bold text-center mb-10">{{ $data->heading }}</h2>
    @endif

    @if(count($rows) && $columns[0])
        <div class="overflow-x-auto rounded-2xl border border-neutral-200">
            <table class="w-full min-w-[640px] border-collapse text-sm">
                <thead>
                    <tr class="bg-neutral-50">
                        <th class="px-4 py-3.5 text-left font-medium text-neutral-500 w-1/5"></th>
                        <th class="px-4 py-3.5 text-center font-outfit font-bold text-neutral-900 bg-sand-100 border-x border-sand-300">{{ $columns[0] }}</th>
                        @foreach(array_slice($columns, 1) as $col)
                            @if($col)
                                <th class="px-4 py-3.5 text-center font-medium text-neutral-600">{{ $col }}</th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr class="border-t border-neutral-200">
                            <td class="px-4 py-3.5 font-medium text-neutral-700">{{ $row['feature'] }}</td>
                            <td class="px-4 py-3.5 text-center font-semibold text-neutral-900 bg-sand-50 border-x border-sand-200">{{ $row['own'] }}</td>
                            @if($columns[1])
                                <td class="px-4 py-3.5 text-center text-neutral-500">{{ $row['col2'] }}</td>
                            @endif
                            @if($columns[2])
                                <td class="px-4 py-3.5 text-center text-neutral-500">{{ $row['col3'] }}</td>
                            @endif
                            @if($columns[3])
                                <td class="px-4 py-3.5 text-center text-neutral-500">{{ $row['col4'] }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
