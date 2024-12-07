<?php
if (isset($_POST['change'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash password baru
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Koneksi ke database
    $conn = new mysqli('localhost', 'root', '', 'worksmart');
    if ($conn->connect_error) {
        die('Could not connect to the database');
    }

    // Update password di database
    $stmt = $conn->prepare("UPDATE userr SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);

    if ($stmt->execute()) {
        echo "<script>
                alert('Password berhasil diubah. Silakan login.');
                window.location.href = 'loginUser.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal mengubah password.');
                window.location.href = 'change_password.php?email=$email';
              </script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Document Title -->
    <title class="brand-color">Forgot Password</title>
<!-- Favicons -->
<link href="pages/assets/img/logo-worksmart.png" rel="icon">
    <!-- External CSS Links -->
    <link
      crossorigin="anonymous"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
      rel="stylesheet"
    />
    <link href="pages/assets/css/brand.css" rel="stylesheet" />

    <!-- Custom Styles -->
    <style>
      body {
        background-color: #02396f;
        font-family: "Arial", sans-serif;
      }
    </style>
  </head>

  <body>
    <div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999;">
      <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>
    <!-- Your page content goes here -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>