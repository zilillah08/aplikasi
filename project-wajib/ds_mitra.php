<?php
include'config.php'; 
session_start();
if (!isset($_SESSION['role'])) {
  header("Location:loginUser.php?aksi=belum");
  exit();
}
$role = $_SESSION['role'];

if ($role !== 'mitra') {
    // Jika pengguna bukan mitra, arahkan ke halaman login
    header("Location: loginUser.php?aksi=unauthorized");
    exit(); // Menghentikan eksekusi skrip
}

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$database = "worksmart"; // Ganti dengan nama database Anda


// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}


// Query untuk menghitung jumlah workshop
$sql = "SELECT COUNT(*) AS total_workshop FROM workshop";
$result = $conn->query($sql); // Pastikan $conn terdefinisi dengan benar

// Ambil hasilnya
$total_workshop = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_workshop = $row['total_workshop'];
} else {
    $total_workshop = 0; // Jika tidak ada data
}
// Query untuk menghitung jumlah peserta
$sql_participants = "SELECT COUNT(*) AS total_participants FROM data_peserta";
$result_participants = $conn->query($sql_participants);

// Ambil hasilnya
$total_participants = 0;
if ($result_participants->num_rows > 0) {
    $row_participants = $result_participants->fetch_assoc();
    $total_participants = $row_participants['total_participants'];
} else {
    $total_participants = 0; // Jika tidak ada data
}
// Query untuk menghitung jumlah mitra
$sql_partners = "SELECT COUNT(*) AS total_partners FROM data_mitra"; // Pastikan nama tabelnya benar
$result_partners = $conn->query($sql_partners);

// Ambil hasilnya
$total_partners = 0;
if ($result_partners->num_rows > 0) {
    $row_partners = $result_partners->fetch_assoc();
    $total_partners = $row_partners['total_partners'];
} else {
    $total_partners = 0; // Jika tidak ada data

}

// Query untuk menghitung jumlah pendaftar per bulan
$sql = "SELECT MONTH(tanggal_daftar) AS bulan, COUNT(*) AS jumlah FROM data_peserta GROUP BY MONTH(tanggal_daftar)";
$result = $conn->query($sql);

// Siapkan array untuk menyimpan jumlah peserta per bulan
$pendaftar_per_bulan = array_fill(0, 12, 0); // Array berisi 12 bulan (Jan - Dec)

while ($row = $result->fetch_assoc()) {
    $bulan = (int) $row['bulan'];
    $pendaftar_per_bulan[$bulan - 1] = (int) $row['jumlah']; // Menyimpan jumlah pendaftar per bulan
}


// Menutup koneksi
$conn->close();
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
            <a href="ds_mitra.php" class="brand-logo">
                <img class="logo-abbr" src="./images/logoWMK.png" alt="">
                
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
                    <li><a class="" href="ds_mitra.php" aria-expanded="false"><i
                                class="fa fa-light fa-table-columns"></i><span class="nav-text">Dashboard</span></a>
                    </li>
                    <li><a class="" href="workshop.php" aria-expanded="false"><i
                                class="fa fa-users"></i><span class="nav-text">Workshop</span></a>
                    </li>
                    <li><a class="" href="rating.php" aria-expanded="false"><i 
                                class="fa-solid fa-user-group"></i><span class="nav-text">Rating</span></a>
                    </li>
                    <li class="nav-label first">Laporan</li>
                    <li><a class="" href="data_keungan.php" aria-expanded="false"><i
                                class="fa-solid fa-book"></i><span class="nav-text">Data Keuangan</span></a>
                    </li>
                    <li><a class="" href="data_peserta.php" aria-expanded="false"><i
                                class="fa-solid fa-book"></i><span class="nav-text">Data Peserta</span></a>
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

                <div class="row">
                    <div class="col-lg-3 col-sm-6">
                        <div class="card">
                            <div class="stat-widget-one card-body">
                                <div class="stat-content d-inline-block">
                                    <div class="stat-text"><i class="fa fa-users"></i> Jumlah Workshop</div>
                                    <div class="stat-digit">10</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="card">
                            <div class="stat-widget-one card-body">
                                <div class="stat-content d-inline-block">
                                    <div class="stat-text"><i class="fa fa-handshake"></i> Jumlah peserta </div>
                                    <div class="stat-digit">1</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>

                <div class="row">
                    <div class="col-xl-8 col-lg-8 col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Grafik Peserta Per Workshop</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12 col-lg-8">
                                        <div id="morris-bar-chart"></div>
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