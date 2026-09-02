@php
    if (!isset($model) || !$model->exists) {
        return;
    }

    $addresses = $model->addresses()->orderByDesc('is_main')->orderByDesc('id')->get();
@endphp

<div class="mb-3">
    @if($addresses->isEmpty())
        <div class="text-muted">@lang('user::translate.no_addresses')</div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>@lang('user::translate.city')</th>
                        <th>@lang('user::translate.street')</th>
                        <th style="width: 100px;">@lang('user::translate.house')</th>
                        <th style="width: 100px;">@lang('user::translate.apartment')</th>
                        <th style="width: 100px;">@lang('user::translate.is_main')</th>
                        <th style="width: 170px;">@lang('user::translate.created_at')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($addresses as $address)
                        <tr>
                            <td>{{ $address->city }}</td>
                            <td>{{ $address->street }}</td>
                            <td>{{ $address->house }}</td>
                            <td>{{ $address->apartment ?? '—' }}</td>
                            <td>
                                @if($address->is_main)
                                    <span class="badge bg-success">@lang('user::translate.is_main')</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $address->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
