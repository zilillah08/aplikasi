<?php
include 'config.php'; 
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['role'])) {
    header("Location:loginUser.php?aksi=belum");
    exit();
}

// Ambil data dari sesi
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

// Koneksi ke database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "worksmart";

$conn = new mysqli($host, $user, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mengambil data pengguna berdasarkan sesi
$sql = "SELECT id_user, email, nama_user, no_tlp, role, gambar FROM userr WHERE id_user = ? AND role = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_user, $role);
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah data ditemukan
if ($result->num_rows > 0) {
    $userr = $result->fetch_assoc();

    // Set nilai ke session
    $_SESSION['email'] = $userr['email'];
    $_SESSION['nama_user'] = $userr['nama_user'];
    $_SESSION['no_tlp'] = $userr['no_tlp'];
    $_SESSION['gambar'] = $userr['gambar'];

    // Path gambar
    $imageSrc = $userr['gambar'] ? "uploads/" . $userr['gambar'] : ''; // Default jika gambar tidak ada
} else {
    echo "Data pengguna tidak ditemukan!";
    exit();
}

// Jika form diperbarui
if (isset($_POST['kirim'])) {
    // Ambil data dari form
    $nama_user = $_POST['nama_user'];
    $no_tlp = $_POST['no_tlp'];
    $gambar = $_FILES['gambar'];

    // Proses upload gambar jika ada file baru
    $target_file = $userr['gambar']; // Gunakan gambar lama jika tidak ada file baru
    if (!empty($gambar['name'])) {
        $target_dir = __DIR__ . "/uploads/";
        $target_file = "uploads/" . basename($gambar["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi jenis file gambar
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            die("Format file gambar tidak valid! Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.");
        }

        // Pindahkan file ke folder uploads
        if (!move_uploaded_file($gambar["tmp_name"], $target_file)) {
            die("Gagal mengunggah file gambar. Pastikan folder 'uploads/' ada dan memiliki izin yang sesuai.");
        }
    }

    // Update data pengguna
    $sql_update = "UPDATE userr SET nama_user = ?, no_tlp = ?, gambar = ? WHERE id_user = ? AND role = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssis", $nama_user, $no_tlp, $target_file, $id_user, $role);

    if ($stmt_update->execute()) {
        // Update session dengan data baru
        $_SESSION['nama_user'] = $nama_user;
        $_SESSION['no_tlp'] = $no_tlp;
        $_SESSION['gambar'] = $target_file;

        echo "<script>alert('Profil berhasil diperbarui!'); window.location.href='profil.php';</script>";
    } else {
        die("Terjadi kesalahan saat menyimpan data: " . $stmt_update->error);
    }

    $stmt_update->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>WorkSmart</title>
    <!-- Favicon icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" sizes="16x16" href="./images/logoWMK.png">
    <link rel="stylesheet" href="./vendor/owl-carousel/css/owl.carousel.min.css">
    <link rel="stylesheet" href="./vendor/owl-carousel/css/owl.theme.default.min.css">
    <link href="./vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet">
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fa;
    margin: 0;
    padding: 0;
}

.profile-container {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    max-width: 800px;
    margin: 40px auto;
}

.profile-image {
    flex: 1;
    text-align: center;
    margin-right: 20px;
}

.profile-image img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-details {
    flex: 2;
    font-size: 16px;
}

.profile-details h2 {
    font-size: 24px;
    color: #333;
}

.role {
    font-size: 14px;
    color: #777;
    margin-bottom: 20px;
}

table {
    width: 100%;
    margin-bottom: 20px;
}

table td {
    padding: 8px;
    color: #555;
}

table td:first-child {
    font-weight: bold;
}

.profile-actions {
    display: flex;
    gap: 10px;
}

.profile-actions button {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
}

.profile-actions button:hover {
    background-color: #0056b3;
}
/* Modal */
.modal {
    display: none; /* Default tidak terlihat */
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

/* Konten Modal */
.modal-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    border-radius: 10px;
    width: 40%;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    position: relative;
}

/* Tombol Close */
.close-button {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    right: 20px;
    top: 10px;
    cursor: pointer;
}

.close-button:hover {
    color: black;
}

/* Tombol Simpan */
.save-button {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.save-button:hover {
    background-color: #218838;
}

/* Input Form */
input[type="text"],
input[type="email"] {
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}
</style>


</head>

<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->


    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="ds_peserta.php" class="brand-logo">
                <img class="logo-abbr" src="./images/logoWMK.png" alt=""><p>
                <h3 style="color: white;">WorkSmart</h3>
            </a>

            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                        <div class="ms-3 d-flex justify-content-center">
                            <a href="index.php" class="btn btn-sm btn-outline-dark rounded-pill">
                            <i class="fa-solid fa-house"></i>
                            </a>
                    </div> 
                          <!--  <div class="search_bar dropdown">
                                <span class="search_icon p-3 c-pointer" data-toggle="dropdown">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                               <div class="dropdown-menu p-0 m-0">
                                    <form>
                                        <input class="form-control" type="search" placeholder="Search" aria-label="Search">
                                    </form>
                                </div> 
                            </div> -->
                        </div>

                        <ul class="navbar-nav header-right">
                           <!-- <li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <i class="mdi mdi-bell"></i>
                                    <div class="pulse-css"></div>
                                </a>    
                            </li> -->

                            <li class="nav-item dropdown header-profile">
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <i class="mdi mdi-account"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                <a href="./profil.php" class="dropdown-item">
                                        <i class="icon-user"></i>
                                        <span class="ml-2">Profile </span>
                                    </a>
                                   <!-- <a href="./email-inbox.html" class="dropdown-item">
                                        <i class="icon-envelope-open"></i>
                                        <span class="ml-2">Inbox </span>
                                    </a> -->
                                    <a href="./logout.php" class="dropdown-item">
                                        <i class="icon-key"></i>
                                        <span class="ml-2">Logout </span>
                                    </a>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->
        
        <!--**********************************
            Sidebar start
        ***********************************-->
        <div class="quixnav">
            <div class="quixnav-scroll">
                <ul class="metismenu" id="menu">
                <?php 
                    if ($role=="admin") {
                     ?>
                    <li class="nav-label first">main menu</li>
                    <li><a class="" href="home.php" aria-expanded="false"><i
                                class="fa fa-light fa-table-columns"></i><span class="nav-text">Dashboard</span></a>
                    </li>
                    <li><a class="" href="data_workshop.php" aria-expanded="false"><i
                                class="fa-solid fa-hands-praying "></i><span class="nav-text">Workshop</span></a>
                    </li>
                    <li><a class="" href="data_mitra.php" aria-expanded="false"><i
                                class="fa fa-handshake"></i><span class="nav-text">Mitra</span></a>
                    </li>
                    <li><a class="" href="data_peserta.php" aria-expanded="false"><i
                                class="fa-regular fa-user"></i><span class="nav-text">Peserta</span></a>
                    </li>
                    <li class="nav-label first">Transaksi</li>                   
                    </li><li><a class="" href="data_keuangan.php" aria-expanded="false"><i 
                                class="fa-regular fa-money-bill"></i><span class="nav-text">Keuangan</span></a>
                    </li>
                    <li class="nav-label first">Laporan</li>
                    <li><a class="" href="laporan_data_keungan.php" aria-expanded="false"><i
                                class="fa-solid fa-book"></i><span class="nav-text">data keuangan</span></a>
                    </li>
                    <li><a class="" href="laporan_data_mitra.php" aria-expanded="false"><i
                                class="fa-solid fa-book"></i><span class="nav-text">data Mitra</span></a>
                    </li>
                    <li><a class="" href="laporan_data_peserta.php" aria-expanded="false"><i
                                class="fa-solid fa-book"></i><span class="nav-text">data Peserta</span></a>
                    </li>
                    <?php 
                }
                ?>

                    <?php 
                    if ($role=="mitra") {
                     ?>
                      <li class="nav-label first">main menu</li>
                    <li><a class="" href="dashboardmitra.php" aria-expanded="false"><i
                                class="fa fa-light fa-table-columns"></i><span class="nav-text">Dashboard</span></a>
                    </li>
                    <li><a class="" href="profilmitra.php" aria-expanded="false"><i 
                                class="fa-solid fa-user"></i><span class="nav-text">profil</span></a>
                    </li>
                    <li><a class="" href="kelolaWorkshop.php" aria-expanded="false"><i
                                class="fa fa-users"></i><span class="nav-text">Kelola Workshop</span></a>
                    </li>
                    <li><a class="" href="daftarPeserta.php" aria-expanded="false"><i 
                                class="fa-solid fa-user-group"></i><span class="nav-text">Daftar Peserta</span></a>
                    </li>
                    <li><a class="" href="sertifikatmitra.php" aria-expanded="false"><i
                                class="fa fa-certificate"></i><span class="nav-text">Sertifikat</span></a>
                    </li>
                    <?php 
                }
                ?>
                <?php 
                    if ($role=="peserta") {
                     ?>
                    <li class="nav-label first">main menu</li>
                    <li><a class="" href="ds_peserta.php" aria-expanded="false"><i
                                class="fa fa-light fa-table-columns"></i><span class="nav-text">Dashboard</span></a>
                    </li>
                    <li><a class="" href="workshop.php" aria-expanded="false"><i
                                class="fa-regular fa-chart-line"></i><span class="nav-text">Aktivitas</span></a>
                    </li>
                    <li><a class="" href="rating.php" aria-expanded="false"><i
                                class="fa-solid fa-star"></i><span class="nav-text">Rating dan Ulasan</span></a>                  
                    <li class="nav-label first">Transaksi</li>
                    <li><a class="" href="data_pembayaran.php" aria-expanded="false"><i
                                class="fa fa-credit-card"></i><span class="nav-text">Pembayaran</span></a>
                    </li>
                    <?php 
                }
                ?>
                </ul>
            </div>


        </div>
        <!--**********************************
            Sidebar end
        ***********************************-->

        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Selamat Datang Di WorkSmart <?php echo $_SESSION['nama_user']; ?></h4>
                            
                        </div>
                    </div>
                </div>

                <div class="profile-container">
    <div class="profile-image">
         <!-- Menampilkan gambar jika ada -->
         <?php if ($imageSrc): ?>
                <h3>Gambar Profil:</h3>
                <img src="<?php echo !empty($_SESSION['gambar']) ? $_SESSION['gambar'] : 'default.jpg'; ?>" alt="Profil Gambar" width="100">
                <?php else: ?>
                <p>Gambar tidak tersedia.</p>
            <?php endif; ?>
                </div>
                <div class="profile-details">
                    <h2><?php echo $_SESSION['nama_user']; ?></h2>
                    <p class="role"><?php echo $_SESSION['role']; ?></p>
                    <table>
                        <tr>
                            <td>Email</td>
                            <td><?php echo $_SESSION['email'] ?></td>
                        </tr>
                        <tr>
                            <td>Nama Lengkap</td>
                            <td><?php echo $_SESSION['nama_user'] ?></td>
                        </tr>
                        <tr>
                            <td>No Handphone</td>
                            <td><?php echo $_SESSION['no_tlp'] ?></td>
                        </tr>
                        <tr>
                            <td>Role</td>
                            <td><?php echo $_SESSION['role'] ?></td>
                        </tr>
                    </table>
                <div class="profile-actions">
                <button id="editButton">Edit Profile</button>
            </div>   
            </div>
        </div>
        <!-- pop-up modal-->
        <div id="popupModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Edit Profil</h2>
            <form id="editForm" enctype="multipart/form-data" method="POST" action="profil.php">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email"   value="<?php echo $_SESSION['email'] ?>" required>

                <label for="nama">Nama Lengkap:</label>
                <input type="text" id="nama" name="nama_user" value="<?php echo $_SESSION['nama_user'] ?>" required>

                <label for="email">No Handphone:</label>
                <input type="text" id="no_tlp" name="no_tlp" value="<?php echo $_SESSION['no_tlp'] ?>" required>

                <label for="gambar">Ubah Gambar:</label><br>
                <input type="file" id="gambar" name="gambar" >
               
                <button type="submit" class="save-button" name="kirim">Simpan</button>
                </form>
            </div>
        </div>
    </div>
        </div>
    </div><!--end modal--> 

<script src="script.js"></script>
        <script>
        // Ambil elemen modal, tombol, dan tombol close
        const modal = document.getElementById('popupModal');
        const editButton = document.getElementById('editButton');
        const closeButton = document.querySelector('.close-button');

        // Tampilkan modal saat tombol Edit diklik
        editButton.addEventListener('click', () => {
            modal.style.display = 'block';
        });

        // Tutup modal saat tombol Close diklik
        closeButton.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Tutup modal jika area luar modal diklik
        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        </script>
        <!--**********************************
            Content body end
        ***********************************-->


        <!--**********************************
            Footer start
        ***********************************-->
        
        <!--**********************************
            Footer end
        ***********************************-->

        <!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
        ***********************************-->


    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="./vendor/global/global.min.js"></script>
    <script src="./js/quixnav-init.js"></script>
    <script src="./js/custom.min.js"></script>


    <!-- Vectormap -->
    <script src="./vendor/raphael/raphael.min.js"></script>
    <script src="./vendor/morris/morris.min.js"></script>


    <script src="./vendor/circle-progress/circle-progress.min.js"></script>
    <script src="./vendor/chart.js/Chart.bundle.min.js"></script>

    <script src="./vendor/gaugeJS/dist/gauge.min.js"></script>

    <!--  flot-chart js -->
    <script src="./vendor/flot/jquery.flot.js"></script>
    <script src="./vendor/flot/jquery.flot.resize.js"></script>

    <!-- Owl Carousel -->
    <script src="./vendor/owl-carousel/js/owl.carousel.min.js"></script>

    <!-- Counter Up -->
    <script src="./vendor/jqvmap/js/jquery.vmap.min.js"></script>
    <script src="./vendor/jqvmap/js/jquery.vmap.usa.js"></script>
    <script src="./vendor/jquery.counterup/jquery.counterup.min.js"></script>


    <script src="./js/dashboard/dashboard-1.js"></script>

</body>

</html>