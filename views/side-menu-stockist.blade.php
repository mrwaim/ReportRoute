@if($auth->manager)
@include('elements.side-menu-parent-item', [
    'folder' => 'report',
    'menu' => 'Monthly Reports',
    'menuIcon' => 'fa-calendar',
    'children' => [
        [
            'url'  => 'monthly-report-list/org',
            'menu'  => 'Monthly Report',
        ],
    ]
])
@endif