<?php
/**
 * Data sample Jasa Joki & Item Game
 * TODO: pindahkan ke tabel database (joki_services, game_items) kalau
 * nanti mau dikelola dari halaman admin. Untuk sekarang di-hardcode
 * di sini supaya listing & detail page bisa saling pakai data yang sama.
 */

function get_joki_services(): array {
    return [
        [
            'slug'=>'ml-warrior-mythic', 'game'=>'Mobile Legends', 'icon'=>'⚔️',
            'title'=>'Joki Rank Warrior → Mythic',
            'desc'=>'Naik rank aman pakai VPN, tanpa cheat, dikerjakan player pro rank Mythical Glory.',
            'price'=>150000, 'duration'=>'2-3 hari', 'rating'=>4.9, 'done'=>850, 'badge'=>'Terlaris',
            'tiers'=>[
                ['label'=>'Warrior → Elite', 'price'=>80000],
                ['label'=>'Elite → Master', 'price'=>120000],
                ['label'=>'Master → Legend', 'price'=>150000],
                ['label'=>'Legend → Mythic', 'price'=>220000],
            ],
        ],
        [
            'slug'=>'ff-bronze-gm', 'game'=>'Free Fire', 'icon'=>'🔥',
            'title'=>'Joki Rank Bronze → Grandmaster',
            'desc'=>'Push rank harian dengan target realistis, progress bisa dipantau.',
            'price'=>120000, 'duration'=>'1-2 hari', 'rating'=>4.8, 'done'=>620, 'badge'=>'',
            'tiers'=>[
                ['label'=>'Bronze → Platinum', 'price'=>60000],
                ['label'=>'Platinum → Diamond', 'price'=>90000],
                ['label'=>'Diamond → Heroic', 'price'=>120000],
                ['label'=>'Heroic → Grandmaster', 'price'=>180000],
            ],
        ],
        [
            'slug'=>'pubgm-ace-conqueror', 'game'=>'PUBG Mobile', 'icon'=>'🎯',
            'title'=>'Joki Push Rank Ace → Conqueror',
            'desc'=>'Ditangani player pro rating tinggi, rate menang dijaga stabil.',
            'price'=>250000, 'duration'=>'3-5 hari', 'rating'=>4.9, 'done'=>340, 'badge'=>'Pro Player',
            'tiers'=>[
                ['label'=>'Ace → Ace Master', 'price'=>150000],
                ['label'=>'Ace Master → Ace Dominator', 'price'=>200000],
                ['label'=>'Ace Dominator → Conqueror', 'price'=>250000],
            ],
        ],
        [
            'slug'=>'valorant-iron-diamond', 'game'=>'Valorant', 'icon'=>'🎮',
            'title'=>'Joki Rank Iron → Diamond',
            'desc'=>'Aman dari report, komunikasi minim di voice chat, akun tetap rapi.',
            'price'=>300000, 'duration'=>'4-6 hari', 'rating'=>4.9, 'done'=>210, 'badge'=>'',
            'tiers'=>[
                ['label'=>'Iron → Silver', 'price'=>120000],
                ['label'=>'Silver → Gold', 'price'=>170000],
                ['label'=>'Gold → Platinum', 'price'=>220000],
                ['label'=>'Platinum → Diamond', 'price'=>300000],
            ],
        ],
        [
            'slug'=>'genshin-abyss-full-star', 'game'=>'Genshin Impact', 'icon'=>'✨',
            'title'=>'Joki Spiral Abyss Full Star',
            'desc'=>'36 bintang penuh floor 9-12, screenshot bukti dikirim setelah selesai.',
            'price'=>80000, 'duration'=>'1 hari', 'rating'=>5.0, 'done'=>480, 'badge'=>'Hot',
            'tiers'=>[
                ['label'=>'Floor 9-10 (12 bintang)', 'price'=>30000],
                ['label'=>'Floor 9-11 (24 bintang)', 'price'=>55000],
                ['label'=>'Floor 9-12 (36 bintang penuh)', 'price'=>80000],
            ],
        ],
        [
            'slug'=>'hsr-simulated-universe', 'game'=>'Honkai Star Rail', 'icon'=>'⭐',
            'title'=>'Joki Simulated Universe',
            'desc'=>'Farming reward otomatis dan cepat, cocok buat kejar event terbatas.',
            'price'=>70000, 'duration'=>'1 hari', 'rating'=>4.9, 'done'=>190, 'badge'=>'',
            'tiers'=>[
                ['label'=>'World 1-4', 'price'=>40000],
                ['label'=>'World 5-8', 'price'=>70000],
            ],
        ],
        [
            'slug'=>'codm-legendary-mythic', 'game'=>'Call of Duty Mobile', 'icon'=>'🎖️',
            'title'=>'Joki Rank Legendary → Mythic',
            'desc'=>'Push rank Battle Royale maupun Multiplayer, sesuai pilihanmu.',
            'price'=>170000, 'duration'=>'2-4 hari', 'rating'=>4.8, 'done'=>140, 'badge'=>'',
            'tiers'=>[
                ['label'=>'Legendary → Legendary III', 'price'=>90000],
                ['label'=>'Legendary III → Mythic', 'price'=>170000],
            ],
        ],
        [
            'slug'=>'ml-winrate-boost', 'game'=>'Mobile Legends', 'icon'=>'⚔️',
            'title'=>'Joki Win Rate Booster',
            'desc'=>'Naikkan win rate untuk syarat turnamen/emblem, main solo Q rapi.',
            'price'=>100000, 'duration'=>'1-2 hari', 'rating'=>4.7, 'done'=>95, 'badge'=>'',
            'tiers'=>[
                ['label'=>'10 match', 'price'=>50000],
                ['label'=>'20 match', 'price'=>100000],
            ],
        ],
    ];
}

