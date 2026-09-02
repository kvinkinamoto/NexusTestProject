@section('sectionFieldsNoTranslates' . $section->name)
    @foreach($formData['fields'] as $fieldName=> $field)
        @if(!($field->isTranslate ?? false))
            @if($field->section == $section->name)
                @if($field->type === 'userType')
                    @include('nexus::templates.field_types.userType')
                @else
                    @includeIf('nexus::templates.field_types.' . $field->type)
                @endif
            @endif
        @endif
    @endforeach
@stop
{{--@dd($formData)--}}
@section('sectionFieldsTranslates' . $section->name)
    @foreach($formData['languages']??[] as $lang)
        <div class="tab-pane fade @if($loop->first) active show @endif"
             id="tab-{{$lang}}-{{$section->name}}">
            <div class="row">
                @foreach($formData['fields'] as $fieldName => $field)
                    @if($field->section == $section->name && ($field->isTranslate ?? false))
                        @include('nexus::templates.field_types.'.$field->type, ['tab_lang' => $lang])
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
@stop


<div class="card {{ $section->class??'' }}">

    <div class="card-body">
        <div class="bg-primary profile-bg rounded-top p-5 position-relative mx-n3 mt-n3">
            <div class="avatar-lg position-absolute top-100 start-0 translate-middle ms-5">
                <div class="avatar-title bg-body rounded-circle border border-3 border-dashed-light position-relative">
                    <label for="avatarSet" class="position-absolute end-0 bottom-0">
                        <div class="avatar-xs cursor-pointer">
                            <span class="avatar-title bg-light text-dark rounded-circle">
                                <i class="{{ nexus_icon('camera') }}"></i>
                            </span>
                        </div>
                    </label>
                    <input class="hidden d-none"
                           name="avatar"
                           type="file"
                           id="avatarSet"
                           accept="image/*"
                    >

                    <img src="{{ asset(\Nodex\Nexus\helpers\Image::getStorageImage($model?->avatar)) }}"
                         alt="{{ $model?->display_name }}"
                         class="img-fluid rounded-circle">
                </div>
            </div>
        </div>
    </div>

    <div class="card-header">
        @if(isset($model))
            <div class="d-flex flex-wrap justify-content-between my-3">
                <div>
                    <h4 class="mb-1">
                        {{ $model['name'] }} {{ $model->last_name }}
                        @if(!empty($model->status))
                            @switch($model->status)
                                @case(\App\Nexus\Modules\User\Enums\Status::PENDING->value)
                                    <i class="{{ nexus_icon('clock') }} text-warning align-middle"
                                       title="@lang(lcfirst($module->name).'::translate.status')"></i>
                                    @break
                                @case(\App\Nexus\Modules\User\Enums\Status::BLOCKED->value)
                                    <i class="{{ nexus_icon('block') }} text-danger align-middle"
                                       title="@lang(lcfirst($module->name).'::translate.status')"></i>
                                    @break
                                @default
                                    <i class="{{ nexus_icon('check_verified') }} text-success align-middle"
                                       title="@lang(lcfirst($module->name).'::translate.status')"></i>
                            @endswitch
                        @endif
                    </h4>

                    @if(!empty($model->display_name))
                        <a href="#!" class="link-primary fs-15">
                            @ {{ $model->display_name }}
                        </a>
                    @endif
                </div>
            </div>
        @endif
        <div class="mt-2">
            @if(!empty($model->email))
                <p class="d-flex align-items-center gap-2 mb-1">
                    <iconify-icon icon="{{ nexus_icon('email') }}"
                                  class="fs-18 text-primary"
                    ></iconify-icon>
                    {{ $model->email }}
                </p>
            @endif
            @if(!empty($model->phone))
                <p class="d-flex align-items-center gap-2 mb-1">
                    <iconify-icon icon="{{ nexus_icon('phone') }}"
                                  class="fs-18 text-primary"
                    ></iconify-icon>
                    {{ $model->phone }}
                </p>
            @endif
            @if(!empty($model->address))
                <p class="d-flex align-items-center gap-2 mb-1">
                    <iconify-icon icon="{{ nexus_icon('location') }}" class="fs-18 text-primary"></iconify-icon>
                    {{ $model->address }}
                </p>
            @endif
        </div>
    </div>

    @if($section->hasTranslations??false)
        <ul class="nav nav-tabs" id="custom-tabs-{{ $section->class }}-tab" role="tablist">
            @foreach($formData['languages'] ?? [] as $lang)
                <li class="nav-item">
                    <a href="#tab-{{$lang}}-{{$section->name}}" data-bs-toggle="tab"
                       aria-expanded="@if($loop->first) true @else false @endif"
                       class="nav-link @if($loop->first) active @endif">
                        <span class="d-block d-sm-none">
                            <i class="{{ nexus_icon('default_icon') }}"></i>
                        </span>
                        <span class="d-none d-sm-block">
                            {{$lang}}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="card-header pb-0">
            <div class="tab-content col-12 pt-0" id="custom-tabs-{{ $section->class }}-tabContent">
                @yield('sectionFieldsTranslates' . $section->name)
            </div>
        </div>
    @endif

    <div class="card-body">
        @yield('sectionFieldsNoTranslates' . $section->name)
    </div>
</div>


