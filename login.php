<?php
session_start();

// Jika sudah login, redirect ke maps
if (isset($_SESSION['user'])) {
    header('Location: maps.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $koneksi = mysqli_connect("127.0.0.1", "root", "", "gis_kemiskinan", 3307);
    if (!$koneksi) {
        $error = 'Koneksi database gagal.';
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username=? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user']  = $user['username'];
            $_SESSION['role']  = $user['role'];
            mysqli_close($koneksi);
            header('Location: maps.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
        mysqli_close($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login – GIS Kemiskinan</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #0f1117;
      color: #e2e8f0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-box {
      background: #1a1d2e;
      border: 1px solid #2d3748;
      border-radius: 14px;
      padding: 36px 32px;
      width: 100%;
      max-width: 360px;
      box-shadow: 0 20px 60px rgba(0,0,0,.5);
    }
    .login-box h1 {
      font-size: 18px;
      font-weight: 700;
      color: #63b3ed;
      text-align: center;
      margin-bottom: 4px;
    }
    .login-box .sub {
      font-size: 12px;
      color: #718096;
      text-align: center;
      margin-bottom: 28px;
    }
    label {
      display: block;
      font-size: 12px;
      color: #a0aec0;
      margin-bottom: 5px;
      font-weight: 600;
    }
    input[type=text], input[type=password] {
      width: 100%;
      padding: 10px 12px;
      background: #0f1117;
      border: 1px solid #2d3748;
      border-radius: 7px;
      color: #e2e8f0;
      font-size: 14px;
      margin-bottom: 16px;
      outline: none;
      transition: border-color .2s;
    }
    input[type=text]:focus, input[type=password]:focus {
      border-color: #4299e1;
    }
    button[type=submit] {
      width: 100%;
      padding: 10px;
      background: #2b6cb0;
      border: none;
      border-radius: 7px;
      color: #fff;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      transition: background .2s;
    }
    button[type=submit]:hover { background: #2c5282; }
    .error-msg {
      background: #742a2a;
      border: 1px solid #c53030;
      color: #fc8181;
      padding: 8px 12px;
      border-radius: 7px;
      font-size: 12px;
      margin-bottom: 16px;
      text-align: center;
    }
    .guest-link {
      display: block;
      text-align: center;
      margin-top: 14px;
      font-size: 12px;
      color: #718096;
      text-decoration: none;
      transition: color .2s;
    }
    .guest-link:hover { color: #a0aec0; }
  </style>
</head>
<body>
  <div class="login-box">
    <h1>🗺 GIS Kemiskinan</h1>
    <div class="sub">Silakan login untuk melanjutkan</div>

    <?php if ($error): ?>
      <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Username</label>
      <input type="text" name="username" placeholder="Masukkan username" autofocus required>

      <label>Password</label>
      <input type="password" name="password" placeholder="Masukkan password" required>

      <button type="submit">🔐 Login</button>
    </form>

    <a href="maps.php" class="guest-link">👁 Masuk sebagai Pengunjung (hanya lihat)</a>
  </div>
</body>
</html>