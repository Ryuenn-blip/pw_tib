<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom">

<div class="container">


<a class="navbar-brand logo" href="index.php">

<i class="bi bi-controller"></i>
GameStore

</a>


<button class="navbar-toggler" 
data-bs-toggle="collapse" 
data-bs-target="#menu">

<span class="navbar-toggler-icon"></span>

</button>



<div class="collapse navbar-collapse" id="menu">


<!-- Search -->

<form class="mx-auto search-box">

<input 
type="text" 
placeholder="Cari game, item, atau produk..."
class="form-control">

</form>



<ul class="navbar-nav me-3">

<li class="nav-item">
<a class="nav-link active" href="#">
Beranda
</a>
</li>


<li class="nav-item">
<a class="nav-link" href="#">
Kategori
</a>
</li>


<li class="nav-item">
<a class="nav-link" href="#">
Cara Order
</a>
</li>


<li class="nav-item">
<a class="nav-link" href="#">
Chat Admin
</a>
</li>


</ul>


<div class="d-flex align-items-center gap-3">


<a href="#" class="cart">

<i class="bi bi-cart3"></i>

</a>


<?php if(isset($_SESSION['user'])): ?>


<div class="dropdown">


<a class="user-menu dropdown-toggle"
data-bs-toggle="dropdown">

<img src="uploads/profile/default.png">

<?= $_SESSION['user']['nama']; ?>

</a>


<ul class="dropdown-menu dropdown-menu-dark">


<li>
<a class="dropdown-item"
href="customer/dashboard.php">
Dashboard Saya
</a>
</li>


<li>
<a class="dropdown-item"
href="customer/history.php">
Riwayat Pembelian
</a>
</li>


<li>
<a class="dropdown-item"
href="customer/profile.php">
Pengaturan Akun
</a>
</li>


<hr>


<li>
<a class="dropdown-item text-danger"
href="logout.php">
Logout
</a>
</li>


</ul>


</div>



<?php else: ?>


<a href="login.php" 
class="btn btn-login">

Login

</a>


<?php endif; ?>


</div>


</div>


</div>

</nav>