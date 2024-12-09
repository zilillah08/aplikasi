<?php
session_start();

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

// Ambil ID workshop dari URL atau parameter
$id_workshop = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT id_workshop, nama_workshop, deskripsi_workshop, gambar, materi_dilatih, lokasi, tanggal_mulai, tanggal_selesai, tipe, harga_workshop, benefit, persyaratan, sesi_pelatihan 
FROM workshop 
WHERE id_workshop = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_workshop);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
$workshop = $result->fetch_assoc();

// Periksa apakah ada nama gambar
if (!empty($workshop['gambar'])) {
// Path gambar disimpan di database, jadi kita hanya menambahkannya di tag img
$imageSrc = "uploads/" . $workshop['gambar'];
} else {
// Gambar default jika tidak ada gambar
$imageSrc = 'path/to/default/image.jpg';
}
} else {
echo "Workshop tidak ditemukan!";
exit();
}

// Query untuk menghitung rata-rata rating
$sql_rating = "SELECT AVG(rating) as avg_rating FROM rating WHERE id_workshop = ?";
$stmt_rating = $conn->prepare($sql_rating);
$stmt_rating->bind_param("i", $id_workshop);
$stmt_rating->execute();
$result_rating = $stmt_rating->get_result();
$row_rating = $result_rating->fetch_assoc();
$rating = isset($row_rating['avg_rating']) ? round($row_rating['avg_rating']) : 0;


// Query untuk mengambil ulasan dari tabel rating
$sql_reviews = "SELECT r.ulasan, u.nama_user FROM rating r 
                JOIN userr u ON r.id_user = u.id_user 
                WHERE r.id_workshop = ? ORDER BY r.created_at DESC";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $id_workshop);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();



$stmt_rating->close();
$stmt_reviews->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>WorkSmart</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/logo1.png" rel="icon">
  <link href="assets/img/logo1.png" rel="icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
<style>
 /* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow: auto;
}

/* Modal Content */
.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 700px;
    position: relative;
}

/* Close Button */
.close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
}

/* Image in Modal */
.modal-image {
    width: 100%;
    height: auto;
    border-radius: 8px;
    margin-bottom: 15px;
}

/* Workshop Details */
.modal-info li {
    margin-bottom: 10px;
    list-style: none;
}

/* Rating and Reviews */
.modal-rating .stars {
    color: #FFD700;
    font-size: 18px;
}

.reviews li {
    margin-bottom: 10px;
}
.modal-actions {
    text-align: center;
    margin-top: 20px;
}

.modal-actions .btn {
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin: 5px;
    color: #fff;
    text-transform: uppercase;
}

.modal-actions .daftar-btn {
    background-color: #007bff;
}

.modal-actions .bayar-btn {
    background-color: #007bff;

}

.modal-actions .btn:hover {
    opacity: 0.9;
}
.modal-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
  .status-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: #007bff; /* blue for Active */
  color: white;
  padding: 5px 10px;
  border-radius: 20px;
  font-weight: bold;
}

.status-badge.nonaktif {
  background-color: #dc3545; /* Red for Nonaktif */
}
/* Gaya untuk tombol filter */
.filter-buttons .btn {
    text-decoration: none; /* Menghilangkan garis bawah default */
    color: #000; /* Warna default teks */
    border: 1px solid transparent; /* Border transparan */
    padding: 8px 16px;
    transition: all 0.3s ease;
}

/* Gaya untuk tombol yang aktif */
.filter-buttons .btn.active {
    color: #007bff; /* Warna teks biru */
    border: 1px solid #007bff; /* Garis border biru */
    font-weight: bold;
}

