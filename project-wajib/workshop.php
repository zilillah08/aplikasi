<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';
session_start();

// Mengecek apakah pengguna sudah login
if (!isset($_SESSION['role'])) {
    header("Location: loginUser.php?aksi=belum");
    exit();
}

$role = $_SESSION['role'];

// Mengecek apakah role pengguna adalah 'mitra'
if ($role !== 'mitra') {
    header("Location: loginUser.php?aksi=unauthorized");
    exit();
}

// Mengambil data mitra untuk ditampilkan secara otomatis
$queryMitra = "SELECT id_mitra, nama_mitra FROM data_mitra LIMIT 1";
$resultMitra = mysqli_query($koneksi, $queryMitra);
$mitraData = mysqli_fetch_assoc($resultMitra);

if (!$mitraData) {
    echo "<script>alert('Mitra tidak ditemukan.'); window.location.href='mitra_list.php';</script>";
    exit();
}
if (isset($_GET['action']) && $_GET['action'] === 'get_last_id') {
    header('Content-Type: application/json');
    $lastId = getLastWorkshopId($koneksi); // Ambil ID terakhir dari database
    if ($lastId) {
        echo json_encode(['lastId' => $lastId]);
    } else {
        echo json_encode(['lastId' => null]); // Jika belum ada ID sebelumnya
    }
    exit();
}

function getLastWorkshopId($conn) {
    $query = "SELECT id_workshop FROM workshop ORDER BY id_workshop DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['id_workshop'] : null;
}



