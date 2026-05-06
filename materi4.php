<?php
// Data user (biasanya ini dari database)
$stored_email = "admin@gmail.com";
$stored_password = "12345";

// Function login
function login($email, $password, $stored_email, $stored_password)
{
    if ($email == $stored_email) {
        if ($password == $stored_password) {
            return "Login Berhasil";
        } else {
            return "Password Salah";
        }
    } else {
        return "Email tidak ditemukan";
    }
}

// Cek jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $hasil = login($email, $password, $stored_email, $stored_password);
}
?>


<form method="POST">
    <input type="email" name="email" placeholder="Masukkan Email" required><br><br>
    <input type="password" name="password" placeholder="Masukkan Password" required><br><br>
    <button type="submit">Login</button>
</form>

<?php
// Tampilkan hasil login
if (isset($hasil)) {
    echo "<h3>$hasil</h3>";
}
?>