{{--
    Usable from any page/theme via `@include('menu::public.menu', ['key' => 'header'])`
    — not a `<x-nexus::...>` tag component, same reasoning as the Slider
    public partial: the package's `x-nexus::` namespace must not depend on an
    app-level module's Eloquent model.

    Silently renders nothing for an unknown/inactive key, same as an empty
    menu — a missing nav slot shouldn't break the page.
--}}
@php
    $menu = \App\Nexus\Modules\Menu\Models\Menu::query()
        ->where('key', $key ?? null)
        ->where('is_active', true)
        ->first();

    $items = $menu
        ? \App\Nexus\Modules\Menu\Models\MenuItem::where('menu_id', $menu->id)
            ->where('is_active', true)
            ->whereIsRoot()
            ->defaultOrder()
            ->with('linkable')
            ->get()
        : collect();
@endphp

@if($items->isNotEmpty())
    @include('menu::public.menu-node', ['items' => $items])
@endif
