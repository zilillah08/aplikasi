<?php
if (isset($_GET['email'])) {
    // Ambil nilai 'email' dari URL
    $email = $_GET['email'];

    // Koneksi ke database untuk memverifikasi email
    $conn = new mysqli('localhost', 'root', '', 'worksmart');
    if ($conn->connect_error) {
        die('Could not connect to the database');
    }

    // Verifikasi apakah email ada dalam database
    $query = $conn->query("SELECT * FROM userr WHERE email = '$email'");
    if ($query->num_rows == 0) {
        // Jika email tidak ditemukan
        echo "Email not found.";
        exit();
    }

    // Menutup koneksi ke database
    $conn->close();
} else {
    // Jika parameter 'email' tidak ada, tampilkan pesan error
    echo "Email parameter is missing.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <!-- Document Title -->
   <title class="brand-color">Change Password</title>

   <!-- Favicons -->
   <link href="../project-wajib/images/logoWMK.png" rel="icon" />
   <link href="../project-wajib/images/logoWMK.png" rel="apple-touch-icon" />
   <!-- External CSS Links -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
   <link href="pages/assets/css/brand.css" rel="stylesheet" />
   <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

   <style>
     body { background-color: #02396f; font-family: "Arial", sans-serif; }
     .container { background-color: white; padding: 30px; max-width: 85%; margin: 50px auto; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
     .form-control { border-radius: 8px; margin-bottom: 15px; padding: 12px 15px; }
     .btn-primary { background-color: #cccccc; border: none; color: #666666; padding: 10px 20px; border-radius: 5px; }
     .btn-primary:hover { background-color: #bbbbbb; }
     .password-toggle {
        border-radius: 0 8px 8px 0;
        height: 100%;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        background: #f8f9fa;
        border: 1px solid #ced4da;
        cursor: pointer;
     }
   </style>
   <script>
document.addEventListener('DOMContentLoaded', function() {
      const togglePassword = document.querySelector('#togglePassword');
      const passwordField = document.querySelector('#password');
      
      togglePassword.addEventListener('click', function() {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.textContent = type === 'password' ? 'Show' : 'Hide';
      });
    });
</script>
</head>
<body>
  <!-- Form untuk Ubah Password -->
  <div class="container rounded-4">
    <div class="row">
      <div class="col-md-6">
        <h2 class="brand-color">Change Password</h2>
        <form method="POST" action="change_password_process.php">
          <label for="inputEmail" class="form-label brand-color">Email</label>
          <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly required />

          <!-- Password Section -->
          <div class="mb-3">
            <label for="inputPassword" class="form-label brand-color">Password Baru</label>
            <div class="input-group">
              <input type="password" class="form-control password-input" id="password" name="password"  required>
              <button class="btn btn-outline-secondary password-toggle" type="button" id="togglePassword">Hide</button>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="d-grid">
            <button type="submit" class="btn btn-outline-primary btn-lg rounded-pill" name="change">Change</button>
          </div>
        </form>
      </div>
      <!-- Bagian Image -->
      <div class="col-md-6">
        <div class="d-flex align-items-center justify-content-center h-100">
          <a href="../project-wajib/loginUser.php">
          <img src="../project-wajib/images/logoWMK.png" class="img-fluid" />
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
