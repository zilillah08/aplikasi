<?php 
include 'config.php';

if (isset($_POST['kirim'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Prepared Statement untuk keamanan
    $stmt = $koneksi->prepare("SELECT * FROM userr WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        // Debugging: Tampilkan hash dari database
        // echo "Hash dari database: " . $data['password'] . "<br>";

        // Verifikasi password
        if (password_verify($pass, $data['password'])) {
            // Jika password valid, simpan session
            session_start();
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['nama_user'] = $data['nama_user'];
            $_SESSION['role'] = $data['role'];

            // Redirect sesuai role
            if ($_SESSION['role'] == 'admin') {
                header("Location: home.php");
            } else if ($_SESSION['role'] == 'mitra') {
                header("Location: ds_mitra.php");
            } else if ($_SESSION['role'] == 'peserta') {
                header("Location: ds_peserta.php");
            } else {
                header("Location: loginUser.php?aksi=eror");
            }
        } else {
            // Jika password salah
            echo "<script>
                    alert('password yang Anda masukkan salah!');
                    window.location.href = 'loginUser.php?aksi=eror';
                  </script>";
        }
    } else {
        // Jika email tidak ditemukan
        echo "<script>
                alert('Email yang Anda masukkan salah!');
                window.location.href = 'loginUser.php?aksi=eror';
              </script>";
    }
}
?>




<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title> Login WorkSmart</title>
    <!-- Favicon icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" sizes="16x16" href="./images/logoWMK.png">
    <link href="./css/style.css" rel="stylesheet">
    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Password Toggle Script -->
    <script>
    function togglePassword() {
        var passwordField = document.querySelector('input[name="password"]');
        var passwordToggle = document.querySelector('.password-toggle i');
        
        // Toggle input type
        if (passwordField.type === "password") {
        passwordField.type = "text"; // Ubah ke teks (tampilkan password)
        passwordToggle.classList.remove("fa-eye"); // Hapus ikon mata
        passwordToggle.classList.add("fa-eye-slash"); // Tambahkan ikon mata terhalang
        } else {
        passwordField.type = "password"; // Kembalikan ke password (sembunyikan password)
        passwordToggle.classList.remove("fa-eye-slash"); // Hapus ikon mata terhalang
        passwordToggle.classList.add("fa-eye"); // Tambahkan ikon mata
        }
    }
    </script>
</head>
<body class="h-100">
    <div class="authincation h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
                                    <h4 class="text-center mb-4">Masuk dan Lanjutkan <p>Perjalanan Cerdas Anda di WorkSmart</p></h4>
                                    <form action="loginUser.php" method="post">
                                        <div class="form-group">
                                            <label><strong>Email</strong></label>
                                            <input type="text" class="form-control" name="email"  required>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Password</strong></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="password" required>
                                                <button class="input-group-text password-toggle" type="button" onclick="togglePassword()">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="form-group text-right mt-2">
                                            <a href="forgot_password.php">Lupa Password?</a>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary btn-block" name="kirim">Masuk</button>
                                        </div>
                                    </form>

                                    <div class="row">
                                        <div class="new-account mt-3">
                                            <p>Kamu Belum Punya Akun? <a class="text-primary" href="register.php">Daftar</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="./vendor/global/global.min.js"></script>
    <script src="./js/quixnav-init.js"></script>
    <script src="./js/custom.min.js"></script>

</body>

</html>