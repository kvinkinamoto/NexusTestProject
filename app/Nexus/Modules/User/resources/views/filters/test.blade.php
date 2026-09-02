<select style="display: none;"
        class="form-control select2 dropdown"
        name="filter[trashed]" id="trashed"
        onchange="document.getElementById('filter-form').submit()">
    <option value="">{{ __('user::translate.chooseTrashed') }}</option>
    <option
        value="with" {{ Request::input('filter.trashed') === 'with' ? 'selected' : '' }}>
        {{ __('user::translate.withTrashed') }}
    </option>
    <option
        value="only" {{ Request::input('filter.trashed') === 'only' ? 'selected' : '' }}>
        {{ __('user::translate.onlyTrashed') }}
    </option>
</select>
<div class="dropdown">
    <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light" data-bs-toggle="dropdown" aria-expanded="false">
        @if(Request::input('filter.trashed') == 'with')
            {{ __('user::translate.withTrashed') }}
        @elseif(Request::input('filter.trashed') == 'only')
            {{ __('user::translate.onlyTrashed') }}
        @else
            {{ __('user::translate.chooseTrashed') }}
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-end">
        <a href="#"
           onclick="document.getElementById('trashed').value = '';document.getElementById('filter-form').submit()"
           class="dropdown-item">
            {{ __('user::translate.chooseTrashed') }}
        </a>
        <a href="#"
           onclick="document.getElementById('trashed').value = 'with';document.getElementById('filter-form').submit()"
           class="dropdown-item">
            {{ __('user::translate.withTrashed') }}
        </a>
        <a href="#"
           onclick="document.getElementById('trashed').value = 'only';document.getElementById('filter-form').submit()"
           class="dropdown-item">
            {{ __('user::translate.onlyTrashed') }}
        </a>
    </div>
</div>
