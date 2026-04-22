<?php
// Contoh Materi Function di PHP

// 1. Function sederhana tanpa parameter
function sapa() {
    echo "Halo, selamat datang di materi function!<br>";
}

// Memanggil function
sapa();

// 2. Function dengan parameter
function sapaNama($nama) {
    echo "Halo, $nama! Selamat belajar PHP.<br>";
}

// Memanggil function dengan parameter
sapaNama("Andi");
sapaNama("Budi");

// 3. Function dengan return value
function tambah($a, $b) {
    return $a + $b;
}

// Menggunakan return value
$hasil = tambah(5, 3);
echo "Hasil penjumlahan 5 + 3 = $hasil<br>";

// 4. Function dengan parameter default
function hitungLuas($panjang, $lebar = 10) {
    return $panjang * $lebar;
}

echo "Luas dengan panjang 5, lebar default = " . hitungLuas(5) . "<br>";
echo "Luas dengan panjang 5, lebar 8 = " . hitungLuas(5, 8) . "<br>";

// 5. Function rekursif (contoh faktorial)
function faktorial($n) {
    if ($n <= 1) {
        return 1;
    } else {
        return $n * faktorial($n - 1);
    }
}

echo "Faktorial 5 = " . faktorial(5) . "<br>";

// 6. Function dengan reference parameter
function tambahSatu(&$nilai) {
    $nilai++;
}

$x = 10;
echo "Nilai x sebelum: $x<br>";
tambahSatu($x);
echo "Nilai x setelah: $x<br>";
?>