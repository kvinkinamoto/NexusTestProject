{{-- Recursive: included by itself for every level of children. --}}
<ul class="nexus-menu">
    @foreach($items as $item)
        <li class="nexus-menu__item">
            <a href="{{ $item->resolvedUrl() }}" @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif>
                @if($item->icon)<span class="nexus-menu__icon">{{ $item->icon }}</span>@endif
                {{ $item->title }}
            </a>

            @php($children = $item->activeChildren()->get())
            @if($children->isNotEmpty())
                @include('menu::public.menu-node', ['items' => $children])
            @endif
        </li>
    @endforeach
</ul>
