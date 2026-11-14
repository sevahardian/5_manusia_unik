<?php
// admin/login.php
// [BAGIAN 1: PROGRAMMER 1] Halaman Login Kasir / Admin Sederhana

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $user['password'] === md5($password)) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama']     = $user['nama_lengkap'];
        $_SESSION['role']     = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: index.php');
        } else {
            header('Location: ../kasir/index.php');
        }
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - Resto & Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-sm w-full bg-white rounded-2xl shadow-xl p-6 border border-slate-200">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mx-auto mb-2 text-xl">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2 class="font-bold text-lg text-slate-900">Login Petugas</h2>
            <p class="text-xs text-slate-400">Kasir / Administrator Resto</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-50 text-red-700 border border-red-200 text-xs p-3 rounded-xl mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Username:</label>
                <input type="text" name="username" placeholder="admin atau kasir" 
                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500" required>
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Password:</label>
                <input type="password" name="password" placeholder="admin123 / kasir123" 
                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500" required>
            </div>
            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition">
                Masuk Sistem
            </button>
        </form>

        <div class="mt-4 pt-4 border-t border-slate-100 text-center text-[11px] text-slate-400">
            Default: <b>admin / admin123</b> atau <b>kasir / kasir123</b>
        </div>
    </div>
</body>
</html>