// Menangani Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari form
    $nama_workshop = mysqli_real_escape_string($koneksi, $_POST['nama_workshop']);
    $deskripsi_workshop = mysqli_real_escape_string($koneksi, $_POST['deskripsi_workshop']);
    $materi_dilatih = mysqli_real_escape_string($koneksi, $_POST['materi_dilatih']);
    $sesi_pelatihan = mysqli_real_escape_string($koneksi, $_POST['sesi_pelatihan']);
    $persyaratan = mysqli_real_escape_string($koneksi, $_POST['persyaratan']);
    $benefit = mysqli_real_escape_string($koneksi, $_POST['benefit']);
    $harga_workshop = mysqli_real_escape_string($koneksi, $_POST['harga_workshop']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $tanggal_mulai = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $tanggal_selesai = mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']);
    $tipe = mysqli_real_escape_string($koneksi, $_POST['tipe']);
    $media_pembelajaran = mysqli_real_escape_string($koneksi, $_POST['media_pembelajaran']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $id_workshop = mysqli_real_escape_string($koneksi, $_POST['id_workshop']); // Mendapatkan ID dari form

    // ID dan Nama Mitra diambil dari database
    $id_mitra = $mitraData['id_mitra'];
    $nama_mitra = $mitraData['nama_mitra'];

    // Menangani file gambar
$gambarName = null;
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileInfo = pathinfo($_FILES['gambar']['name']);
    $extension = strtolower($fileInfo['extension']);
    $originalName = $fileInfo['basename'];

    if (in_array($extension, $allowedExtensions)) {
        $uploadDir = "uploads/";  // Folder tempat menyimpan gambar
        $gambarName = $originalName;

        // Cek apakah file dengan nama yang sama sudah ada
        if (file_exists($uploadDir . $gambarName)) {
            $gambarName = pathinfo($gambarName, PATHINFO_FILENAME) . "_" . uniqid() . "." . $extension;
        }

        $gambarPath = $uploadDir . $gambarName;

        // Pindahkan file gambar ke folder uploads
        if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $gambarPath)) {
            echo "<script>alert('Gagal mengunggah gambar.');</script>";
            exit();
        }
    } else {
        echo "<script>alert('Format gambar tidak valid.');</script>";
        exit();
    }
}

    // Query SQL untuk menyimpan workshop
    if ($gambarName) {
        $query = "INSERT INTO workshop 
                    (id_workshop, nama_workshop, id_mitra, nama_mitra, deskripsi_workshop, materi_dilatih, sesi_pelatihan, persyaratan, benefit, harga_workshop, lokasi, tanggal_mulai, tanggal_selesai, tipe, media_pembelajaran, status, gambar) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    } else {
        $query = "INSERT INTO workshop 
                    (id_workshop, nama_workshop, id_mitra, nama_mitra, deskripsi_workshop, materi_dilatih, sesi_pelatihan, persyaratan, benefit, harga_workshop, lokasi, tanggal_mulai, tanggal_selesai, tipe, media_pembelajaran, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    }

    // Eksekusi query untuk menyimpan data workshop ke database, termasuk path gambar
    $stmt = mysqli_prepare($koneksi, $query);
    if ($gambarName) {
        mysqli_stmt_bind_param($stmt, "sssssssssssssssss", $id_workshop, $nama_workshop, $id_mitra, $nama_mitra, $deskripsi_workshop, $materi_dilatih, $sesi_pelatihan, $persyaratan, $benefit, $harga_workshop, $lokasi, $tanggal_mulai, $tanggal_selesai, $tipe, $media_pembelajaran, $status, $gambarName);
    } else {
        mysqli_stmt_bind_param($stmt, "ssssssssssssssss", $id_workshop, $nama_workshop, $id_mitra, $nama_mitra, $deskripsi_workshop, $materi_dilatih, $sesi_pelatihan, $persyaratan, $benefit, $harga_workshop, $lokasi, $tanggal_mulai, $tanggal_selesai, $tipe, $media_pembelajaran, $status);
    }

    // Eksekusi query
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Workshop berhasil ditambahkan!'); window.location.href='workshop.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }

    // Tutup statement dan koneksi
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);
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
                                <a href="../project-wajib/profil.php" class="dropdown-item">
                                        <i class="icon-user"></i>
                                        <span class="ml-2">Profile </span>
                                    </a>
                                   <!-- <a href="./email-inbox.html" class="dropdown-item">
                                        <i class="icon-envelope-open"></i>
                                        <span class="ml-2">Inbox </span>
                                    </a> -->
                                    <a href="../project-wajib/logout.php" class="dropdown-item">
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
                                class="icon icon-home"></i><span class="nav-text">Dashboard</span></a>
                    </li>
                    <li><a class="" href="Workshop.php" aria-expanded="false"><i
                                class="fa fa-users"></i><span class="nav-text">Workshop</span></a>
                    </li>
                    <li><a class="" href="mitra.php" aria-expanded="false"><i
                                class="fa fa-handshake"></i><span class="nav-text">Mitra</span></a>
                    </li>
                    <li><a class="" href="peserta.php" aria-expanded="false"><i 
                                class="fa fa-user"></i><span class="nav-text">Peserta</span></a>
                    </li>
                    <li class="nav-label first">Transaksi</li>
                    <li><a class="" href="pembayaran.php" aria-expanded="false"><i
                                class="fa fa-credit-card"></i><span class="nav-text">Pengelolaan Pembayaran</span></a>
                    </li>
                    <li class="nav-label first">Laporan</li>
                    <li><a class="" href="laporan.php" aria-expanded="false"><i
                                class="fa-solid fa-book"></i><span class="nav-text">Laporan Dan Statistik</span></a>
                    </li>
                    <li class="nav-label first">benefit</li>
                    <li><a class="" href="sertifikat.php" aria-expanded="false"><i
                                class="fa fa-certificate"></i><span class="nav-text">Sertifikat</span></a>
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
                                class="icon icon-home"></i><span class="nav-text">Dashboard</span></a>
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
                                <h4 class="card-title"> Table Workshop</h4>
                            </div>                          
                            <div class="card-body">
                             <td>
                            <button class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkshopModal">
                                <i class="fa-solid fa-square-plus"></i> Buat Workshop Baru
                            </button>
                            <br>
                             </td> <br>
                                <div class="table-responsive">
                                    <table class="table primary-table-bordered">
                                        <thead class="thead-primary">
                                            <tr>
                                                <th scope="col">No</th>
                                                <th scope="col">Id Workshop</th>
                                                <th scope="col">Nama Workshop</th>
                                                <th scope="col">Id Mitra</th>
                                                <th scope="col">Nama Mitra</th>
                                                <th scope="col">Deskripsi Workshop</th>
                                                <th scope="col">Materi Dilatih</th>
                                                <th scope="col">Sesi Pelatihan</th>
                                                <th scope="col">Persyaratan</th>
                                                <th scope="col">Benefit</th>
                                                <th scope="col">Harga Workshop </th>
                                                <th scope="col">Lokasi </th>
                                                <th scope="col">Tanggal Mulai </th>
                                                <th scope="col">Tanggal Selesai </th>
                                                <th scope="col">Tipe </th>
                                                <th scope="col">Media Pembelajaran </th>
                                                <th scope="col">Status</th>
                                                <th scope="col"> Gambar</th>

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
                                                <td><?php echo $data['nama_mitra'] ?></td>
                                                <td><?php echo $data['deskripsi_workshop'] ?></td>
                                                <td><?php echo $data['materi_dilatih'] ?></td>
                                                <td><?php echo $data['sesi_pelatihan'] ?></td>
                                                <td><?php echo $data['persyaratan'] ?></td>
                                                <td><?php echo $data['benefit'] ?></td>
                                                <td><?php echo $data['harga_workshop'] ?></td>
                                                <td><?php echo $data['lokasi'] ?></td>
                                                <td><?php echo $data['tanggal_mulai'] ?></td>
                                                <td><?php echo $data['tanggal_selesai'] ?></td>
                                                <td><?php echo $data['tipe'] ?></td>
                                                <td><?php echo $data['media_pembelajaran'] ?></td>
                                                <td><?php echo $data['status'] ?></td>
                                                <td><?php echo $data['gambar'] ?></td>
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

   <!-- Modal Buat Workshop Baru -->
    <div class="modal fade" id="createWorkshopModal" tabindex="-1" aria-labelledby="createWorkshopModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createWorkshopModalLabel">Buat Workshop Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form action="workshop.php" method="POST" enctype="multipart/form-data">
            <!-- ID Workshop (otomatis) -->
                    <div class="mb-3">
                            <label for="id_workshop" class="form-label">ID Workshop</label>
                            <input type="text" class="form-control" id="id_workshop" name="id_workshop" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="nama_workshop" class="form-label">Nama Workshop</label>
                            <input type="text" class="form-control" id="nama_workshop" name="nama_workshop" >
                        </div>

                        <div class="mb-3">
                            <label for="id_mitra" class="form-label">ID Mitra</label>
                            <input type="text" class="form-control" id="id_mitra" name="id_mitra" value="<?= htmlspecialchars($mitraData['id_mitra']); ?>" required readonly>
                        </div>

                        <div class="mb-3">
                            <label for="nama_mitra" class="form-label">Nama Mitra</label>
                            <input type="text" class="form-control" id="nama_mitra" name="nama_mitra" value="<?= htmlspecialchars($mitraData['nama_mitra']); ?>" required readonly>
                        </div>

                    <div class="mb-3">
                        <label for="deskripsi_workshop" class="form-label">Deskripsi Workshop</label>
                        <textarea class="form-control" id="deskripsi_workshop" name="deskripsi_workshop" ></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="materi_dilatih" class="form-label">Materi Dilatih</label>
                        <textarea class="form-control" id="materi_dilatih" name="materi_dilatih" ></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="sesi_pelatihan" class="form-label">Sesi Pelatihan</label>
                        <textarea class="form-control" id="sesi_pelatihan" name="sesi_pelatihan" ></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="persyaratan" class="form-label">Persyaratan</label>
                        <textarea class="form-control" id="persyaratan" name="persyaratan" ></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="benefit" class="form-label">Benefit</label>
                        <textarea class="form-control" id="benefit" name="benefit" ></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="harga_workshop" class="form-label">Harga Workshop</label>
                        <textarea class="form-control" id="harga_workshop" name="harga_workshop" ></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <textarea class="form-control" id="lokasi" name="lokasi" ></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" >
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" >
                    </div>

                    <div class="mb-3">
                        <label for="tipe" class="form-label">Tipe</label>
                        <input type="text" class="form-control" id="tipe" name="tipe" >
                    </div>

                    <div class="mb-3">
                        <label for="media_pembelajaran" class="form-label">Media Pembelajaran</label>
                        <input type="text" class="form-control" id="media_pembelajaran" name="media_pembelajaran" >
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" class="form-control" id="status" name="status" >
                    </div>

                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar</label>
                        <input type="file" class="form-control" id="gambar" name="gambar" accept="uploads/*">
                    </div>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Simpan</button>

                </form>
            </div>
        </div>
    </div>
