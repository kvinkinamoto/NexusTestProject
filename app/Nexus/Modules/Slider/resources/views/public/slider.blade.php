{{--
    Usable from any page/theme via `@include('slider::public.slider', ['key' => 'homepage'])`
    — not a `<x-nexus::...>` tag component: that namespace belongs to the
    package (packages/nodex/nexus), which must not depend on an app-level
    module's Eloquent model. This partial does its own query, so it stays
    inside the Slider module itself.

    $key selects which named, independently-published slider to render (a
    site can have several — "homepage", "checkout-promo", etc.). Silently
    renders nothing if that key doesn't exist or is inactive, same as an
    empty slider — a missing slot shouldn't break the page.
--}}
@php
    $slider = \App\Nexus\Modules\Slider\Models\Slider::query()
        ->where('key', $key ?? null)
        ->where('is_active', true)
        ->with('slides')
        ->first();
@endphp

@php($slideCount = $slider?->slides->count() ?? 0)
@if($slider && $slideCount > 0)
    <div class="nexus-slider" data-slider-key="{{ $slider->key }}"
        @if($slideCount > 1) x-data="nexusSlider({{ $slideCount }})" @endif>
        <div class="nexus-slider__track" @if($slideCount > 1) :style="`transform: translateX(-${active * 100}%)`" @endif>
            @foreach($slider->slides as $slide)
                <div class="nexus-slider__slide">
                    @if($slide->image)
                        <img src="/{{ ltrim($slide->image, '/') }}" alt="{{ $slide->title }}" class="nexus-slider__image">
                    @endif

                    @if($slide->title || $slide->subtitle)
                        <div class="nexus-slider__caption">
                            @if($slide->title)
                                <h2 class="nexus-slider__title">{{ $slide->title }}</h2>
                            @endif
                            @if($slide->subtitle)
                                <p class="nexus-slider__subtitle">{{ $slide->subtitle }}</p>
                            @endif
                            @if($slide->link_url)
                                <a href="{{ $slide->link_url }}" class="nexus-slider__link">{{ $slide->link_text ?: $slide->link_url }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if($slideCount > 1)
            <button type="button" @click="prev()" class="nexus-slider__nav nexus-slider__nav--prev" aria-label="Попередній слайд">‹</button>
            <button type="button" @click="next()" class="nexus-slider__nav nexus-slider__nav--next" aria-label="Наступний слайд">›</button>
            <div class="nexus-slider__dots">
                @for ($i = 0; $i < $slideCount; $i++)
                    <button type="button" @click="go({{ $i }})" :class="active === {{ $i }} ? 'nexus-slider__dot--active' : ''" class="nexus-slider__dot" aria-label="Слайд {{ $i + 1 }}"></button>
                @endfor
            </div>
        @endif
    </div>
@endif
