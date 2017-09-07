@include('elements.side-menu-parent-item', [
    'folder' => 'report',
    'menu' => 'Monthly Reports',
    'menuIcon' => 'fa-calendar',
    'children' => [
        [
            'url'  => 'monthly-report-list/hq',
            'menu'  => 'Monthly Reports (HQ)',
            ],
        [
            'url'  => 'monthly-report-list/org',
            'menu'  => 'Monthly Reports (BioKare)',
            ],
        [
            'url'  => 'monthly-report-list/pl',
            'menu'  => 'Monthly Reports (PL)',
            ],
        [
            'url' => 'sales-report/org',
            'menu' => 'Sales Report Org'
            ],
        [
           'url' => 'sales-report/pl',
            'menu' => 'Sales Report PL'
        ]
    ]
])
