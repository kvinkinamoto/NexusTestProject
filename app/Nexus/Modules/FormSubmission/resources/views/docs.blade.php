<div class="col-12">
    <div class="card h-100 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm bg-info bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                    <iconify-icon icon="{{ nexus_icon('solar:inbox-bold') }}" class="fs-4 text-info"></iconify-icon>
                </div>
                <h4 class="mb-0 fw-bold text-dark">{{ __('formSubmission::translate.docs.title') }}</h4>
            </div>
            <p class="text-muted mt-2 mb-0">{{ __('formSubmission::translate.docs.subtitle') }}</p>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">{{ __('formSubmission::translate.docs.usage_title') }}</h5>
                <p class="small text-muted mb-3">{{ __('formSubmission::translate.docs.usage_desc') }}</p>
            </div>

            <div class="mb-0">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">{{ __('formSubmission::translate.docs.tech_title') }}</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-primary mb-2 small uppercase">
                                {{ __('formSubmission::translate.docs.admin_part') }}</h6>
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2"><strong>{{ __('formSubmission::translate.docs.endpoints') }}:</strong>
                                    <code>index</code>, <code>delete</code>
                                </li>
                                <li><strong>{{ __('formSubmission::translate.docs.fields') }}:</strong> <code>form</code>,
                                    <code>data</code>, <code>ip_address</code></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-info mb-2 small uppercase">{{ __('formSubmission::translate.docs.api_part') }}
                            </h6>
                            <p class="small text-muted mb-0">No public API endpoints — submissions are written directly
                                by the Form module's public controller.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
