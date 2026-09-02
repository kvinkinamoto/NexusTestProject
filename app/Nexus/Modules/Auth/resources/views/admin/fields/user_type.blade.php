<div class="col-12 mb-3">
    <label class="form-label text-muted">{{ __('Auth::Auth.Auth_type') }}</label>

    <select name="Auth_type" class="form-control" style="width: 100%;" data-choices data-choices-removeItem>
        @foreach ($AuthTypes as $typeKey => $typeValue)
            <option value="{{ $typeKey }}" {{ old('Auth_type', $AuthType) == $typeKey ? 'selected' : '' }}>
                {{ $typeValue }}
            </option>
        @endforeach
    </select>
</div>
