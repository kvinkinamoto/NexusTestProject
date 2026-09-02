<div class="col-12">
    <div class="card h-100 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm bg-info bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                    <iconify-icon icon="{{ nexus_icon('users') }}" class="fs-4 text-info"></iconify-icon>
                </div>
                <h4 class="mb-0 fw-bold text-dark">{{ __('role::translate.docs.title') }}</h4>
            </div>
            <p class="text-muted mt-2 mb-0">{{ __('role::translate.docs.subtitle') }}</p>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">{{ __('role::translate.docs.permissions_title') }}
                </h5>
                <p class="small text-muted mb-3">{{ __('role::translate.docs.permissions_desc') }}</p>
                <div class="bg-light p-3 rounded border small">
                    <iconify-icon icon="{{ nexus_icon('shield') }}" class="text-info me-1"></iconify-icon>
                    <strong>RBAC System:</strong> Use roles for general access levels (e.g., 'Editor', 'Manager') and
                    permissions for specific actions.
                </div>
            </div>

            <div class="mb-0">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">{{ __('role::translate.docs.tech_title') }}</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-primary mb-2 small uppercase">
                                {{ __('role::translate.docs.admin_part') }}</h6>
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2"><strong>{{ __('role::translate.docs.endpoints') }}:</strong>
                                    <code>index</code>, <code>store</code>, <code>update</code>, <code>delete</code>
                                </li>
                                <li><strong>{{ __('role::translate.docs.fields') }}:</strong> <code>name</code>,
                                    <code>display_name</code>, <code>permissions</code>, <code>users</code></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-info mb-2 small uppercase">{{ __('role::translate.docs.api_part') }}
                            </h6>
                            <p class="small text-muted mb-0">Role management is restricted to the admin panel. No public
                                API endpoints are available.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>