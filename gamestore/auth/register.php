<?php

require_once "../config/config.php";

if(isset($_POST['register'])){

    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);

    $password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    mysqli_query(
        $conn,
        "INSERT INTO users(
        nama,
        email,
        password
        ) VALUES(
        '$nama',
        '$email',
        '$password'
        )"
    );

    header("Location: login.php");
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body>

<div class="auth-box">

<h2>Daftar Akun</h2>

<form method="POST">

<input type="text"
name="nama"
placeholder="Nama Lengkap"
required>

<input type="email"
name="email"
placeholder="Email"
required>

<input type="password"
name="password"
placeholder="Password"
required>

<button name="register">
Daftar
</button>

</form>

<a href="login.php">
Sudah punya akun?
</a>

</div>

</body>
</html>