function get_joki_by_slug(string $slug): ?array {
    foreach (get_joki_services() as $j) if ($j['slug'] === $slug) return $j;
    return null;
}

function get_game_items(): array {
    return [
        [
            'slug'=>'ml-akun-mythic-fullskin', 'game'=>'Mobile Legends', 'icon'=>'⚔️',
            'name'=>'Akun Mythic Full Skin Epic', 'type'=>'Akun',
            'price'=>850000, 'stock'=>3, 'rating'=>4.9,
            'desc'=>'Akun rank Mythic, 40+ hero, 60+ skin (termasuk Epic & Legend), email masih bisa diganti.',
        ],
        [
            'slug'=>'ff-bundle-elite-pass', 'game'=>'Free Fire', 'icon'=>'🔥',
            'name'=>'Bundle Skin Elite Pass Season Ini', 'type'=>'Item',
            'price'=>45000, 'stock'=>12, 'rating'=>4.8,
            'desc'=>'Bundle skin eksklusif Elite Pass musim berjalan, dikirim langsung ke akun via UID.',
        ],
        [
            'slug'=>'pubgm-akun-conqueror', 'game'=>'PUBG Mobile', 'icon'=>'🎯',
            'name'=>'Akun Conqueror + Skin Mythic', 'type'=>'Akun',
            'price'=>1200000, 'stock'=>1, 'rating'=>4.9,
            'desc'=>'Akun season ini rank Conqueror, koleksi skin senjata Mythic lengkap, KD tinggi.',
        ],
        [
            'slug'=>'pubgm-uc-8100-bonus', 'game'=>'PUBG Mobile', 'icon'=>'🎯',
            'name'=>'UC 8100 + Bonus Item Event', 'type'=>'Item',
            'price'=>950000, 'stock'=>5, 'rating'=>4.7,
            'desc'=>'Top up UC 8100 plus bonus crate event terbatas, proses instan ke ID kamu.',
        ],
        [
            'slug'=>'genshin-akun-ar60', 'game'=>'Genshin Impact', 'icon'=>'✨',
            'name'=>'Akun AR60 5-Star Lengkap', 'type'=>'Akun',
            'price'=>2500000, 'stock'=>2, 'rating'=>5.0,
            'desc'=>'AR60, 10+ karakter 5★ (termasuk limited), weapon signature lengkap, email ganti bisa.',
        ],
        [
            'slug'=>'valorant-skin-elderflame', 'game'=>'Valorant', 'icon'=>'🎮',
            'name'=>'Skin Bundle Elderflame', 'type'=>'Item',
            'price'=>650000, 'stock'=>4, 'rating'=>4.8,
            'desc'=>'Full bundle Elderflame (Vandal, Operator, Judge, Knife) langsung terpasang ke akunmu.',
        ],
        [
            'slug'=>'hsr-akun-5star', 'game'=>'Honkai Star Rail', 'icon'=>'⭐',
            'name'=>'Akun 3x Karakter 5-Star', 'type'=>'Akun',
            'price'=>1450000, 'stock'=>2, 'rating'=>4.9,
            'desc'=>'3 karakter 5★ termasuk limited, light cone signature, Trailblaze Level 60+.',
        ],
        [
            'slug'=>'codm-akun-legendary', 'game'=>'Call of Duty Mobile', 'icon'=>'🎖️',
            'name'=>'Akun Rank Legendary + Skin Mythic', 'type'=>'Akun',
            'price'=>780000, 'stock'=>3, 'rating'=>4.7,
            'desc'=>'Rank Legendary BR & MP, koleksi skin senjata Mythic dan karakter langka.',
        ],
    ];
}

function get_item_by_slug(string $slug): ?array {
    foreach (get_game_items() as $i) if ($i['slug'] === $slug) return $i;
    return null;
}

function joki_games_list(): array {
    return array_values(array_unique(array_column(get_joki_services(), 'game')));
}
function item_games_list(): array {
    return array_values(array_unique(array_column(get_game_items(), 'game')));
}
