<div class="col-12">
    <div class="card h-100 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <div class="d-flex align-items-center gap-3">
                <div
                    class="avatar-sm bg-warning bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                    <iconify-icon icon="{{ nexus_icon('lock') }}" class="fs-4 text-warning"></iconify-icon>
                </div>
                <h4 class="mb-0 fw-bold text-dark">{{ __('auth::translate.docs.title') }}</h4>
            </div>
            <p class="text-muted mt-2 mb-0">{{ __('auth::translate.docs.subtitle') }}</p>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">{{ __('auth::translate.docs.guards_title') }}</h5>
                <p class="small text-muted mb-3">{{ __('auth::translate.docs.guards_desc') }}</p>
                <div class="alert alert-warning border-0 shadow-none small">
                    <iconify-icon icon="{{ nexus_icon('shield_warning') }}" class="me-1"></iconify-icon>
                    Session lifetime and security settings can be adjusted in <code>config/session.php</code>.
                </div>
            </div>

            <div class="mb-0">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">{{ __('auth::translate.docs.tech_title') }}</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-primary mb-2 small uppercase">
                                {{ __('auth::translate.docs.admin_part') }}</h6>
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2"><strong>{{ __('auth::translate.docs.endpoints') }}:</strong>
                                    <code>login</code>, <code>logout</code>, <code>password.request</code></li>
                                <li><strong>{{ __('auth::translate.docs.fields') }}:</strong> <code>email</code>,
                                    <code>password</code>, <code>remember</code></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-info mb-2 small uppercase">{{ __('auth::translate.docs.api_part') }}
                            </h6>
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2"><strong>{{ __('auth::translate.docs.endpoints') }}:</strong>
                                    <code>/api/login</code>, <code>/api/register</code>, <code>/api/logout</code>,
                                    <code>/api/me</code></li>
                                <li><strong>{{ __('auth::translate.docs.fields') }}:</strong> <code>email</code>,
                                    <code>password</code>, <code>name</code>, <code>device_name</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>