/* Gaya tambahan untuk tombol yang aktif dengan garis bawah */
.filter-buttons .btn.active i {
    text-decoration: underline; /* Menambahkan garis bawah */
}
</style>


  <!-- =======================================================
  * Template Name: QuickStart
  * Template URL: https://bootstrapmade.com/quickstart-bootstrap-startup-website-template/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.html" class="logo d-flex align-items-center me-auto">
        <img src="assets/img/logo1.png" alt="">
        <h1 class="sitename">WorkSmart</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php#hero" class="active">Beranda</a></li>
          <li><a href="index.php#about">Tentang</a></li>
          <li><a href="index.php#pricing">Workshop</a></li>

          <?php
        if (isset($_SESSION['role'])):
            $role = $_SESSION['role'];
        ?>
            <li class="dropdown"><a href="#"><span>Akun</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                <ul>
                    <?php if ($role === 'peserta'): ?>
                        <li><a href="../project-wajib/profil.php">Profil Saya</a></li>
                        <li><a href="../project-wajib/workshop.php">Workshop Saya</a></li>
                        <li><a href="../project-wajib/data_pembayaran.php">Riwayat Transaksi</a></li>
                    <?php elseif ($role === 'mitra'): ?>
                        <li><a href="../project-wajib/profil.php">Profil Saya</a></li>
                        <li><a href="../project-wajib/workshop_kelola.php">Workshop yang Dikelola</a></li>
                        <li><a href="../project-wajib/data_transaksi.php">Riwayat Transaksi</a></li>
                    <?php elseif ($role === 'admin'): ?>
                        <li><a href="../project-wajib/profil.php">Profil Saya</a></li>
                        <li><a href="../project-wajib/data_keuangan.php">Keuangan</a></li>
                        <li><a href="../project-wajib/data_workshop.php">Workshop</a></li>
                        <li><a href="../project-wajib/data_mitra.php">Mitra</a></li>
                        <li><a href="../project-wajib/data_peserta.php">Peserta</a></li>

                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>
 
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
      <?php if (isset($_SESSION['role'])) {$role = $_SESSION['role'];
            if ($role === 'admin') {
                $dashboardPage = 'home.php';
                $buttonText = 'Dashboard';
            } elseif ($role === 'mitra' || $role === 'peserta') {
                $dashboardPage = $role === 'mitra' ? 'ds_mitra.php' : 'ds_peserta.php';
                $buttonText = 'Dashboard';
            }
        } else {
            // Jika belum login, arahkan ke halaman registrasi
            $dashboardPage = 'register.php';
            $buttonText = 'Daftar';
        }
        ?>
        <!-- Tampilkan tombol -->
        <a class="btn-getstarted" href="<?php echo $dashboardPage; ?>">
            <?php echo $buttonText; ?>
        </a> </a>

    </div>
  </header> 

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">
      <div class="hero-bg">
        <img src="assets/img/hero-bg-light.webp" alt="">
      </div>
      <div class="container text-center">
        <div class="d-flex flex-column justify-content-center align-items-center">
          <h1 data-aos="fade-up">Welcome to <span>WorkSmart</span></h1>
          <p data-aos="fade-up" data-aos-delay="100">Belajar dan Berkembang dengan Workshop dari Para Ahli<br></p>
          <div class="d-flex" data-aos="fade-up" data-aos-delay="200">
            <a href="#about" class="btn-get-started">Jelajahi</a>
           <!-- <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox btn-watch-video d-flex align-items-center"><i class="bi bi-play-circle"></i><span>Watch Video</span></a> -->
          </div>
          <img src="assets/img/hero-services-img.webp" class="img-fluid hero-img" alt="" data-aos="zoom-out" data-aos-delay="300">
        </div>
      </div>

    </section><!-- /Hero Section -->

    <!-- Featured Services Section -->
   <!-- /Featured Services Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
            <p class="who-we-are">Tentang WorkSmart</p>
            <h3>Gerbang Menuju Profesional Pertumbuhan anda </h3>
            <p class="fst-italic">
             WorkSmart adalah tujuan utama Anda untuk workshop dan program pelatihan profesional berkualitas tinggi.
             Kami menghubungkan para profesional yang ambisius dengan para ahli terkemuka industri untuk memberikan pengalaman pembelajaran transformatif yang meningkatkan pertumbuhan karir Anda.
            </p>
            <ul>
              <li><i class="bi bi-check-circle"></i> <span>Workshop dipimpin para ahli.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Sertifikasi industri</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Jadwal belajar flexibel</span></li>
            </ul>
          </div>

          <div class="col-lg-6 about-images" data-aos="fade-up" data-aos-delay="200">
            <div class="row gy-4">
              <div class="col-lg-6">
                <img src="assets/img/about-company-1.jpg" class="img-fluid" alt="">
              </div>
              <div class="col-lg-6">
                <div class="row gy-4">
                  <div class="col-lg-12">
                    <img src="assets/img/about-company-2.jpg" class="img-fluid" alt="">
                  </div>
                  <div class="col-lg-12">
                    <img src="assets/img/about-company-3.jpg" class="img-fluid" alt="">
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>
    </section><!-- /About Section -->

    <!-- Clients Section -->
  <!-- /Clients Section -->

    <!-- Features Section -->
   <!-- /Features Section -->

    <!-- Features Details Section -->
   <!-- /Features Details Section -->

    <!-- Services Section -->
    <!-- /Services Section -->

    <!-- More Features Section -->
    <!-- /More Features Section -->

    <!-- Pricing Section -->
    <section id="pricing" class="pricing section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Eksplorasi Workshop</h2>
        <p>Tingkatkan skillset Anda melalui workshop interaktif yang dirancang khusus untuk profesional modern</p>
      </div><!-- End Section Title -->

      <!-- Search Bar with Animation -->
      <div class="container mb-5">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="search-wrapper glass-effect" data-aos="zoom-in">
              <div class="input-group">
              <input 
                type="text" 
                class="form-control search-input rounded-pill" 
                placeholder="Temukan workshop impianmu..." 
                id="workshopSearch" 
                autocomplete="off"
               />
              </div>
            </div>
          </div>
        </div>
      </div>

        <!-- Interactive Filter Options -->
        <div class="row mb-4">
          <div class="col-12">
          <div class="filter-buttons d-flex gap-3 flex-wrap justify-content-center" data-aos="fade-up">
          <a href="?filter=semua#pricing" class="btn btn-pill <?php echo ($filter === 'semua') ? 'active' : ''; ?>">
              <i class="bi bi-grid"></i> Semua
          </a>
          <a href="?filter=terbaru#pricing" class="btn btn-pill <?php echo ($filter === 'terbaru') ? 'active' : ''; ?>">
              <i class="bi bi-clock"></i> Terbaru
          </a>
          <button class="btn btn-pill">
              <i class="bi bi-trophy"></i> Best Seller
          </button>
      </div>

          </div>
        </div>

      <div class="container">
        <div class="row gy-4">

          <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="pricing-item">
            <?php
                // Mengambil tanggal saat ini
                $current_date = date('Y-m-d');

                  // Ambil filter dari URL (default 'semua' jika tidak ada)
              $filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';

                                    // Query berdasarkan filter
                    if ($filter === 'terbaru') {
                      // Query untuk workshop terbaru (berdasarkan tanggal mulai)
                      $query = "SELECT * FROM workshop WHERE tanggal_selesai >= CURDATE() ORDER BY tanggal_mulai DESC";
                    } else {
                      // Query untuk workshop semua yang masih aktif
                      $query = "SELECT * FROM workshop WHERE tanggal_selesai >= CURDATE() ORDER BY tanggal_mulai DESC";
                    }

                // Membandingkan tanggal_selesai dengan tanggal saat ini
                $status = (strtotime($workshop['tanggal_selesai']) >= strtotime($current_date)) ? 'Active' : 'Nonaktif';

               // Menampilkan workshop hanya jika statusnya "Active"
                if ($status === 'Active') {
                }
              ?>
              <!-- Menampilkan status di pojok kanan atas -->
              <span class="status-badge"><?php echo $status; ?></span>
              <h3><?php echo htmlspecialchars($workshop['nama_workshop']); ?></h3>
              <p class="description"><?php echo nl2br(htmlspecialchars($workshop['deskripsi_workshop'])); ?></p>
              <h4><sup>Rp</sup><?php echo number_format($workshop['harga_workshop'], 0, ',', '.'); ?></h4>
              <a href="#" class="cta-btn" id="openModal">Lihat Workshop</a>
              <ul>
                <li><i class="bi bi-check"></i>Lokasi : <?php echo htmlspecialchars($workshop['lokasi']); ?></li>
                <li><i class="bi bi-check"></i>Tanggal Mulai :</b> <?php echo htmlspecialchars($workshop['tanggal_mulai']); ?></li>
                <li><i class="bi bi-check"></i>Tanggal Selesai :</b> <?php echo htmlspecialchars($workshop['tanggal_selesai']); ?></li>
                <li><i class="bi bi-check"></i>Benefit :</b> <?php echo nl2br(htmlspecialchars($workshop['benefit'])); ?></li>

              </ul>
            </div>
          </div><!-- End Pricing Item -->

        <!-- Modal -->
    <div id="workshopModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <img src="<?php echo $imageSrc; ?>" alt="Workshop Image" class="modal-image">
            <h3><?php echo htmlspecialchars($workshop['nama_workshop']); ?></h3>
            <p class="modal-description"><?php echo nl2br(htmlspecialchars($workshop['deskripsi_workshop'])); ?></p>

            <ul class="modal-info">
                <li><b>Materi Yang Dilatih :</b> <?php echo htmlspecialchars($workshop['materi_dilatih']); ?></li>
                <li><b>Lokasi :</b> <?php echo htmlspecialchars($workshop['lokasi']); ?></li>
                <li><b>Tanggal Mulai :</b> <?php echo htmlspecialchars($workshop['tanggal_mulai']); ?></li>
                <li><b>Tanggal Selesai :</b> <?php echo htmlspecialchars($workshop['tanggal_selesai']); ?></li>
                <li><b>Tipe :</b> <?php echo htmlspecialchars($workshop['tipe']); ?></li>
                <li><b>Harga :</b> Rp <?php echo number_format($workshop['harga_workshop'], 0, ',', '.'); ?></li>
                <li><b>Benefit :</b> <?php echo nl2br(htmlspecialchars($workshop['benefit'])); ?></li>
                <li><b>Persyaratan :</b> <?php echo nl2br(htmlspecialchars($workshop['persyaratan'])); ?></li>
                <li><b>Sesi Pelatihan :</b> <?php echo htmlspecialchars($workshop['sesi_pelatihan']); ?></li>
            </ul>

              <div class="modal-rating">
                <b>Rating:</b>
                <span class="stars">
                    <?php
                    for ($i = 0; $i < 5; $i++) {
                        echo $i < $rating ? "★" : "☆";
                    }
                    ?>
                </span>
            </div>
            <h2>Ulasan : </h2>
            <div class="ulasan-container">
                <?php if ($result_reviews->num_rows > 0): ?>
                    <ul>
                        <?php while ($review = $result_reviews->fetch_assoc()): ?>
                            <li>
                                <strong><?= htmlspecialchars($review['nama_user' ]); ?>:</strong>
                                <?= htmlspecialchars($review['ulasan']); ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>Belum ada ulasan untuk workshop ini.</p>
                <?php endif; ?>
            </div>


            <!-- Tombol Daftar dan Bayar Sekarang -->
            <div class="modal-actions">
                <?php if (!isset($_SESSION['role'])): ?>
                    <button class="btn daftar-btn" onclick="window.location.href='register.php'">Daftar</button>
                <?php elseif ($_SESSION['role'] === 'peserta'): ?>
                    <button class="btn bayar-btn" onclick="window.location.href='data_workshop.php?id=<?php echo $workshop['id_workshop']; ?>'">Bayar Sekarang</button>
                <?php endif; ?>
            </div>
        </div>
    </div><!-- end modal -->

          <script>
          // Ambil elemen modal dan tombol
          const modal = document.getElementById('workshopModal');
          const btn = document.getElementById('openModal');
          const closeBtn = document.querySelector('.close');

          // Ketika tombol "Lihat Workshop" diklik, tampilkan modal
          btn.addEventListener('click', function (event) {
              event.preventDefault(); // Mencegah reload halaman
              modal.style.display = 'block'; // Tampilkan modal
          });

          // Ketika tombol "X" diklik, sembunyikan modal
          closeBtn.addEventListener('click', function () {
              modal.style.display = 'none';
          });

          // Ketika pengguna klik di luar modal, sembunyikan modal
          window.addEventListener('click', function (event) {
              if (event.target === modal) {
                  modal.style.display = 'none';
              }
          });
    // Menangani klik filter dan scroll ke #pricing
    document.querySelectorAll('.filter-buttons .btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            
            // Ambil filter dari URL
            var filter = this.getAttribute('href').split('=')[1].split('#')[0];
            
            // Perbarui URL tanpa memuat ulang halaman
            history.pushState(null, '', '?filter=' + filter + '#pricing');
            
            // Gulung ke #pricing
            document.querySelector('#pricing').scrollIntoView({ behavior: 'smooth' });

            // Perbarui konten workshop sesuai filter yang dipilih
            updateWorkshops(filter);
            
            // Update kelas aktif pada tombol filter
            document.querySelectorAll('.filter-buttons .btn').forEach(btn => {
                btn.classList.remove('active'); // Hapus kelas 'active' dari semua tombol
            });
            this.classList.add('active'); // Tambahkan kelas 'active' pada tombol yang diklik
        });
    });

    // Fungsi untuk memperbarui konten workshop
    function updateWorkshops(filter) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("workshops-container").innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", "workshops.php?filter=" + filter, true);
        xhttp.send();
    }
        </script> 
        </div>
      </div>
    </section><!-- /Pricing Section -->

    <!-- Faq Section -->
  <!--  <section id="faq" class="faq section">

       Section Title 
      <div class="container section-title" data-aos="fade-up">
        <h2>Pertanyaan yang sering </h2>
      </div>  End Section Title 

      <div class="container">

        <div class="row justify-content-center">

          <div class="col-lg-10" data-aos="fade-up" data-aos-delay="100">

            <div class="faq-container">

              <div class="faq-item faq-active">
                <h3>Non consectetur a erat nam at lectus urna duis?</h3>
                <div class="faq-content">
                  <p>Feugiat pretium nibh ipsum consequat. Tempus iaculis urna id volutpat lacus laoreet non curabitur gravida. Venenatis lectus magna fringilla urna porttitor rhoncus dolor purus non.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div> End Faq item

              <div class="faq-item">
                <h3>Feugiat scelerisque varius morbi enim nunc faucibus?</h3>
                <div class="faq-content">
                  <p>Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim. Mauris ultrices eros in cursus turpis massa tincidunt dui.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div> End Faq item

              <div class="faq-item">
                <h3>Dolor sit amet consectetur adipiscing elit pellentesque?</h3>
                <div class="faq-content">
                  <p>Eleifend mi in nulla posuere sollicitudin aliquam ultrices sagittis orci. Faucibus pulvinar elementum integer enim. Sem nulla pharetra diam sit amet nisl suscipit. Rutrum tellus pellentesque eu tincidunt. Lectus urna duis convallis convallis tellus. Urna molestie at elementum eu facilisis sed odio morbi quis</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div>  End Faq item

              <div class="faq-item">
                <h3>Ac odio tempor orci dapibus. Aliquam eleifend mi in nulla?</h3>
                <div class="faq-content">
                  <p>Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim. Mauris ultrices eros in cursus turpis massa tincidunt dui.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div> End Faq item

              <div class="faq-item">
                <h3>Tempus quam pellentesque nec nam aliquam sem et tortor?</h3>
                <div class="faq-content">
                  <p>Molestie a iaculis at erat pellentesque adipiscing commodo. Dignissim suspendisse in est ante in. Nunc vel risus commodo viverra maecenas accumsan. Sit amet nisl suscipit adipiscing bibendum est. Purus gravida quis blandit turpis cursus in</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div> End Faq item

              <div class="faq-item">
                <h3>Perspiciatis quod quo quos nulla quo illum ullam?</h3>
                <div class="faq-content">
                  <p>Enim ea facilis quaerat voluptas quidem et dolorem. Quis et consequatur non sed in suscipit sequi. Distinctio ipsam dolore et.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div> End Faq item

            </div>

          </div>End Faq Column

        </div>

      </div>

    </section> -->

    <!-- Testimonials Section -->
   <!-- <section id="testimonials" class="testimonials section light-background">

       Section Title 
      <div class="container section-title" data-aos="fade-up">
        <h2>Testimonials</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div> End Section Title 

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "breakpoints": {
                "320": {
                  "slidesPerView": 1,
                  "spaceBetween": 40
                },
                "1200": {
                  "slidesPerView": 3,
                  "spaceBetween": 1
                }
              }
            }
          </script>
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et. Maecen aliquam, risus at semper.
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/testimonials-1.jpg" class="testimonial-img" alt="">
                  <h3>Saul Goodman</h3>
                  <h4>Ceo &amp; Founder</h4>
                </div>
              </div>
            </div>  End testimonial item 

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid cillum eram malis quorum velit fore eram velit sunt aliqua noster fugiat irure amet legam anim culpa.
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/testimonials-2.jpg" class="testimonial-img" alt="">
                  <h3>Sara Wilsson</h3>
                  <h4>Designer</h4>
                </div>
              </div>
            </div> End testimonial item 

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem eram duis noster aute amet eram fore quis sint minim.
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/testimonials-3.jpg" class="testimonial-img" alt="">
                  <h3>Jena Karlis</h3>
                  <h4>Store Owner</h4>
                </div>
              </div>
            </div> End testimonial item 

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat minim velit minim dolor enim duis veniam ipsum anim magna sunt elit fore quem dolore labore illum veniam.
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/testimonials-4.jpg" class="testimonial-img" alt="">
                  <h3>Matt Brandon</h3>
                  <h4>Freelancer</h4>
                </div>
              </div>
            </div> End testimonial item 

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster veniam enim culpa labore duis sunt culpa nulla illum cillum fugiat legam esse veniam culpa fore nisi cillum quid.
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/testimonials-5.jpg" class="testimonial-img" alt="">
                  <h3>John Larson</h3>
                  <h4>Entrepreneur</h4>
                </div>
              </div>
            </div> End testimonial item

          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section>/Testimonials Section -->

    <!-- Contact Section -->
    

  </main>

  <footer id="footer" class="footer position-relative light-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">WorkSmart</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Jl. Mastrip, Krajan Timur, Sumbersari, </p>
            <p>Sumbersari, Kabupaten Jember, Jawa Timur 68121</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+62 8560 7601 828</span></p>
            <p><strong>Email:</strong> <span>WorkSmart@gmail.com</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href="https://www.instagram.com/worksmart.wmk?igsh=amNkY3NwcWd5OG5l"><i class="bi bi-instagram"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Tautan Cepat</h4>
          <ul>
            <li><a href="index.php#hero">Beranda </a></li>
            <li><a href="index.php#about">Tentang</a></li>
            <li><a href="index.php#pricing">workshop</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">WorkSmart</strong><span>Seluruh Hak Dilindungi</span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>