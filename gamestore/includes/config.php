<?php
define('SITE_NAME', 'GameStore');
define('SITE_TAGLINE', 'Top Up Game & Item Terlengkap & Termurah');
define('WHATSAPP_NUMBER', '6281234567890');

function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

$games = [
    [
        'id' => 1,
        'name' => 'Mobile Legends',
        'slug' => 'mobile-legends',
        'category' => 'Mobile',
        'currency' => 'Diamond',
        'color' => '#1a6fc4',
        'icon' => '⚔️',
        'badge' => 'Terlaris',
        'packages' => [
            ['amount' => 86,   'price' => 13000,  'bonus' => 0],
            ['amount' => 172,  'price' => 25000,  'bonus' => 0],
            ['amount' => 257,  'price' => 37000,  'bonus' => 0],
            ['amount' => 344,  'price' => 50000,  'bonus' => 0],
            ['amount' => 514,  'price' => 73000,  'bonus' => 14],
            ['amount' => 706,  'price' => 100000, 'bonus' => 21],
            ['amount' => 1412, 'price' => 193000, 'bonus' => 42],
            ['amount' => 2195, 'price' => 300000, 'bonus' => 65],
        ]
    ],
    [
        'id' => 2,
        'name' => 'Free Fire',
        'slug' => 'free-fire',
        'category' => 'Mobile',
        'currency' => 'Diamond',
        'color' => '#f97316',
        'icon' => '🔥',
        'badge' => 'Populer',
        'packages' => [
            ['amount' => 70,   'price' => 11000,  'bonus' => 0],
            ['amount' => 140,  'price' => 21000,  'bonus' => 0],
            ['amount' => 355,  'price' => 50000,  'bonus' => 0],
            ['amount' => 720,  'price' => 100000, 'bonus' => 0],
            ['amount' => 1450, 'price' => 193000, 'bonus' => 0],
            ['amount' => 2180, 'price' => 285000, 'bonus' => 0],
        ]
    ],
    [
        'id' => 3,
        'name' => 'PUBG Mobile',
        'slug' => 'pubg-mobile',
        'category' => 'Mobile',
        'currency' => 'UC',
        'color' => '#eab308',
        'icon' => '🎯',
        'badge' => '',
        'packages' => [
            ['amount' => 60,   'price' => 25000,  'bonus' => 0],
            ['amount' => 300,  'price' => 98000,  'bonus' => 25],
            ['amount' => 600,  'price' => 190000, 'bonus' => 60],
            ['amount' => 1500, 'price' => 455000, 'bonus' => 150],
            ['amount' => 3000, 'price' => 900000, 'bonus' => 300],
        ]
    ],
    [
        'id' => 4,
        'name' => 'Genshin Impact',
        'slug' => 'genshin-impact',
        'category' => 'Mobile',
        'currency' => 'Genesis Crystal',
        'color' => '#8b5cf6',
        'icon' => '✨',
        'badge' => 'Baru',
        'packages' => [
            ['amount' => 60,   'price' => 13500,  'bonus' => 0],
            ['amount' => 300,  'price' => 65000,  'bonus' => 30],
            ['amount' => 980,  'price' => 200000, 'bonus' => 110],
            ['amount' => 1980, 'price' => 395000, 'bonus' => 260],
            ['amount' => 3280, 'price' => 645000, 'bonus' => 600],
            ['amount' => 6480, 'price' => 1250000,'bonus' => 1600],
        ]
    ],
    [
        'id' => 5,
        'name' => 'Call of Duty Mobile',
        'slug' => 'codm',
        'category' => 'Mobile',
        'currency' => 'CP',
        'color' => '#22c55e',
        'icon' => '🎖️',
        'badge' => '',
        'packages' => [
            ['amount' => 80,   'price' => 16500,  'bonus' => 0],
            ['amount' => 400,  'price' => 75000,  'bonus' => 0],
            ['amount' => 800,  'price' => 145000, 'bonus' => 0],
            ['amount' => 2000, 'price' => 345000, 'bonus' => 0],
            ['amount' => 4000, 'price' => 680000, 'bonus' => 0],
        ]
    ],
    [
        'id' => 6,
        'name' => 'Valorant',
        'slug' => 'valorant',
        'category' => 'PC',
        'currency' => 'VP',
        'color' => '#ef4444',
        'icon' => '🎮',
        'badge' => 'Hot',
        'packages' => [
            ['amount' => 475,  'price' => 20000,  'bonus' => 0],
            ['amount' => 950,  'price' => 39000,  'bonus' => 0],
            ['amount' => 2050, 'price' => 82000,  'bonus' => 100],
            ['amount' => 3650, 'price' => 145000, 'bonus' => 150],
            ['amount' => 5350, 'price' => 210000, 'bonus' => 350],
            ['amount' => 11000,'price' => 420000, 'bonus' => 1000],
        ]
    ],
    [
        'id' => 7,
        'name' => 'Honkai Star Rail',
        'slug' => 'honkai-star-rail',
        'category' => 'Mobile',
        'currency' => 'Oneiric Shard',
        'color' => '#06b6d4',
        'icon' => '⭐',
        'badge' => 'Baru',
        'packages' => [
            ['amount' => 60,   'price' => 14000,  'bonus' => 0],
            ['amount' => 300,  'price' => 68000,  'bonus' => 30],
            ['amount' => 980,  'price' => 210000, 'bonus' => 110],
            ['amount' => 1980, 'price' => 410000, 'bonus' => 260],
        ]
    ],
    [
        'id' => 8,
        'name' => 'Steam Wallet',
        'slug' => 'steam',
        'category' => 'PC',
        'currency' => 'USD',
        'color' => '#1e40af',
        'icon' => '🎲',
        'badge' => '',
        'packages' => [
            ['amount' => 5,   'price' => 85000,  'bonus' => 0],
            ['amount' => 10,  'price' => 165000, 'bonus' => 0],
            ['amount' => 20,  'price' => 325000, 'bonus' => 0],
            ['amount' => 50,  'price' => 800000, 'bonus' => 0],
            ['amount' => 100, 'price' => 1580000,'bonus' => 0],
        ]
    ],
];

$categories = ['Semua', 'Mobile', 'PC'];

$testimonials = [
    ['name' => 'Budi Santoso', 'game' => 'Mobile Legends', 'text' => 'Proses top up super cepat, kurang dari 1 menit diamond sudah masuk. Recommended banget!', 'rating' => 5, 'avatar' => 'B'],
    ['name' => 'Siti Rahayu', 'game' => 'Free Fire', 'text' => 'Harga paling murah se-Indonesia, udah langganan disini dari 2022. Mantap!', 'rating' => 5, 'avatar' => 'S'],
    ['name' => 'Ahmad Rizki', 'game' => 'PUBG Mobile', 'text' => 'Awalnya ragu tapi ternyata aman banget. CS juga ramah dan responsif.', 'rating' => 5, 'avatar' => 'A'],
    ['name' => 'Dewi Lestari', 'game' => 'Genshin Impact', 'text' => 'Top up genesis crystal langsung masuk, pelayanan 24 jam. Suka!', 'rating' => 5, 'avatar' => 'D'],
];
