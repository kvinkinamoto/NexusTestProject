<div class="d-flex align-items-center gap-2">
{{--    <img src="{{ asset(\Nodex\Nexus\Core\helpers\Image::productImage($model->avatar)) }}"--}}
{{--         alt="{{ $model->display_name }}"--}}
{{--         class="avatar-sm rounded-circle me-2" style="max-width: 200px">--}}

    <div>
        {{ $model->name }}

        @if(!empty($model->display_name))
            <p class="text-muted mb-0 mt-1 fs-13">
                @ {{ $model->display_name }}
            </p>
        @endif
    </div>
</div>
