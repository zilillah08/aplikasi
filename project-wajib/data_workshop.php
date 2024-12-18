<?php
include'config.php'; 
session_start();
if (!isset($_SESSION['role'])) {
  header("Location:loginUser.php?aksi=belum");
  exit();
}
$role = $_SESSION['role'];

if ($role !== 'admin') {
    // Jika pengguna bukan admin, arahkan ke halaman login
    header("Location:loginUser.php?aksi=unauthorized");
    exit(); // Menghentikan eksekusi skrip
}

// //$katakunci ="";
// //if (isset($_POST['cari'])) {
//   $katakunci = $_POST['katakunci'];
//   $sql = mysqli_query($koneksi, "SELECT * FROM pegawai WHERE id_pegawai LIKE '%".$katakunci."%' OR nama_pegawai LIKE '%".$katakunci."%' OR username LIKE '%".$katakunci."%' OR level LIKE '%".$katakunci."%' ORDER BY id_pegawai ASC");
// }elseif(isset($_POST['cetak'])){
//   $katakunci = $_POST['katakunci'];
//   header("Location:cetakpegawai.php?katakunci=$katakunci");
// }else{
//   $sql = mysqli_query($koneksi, "SELECT * FROM pegawai ORDER BY id_pegawai ASC");
// }
// $row = mysqli_num_rows($sql);


if (isset($_GET['aksi'])) {
  $aksi=$_GET['aksi'];
    if ($aksi=="hapusok") {
    echo "
    <script>
    alert('selamat data anda berhasil hapus');
    </script>
    ";
  }
}
if (isset($_GET['pesan'])) {
  $id_pegawai = $_GET['id_workshop'];
  mysqli_query($koneksi, "DELETE FROM workshop WHERE id_workshop='$id_workshop'");
  header("Location:data_workshop.php?aksi=hapusok");
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            <a href="home.php" class="brand-logo">
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

                <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title"> Table Data Workshop</h2>
                            </div>                          
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table primary-table-bordered">
                                        <thead class="thead-primary">
                                            <tr>
                                                <th scope="col">No</th>
                                                <th scope="col">Id Workshop</th>
                                                <th scope="col">Nama Workshop</th>
                                                <th scope="col">Id Mitra</th>
                                                <th scope="col">Benefit</th>
                                                <th scope="col">Harga Workshop</th>
                                                <th scope="col">Lokasi</th>
                                                <th scope="col">Tanggal Mulai</th>
                                                <th scope="col">Tanggal Selesai </th>
                                                <th scope="col">Tipe Workshop </th>
                                                <th scope="col">Status </th>
                                                <th scope="col">Aksi </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        include 'config.php';
                                        $no=1;
                                        $query = mysqli_query($koneksi, "SELECT * FROM workshop");
                                        while ($data = mysqli_fetch_array($query)) {
                                        
                                        ?>                             
                                            <tr>
                                                <td><?php echo $no++ ?></td>
                                                <td><?php echo $data['id_workshop'] ?></td>
                                                <td><?php echo $data['nama_workshop'] ?></td>
                                                <td><?php echo $data['id_mitra'] ?></td>
                                                <td><?php echo $data['benefit'] ?></td>
                                                <td><?php echo $data['harga_workshop'] ?></td>
                                                <td><?php echo $data['lokasi'] ?></td>
                                                <td><?php echo $data['tanggal_mulai'] ?></td>
                                                <td><?php echo $data['tanggal_selesai'] ?></td>
                                                <td><?php echo $data['tipe'] ?></td>
                                                <td><?php echo $data['status'] ?></td>
                                                <td>
                                                <a href="delete.php?id_workshop=<?php echo $data['id_workshop']; ?>"><button class="btn btn-primary btn-bloc"><i class=" fa fa-thin fa-trash"></i></button>
                                                </td>
                                                <?php } ?>
                                            </tr>
                                            
                                        </tbody>
                                    </table>
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