</div><!-- end modal -->

<script>
document.getElementById('createWorkshopModal').addEventListener('shown.bs.modal', function () {
    generateIdWorkshop(); // Panggil fungsi setiap kali modal dibuka
});
function generateIdWorkshop() {
    const today = new Date();
    const date = today.getDate().toString().padStart(2, '0');
    const month = (today.getMonth() + 1).toString().padStart(2, '0');

    // Ambil data ID terakhir dari server
    fetch('workshop.php?action=get_last_id')
        .then(response => response.json())
        .then(data => {
            console.log('Response from API:', data); // Debugging
            let lastId = data.lastId || ''; // Ambil ID terakhir
            
            // Ambil satu digit terakhir dari nomor ID workshop
            let lastNumber = 1; // Default value jika tidak ada ID sebelumnya
            if (lastId) {
                const numberPart = lastId.slice(-3); // Ambil digit terakhir dari ID
                lastNumber = parseInt(numberPart) + 1;
            }

            // Pastikan nomor urut hanya satu digit
            const generatedId = 'WRK' + date + month + lastNumber.toString().padStart(3, '0');
            
            // Set nilai ID Workshop
            document.getElementById('id_workshop').value = generatedId;
        })
        .catch(error => {
            console.error('Error generating ID workshop:', error);
        });
}


        document.getElementById('createWorkshopModal').addEventListener('shown.bs.modal', function () {
            generateIdWorkshop(); // Panggil fungsi setiap kali modal dibuka
        });

        
    function checkStatus() {
    const startDate = new Date(document.getElementById('tanggal_mulai').value);
    const endDate = new Date(document.getElementById('tanggal_selesai').value);
    const today = new Date();

    console.log('Start Date:', startDate);
    console.log('End Date:', endDate);
    console.log('Today:', today);

    if (startDate >= today && endDate >= today) {
        document.getElementById('status').value = 'aktif';
        document.getElementById('submitBtn').disabled = false;
    } else {
        document.getElementById('status').value = 'nonaktif';
        document.getElementById('submitBtn').disabled = true;
    }
}

// Pastikan status diperbarui saat tanggal mulai atau selesai berubah
document.getElementById('tanggal_mulai').addEventListener('change', checkStatus);
document.getElementById('tanggal_selesai').addEventListener('change', checkStatus)

        document.getElementById('createWorkshopModal').addEventListener('shown.bs.modal', function () {
            generateIdWorkshop(); // Panggil fungsi setiap kali modal dibuka
        });


        function fetchMitraData() {
            fetch('workshop.php')  // PHP untuk mengambil data mitra default
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('id_mitra').value = data.id_mitra;
                        document.getElementById('nama_mitra').value = data.nama_mitra;
                    }
                })
                .catch(error => {
                    console.error('Error fetching mitra data:', error);
                });
        }
 </script>
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>


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