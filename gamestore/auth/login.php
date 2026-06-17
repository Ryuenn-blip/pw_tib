<?php

require_once "../config/config.php";

$error = "";

if(isset($_POST['login']))
{
    $email = mysqli_real_escape_string(
        $conn,
        $_POST['email']
    );

    $password = $_POST['password'];

    $query = mysqli_query(
        $conn,
        "SELECT * FROM users
        WHERE email='$email'"
    );

    if(mysqli_num_rows($query) > 0)
    {
        $user = mysqli_fetch_assoc($query);

        // Jika password menggunakan password_hash()
        if(password_verify(
            $password,
            $user['password']
        ))
        {
            $_SESSION['user'] = $user;

            if($user['role'] == 'admin')
            {
                header(
                    "Location: ../admin/dashboard.php"
                );
                exit;
            }
            else
            {
                header(
                    "Location: ../customer/dashboard.php"
                );
                exit;
            }
        }
        else
        {
            $error = "Password salah!";
        }
    }
    else
    {
        $error = "Email tidak ditemukan!";
    }
}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Login GameStore</title>

<link
rel="stylesheet"
href="../assets/css/auth.css">

</head>

<body>

<div class="auth-container">

<div class="auth-card">

<div class="logo">
🎮
</div>

<h1>Login</h1>

<p>Masuk ke akun GameStore</p>

<?php if($error != ""): ?>

<div class="alert">
<?= $error ?>
</div>

<?php endif; ?>

<form method="POST">

<div class="form-group">

<label>Email</label>

<input
type="email"
name="email"
placeholder="Masukkan Email"
required>

</div>

<div class="form-group">

<label>Password</label>

<input
type="password"
name="password"
placeholder="Masukkan Password"
required>

</div>

<button
type="submit"
name="login"
class="btn-login">

Masuk

</button>

</form>

<div class="auth-footer">

Belum punya akun?

<a href="register.php">
Daftar Sekarang
</a>

</div>

</div>

</div>

</body>
</html>