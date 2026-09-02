<div class="col-12">
    <div class="card h-100 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <div class="d-flex align-items-center gap-3">
                <div
                    class="avatar-sm bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                    <iconify-icon icon="{{ nexus_icon('user') }}" class="fs-4 text-primary"></iconify-icon>
                </div>
                <h4 class="mb-0 fw-bold text-dark">{{ __('user::translate.docs.title') }}</h4>
            </div>
            <p class="text-muted mt-2 mb-0">{{ __('user::translate.docs.subtitle') }}</p>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="mb-5">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    {{ __('user::translate.docs.roles_permissions_title') }}
                </h5>
                <p class="small text-muted mb-3">{{ __('user::translate.docs.roles_permissions_desc') }}</p>
                <div class="alert alert-info border-0 shadow-none small">
                    <iconify-icon icon="{{ nexus_icon('info') }}" class="me-1"></iconify-icon>
                    {{ __('User permissions are merged from their assigned roles and direct permission assignments.') }}
                </div>
            </div>

            <div class="mb-0">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">{{ __('user::translate.docs.password_title') }}
                </h5>
                <p class="small text-muted mb-3">{{ __('user::translate.docs.password_desc') }}</p>
                <div class="code-block small mb-4">
                    // Validation rules applied:<br>
                    'password' => ['required', 'min:8', 'confirmed']
                </div>
            </div>

            <div class="mb-0 pt-2">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">{{ __('user::translate.docs.tech_title') }}</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-primary mb-2 small uppercase">
                                {{ __('user::translate.docs.admin_part') }}</h6>
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2"><strong>{{ __('user::translate.docs.endpoints') }}:</strong>
                                    <code>index</code>, <code>store</code>, <code>update</code>, <code>delete</code>
                                </li>
                                <li><strong>{{ __('user::translate.docs.fields') }}:</strong> <code>email</code>,
                                    <code>name</code>, <code>permissions</code>, <code>roles</code>,
                                    <code>password</code></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-info mb-2 small uppercase">{{ __('user::translate.docs.api_part') }}
                            </h6>
                            <p class="small text-muted mb-0">User resource is currently managed internally for
                                administration. No public API endpoints are exposed by default in the User module
                                itself.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>