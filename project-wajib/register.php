<?php
include 'config.php'; 
session_start();

// Jika pengguna sudah login, arahkan kembali
if (isset($_SESSION['role'])) {
    header("Location:loginUser.php?aksi=belum");
    exit();
}

if (isset($_POST['kirim'])) {
    // Menangani input data dari form
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $nama_user = mysqli_real_escape_string($koneksi, $_POST['nama_user']);
    $no_tlp = mysqli_real_escape_string($koneksi, $_POST['no_tlp']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);
    
    // Periksa apakah email sudah digunakan
    $email_check_query = "SELECT * FROM userr WHERE email = '$email'";
    $email_check_result = mysqli_query($koneksi, $email_check_query);

    if (!$email_check_result) {
        die("Query gagal: " . mysqli_error($koneksi));
    }

    if (mysqli_num_rows($email_check_result) > 0) {
        echo "<script>
                alert('Email sudah digunakan, silakan gunakan email lain.');
                window.location.href = 'register.php?aksi=email_terpakai';
            </script>";
        exit();
    }

    // Validasi password (min. 6 karakter, harus ada huruf dan angka)
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password)) {
        echo "<script>
                alert('Password harus terdiri dari minimal 6 karakter, mengandung huruf dan angka.');
                window.location.href = 'register.php?aksi=password_tidak_valid';
              </script>";
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Menghasilkan ID pengguna baru dengan format USR + tanggal + bulan + nomor urut
    $tanggal = date('d'); // Tanggal saat ini
    $bulan = date('m');   // Bulan saat ini
    $prefix = "USR" . $tanggal . $bulan;

    // Query untuk mendapatkan ID terakhir yang dimulai dengan prefix
    $sql = mysqli_query($koneksi, "SELECT id_user FROM userr WHERE id_user LIKE '$prefix%' ORDER BY id_user DESC LIMIT 1");
    $row = mysqli_fetch_array($sql);

    if ($row) {
        $last_id = $row['id_user'];
        $last_number = (int)substr($last_id, -1); // Ambil digit terakhir dari ID terakhir
        $new_number = $last_number + 1;          // Tambahkan 1
    } else {
        $new_number = 1; // Jika tidak ada data, mulai dari 1
    }

    // Formatkan ID baru dengan zero padding
    $id_user = $prefix . $new_number;

    // Menyimpan data ke database
    $query = "INSERT INTO userr (id_user, email, password, nama_user,no_tlp, role) VALUES ('$id_user', '$email', '$hashed_password', '$nama_user','$no_tlp', '$role')";
    
    if (mysqli_query($koneksi, $query)) {
        // Jika berhasil, simpan data ke tabel peserta jika role = peserta
        if ($role == 'peserta') {
            $prefix_peserta = "PST" . $tanggal . $bulan;

            // Query untuk mendapatkan ID terakhir peserta
            $sql_peserta = mysqli_query($koneksi, "SELECT id_peserta FROM data_peserta WHERE id_peserta LIKE '$prefix_peserta%' ORDER BY id_peserta DESC LIMIT 1");
            $row_peserta = mysqli_fetch_array($sql_peserta);

            if ($row_peserta) {
                $last_id_peserta = $row_peserta['id_peserta'];
                $last_number_peserta = (int)substr($last_id_peserta, -1); // Ambil digit terakhir dari ID peserta terakhir
                $new_number_peserta = $last_number_peserta + 1;          // Tambahkan 1
            } else {
                $new_number_peserta = 1; // Jika tidak ada data, mulai dari 1
            }

            // Formatkan ID baru peserta
            $id_peserta = $prefix_peserta . $new_number_peserta;

            // Insert data ke tabel data_peserta
            $query_peserta = "INSERT INTO data_peserta (id_peserta, id_user, nama_user, email,no_tlp, status, tanggal_daftar) 
                              VALUES ('$id_peserta', '$id_user', '$nama_user','$email','$no_tlp', 'terdaftar', NOW())";

            if (!mysqli_query($koneksi, $query_peserta)) {
                echo "Error: " . mysqli_error($koneksi);
            }
        }

        // Jika role = mitra, simpan data ke tabel data_mitra
        if ($role == 'mitra') {
            $prefix_mitra = "MTR" . $tanggal . $bulan;

            // Query untuk mendapatkan ID terakhir mitra
            $sql_mitra = mysqli_query($koneksi, "SELECT id_mitra FROM data_mitra WHERE id_mitra LIKE '$prefix_mitra%' ORDER BY id_mitra DESC LIMIT 1");
            $row_mitra = mysqli_fetch_array($sql_mitra);

            if ($row_mitra) {
                $last_id_mitra = $row_mitra['id_mitra'];
                $last_number_mitra = (int)substr($last_id_mitra, -1); // Ambil digit terakhir dari ID mitra terakhir
                $new_number_mitra = $last_number_mitra + 1;          // Tambahkan 1
            } else {
                $new_number_mitra = 1; // Jika tidak ada data, mulai dari 1
            }

            // Formatkan ID baru mitra
            $id_mitra = $prefix_mitra . $new_number_mitra;

            // Insert data ke tabel data_mitra
            $query_mitra = "INSERT INTO data_mitra (id_mitra, id_user, nama_user, email,no_tlp, status, tanggal_bergabung) 
                            VALUES ('$id_mitra', '$id_user', '$nama_user', '$email','$no_tlp', 'terdaftar', NOW())";

            if (!mysqli_query($koneksi, $query_mitra)) {
                echo "Error: " . mysqli_error($koneksi);
            }
        }

        echo "<script>
                alert('Pendaftaran berhasil! Silakan login.');
                window.location.href = 'loginUser.php?aksi=sukses';
              </script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>


<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register WorkSmart </title>
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
                                    <h4 class="text-center mb-4">Optimalkan Potensi, Wujudkan Efisiensi <p> Daftar WorkSmart Hari Ini!</h4>
                                    <form action="register.php" method="POST">
                                        <div class="form-group">
                                            <label><strong>Email</strong></label>
                                            <input type="text" class="form-control" id=" " name="email"   required>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Password</strong></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="password" required>
                                                <button class="input-group-text password-toggle" type="button" onclick="togglePassword()">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                            </div>
                                            <p style="margin-top: 5px; font-size: 14px; color: #6c757d;">Gunakan huruf dan angka maksimal 6 karakter</p>
                                        </div>
                                        <div class="form-group">
                                         <label><strong>Nama Lengkap</strong></label>
                                            <input type="text" class="form-control" id=" " name="nama_user"   required>
                                        </div>
                                        <div class="form-group">
                                         <label><strong>No Handphone</strong></label>
                                            <input type="text" class="form-control" id=" " name="no_tlp"   required>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Mendaftar Sebagai</strong></label><br>
                                            <label><input type="radio" name="role" value="mitra" onclick="disableOtherOption(this)" required> Mitra</label>
                                            <label><input type="radio" name="role" value="peserta" onclick="disableOtherOption(this)" required> Peserta</label>
                                        </div>
                                       
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary btn-block" name="kirim">Lanjutkan</button>
                                        </div>
                                    </form>
                                    <div class="new-account mt-3">
                                        <p>Apakah Kamu Sudah Mempunyai Akun? <a class="text-primary" href="loginUser.php">Masuk</a></p>
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
    <!--endRemoveIf(production)-->
</body>

</html>