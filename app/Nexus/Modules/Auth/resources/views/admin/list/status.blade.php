<span class="badge bg-{{ $statuses[$model->status]['type'] }}-subtle text-{{ $statuses[$model->status]['type'] }} py-1 px-2">
    {{ __('Auth::Auth.status_' . $statuses[$model->status]['name']) }}
</span>
