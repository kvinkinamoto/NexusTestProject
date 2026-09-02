@if(!empty($model->language))
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <span class="badge p-1 bg-light text-dark fs-12">
                {{ strtoupper($model->language) }}
            </span>
        </div>
    </div>
@endif
