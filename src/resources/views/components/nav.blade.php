@php
$pages = [
    (object) ['link' => '/', 'name' => 'Home'],
    (object) ['link' => '/about', 'name' => 'About'],
    (object) ['link' => '/contacts', 'name' => 'Contacts'],
];

@endphp

<nav>
    @foreach ($pages as $page)
        <a
            @if ($page->link !== request()->path()) |
                href="{{ $page->link }}"
            @endif
        >{{ $page->name }}</a>
    @endforeach
</nav>
 