<?php
// admin/login.php
// Halaman Login Petugas (Kasir & Administrator)

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

// Jika sudah login, langsung alihkan sesuai role
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: index.php');
    } else {
        header('Location: ../kasir/index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

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
    <title>Login Petugas - KanDar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-b from-orange-50/60 to-slate-50 min-h-screen flex items-center justify-center p-4 text-slate-800">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200 p-7 sm:p-8">
        
        <!-- Brand Logo & Judul persis seperti Landing Page -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white flex items-center justify-center mx-auto mb-3 text-2xl shadow-md shadow-orange-500/20">
                <i class="fa-solid fa-utensils"></i>
            </div>
            <h2 class="font-black text-2xl text-slate-900">KanDar</h2>
            <span class="text-[11px] text-orange-600 font-semibold tracking-wider uppercase block mt-0.5">Portal Masuk Petugas</span>
            <p class="text-xs text-slate-500 mt-1">Akses khusus kasir dan staf resto</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-orange-50 text-orange-800 border border-orange-200 text-xs p-3.5 rounded-xl mb-5 flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation text-orange-600 text-sm flex-shrink-0"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[11px]">
                    <i class="fa-solid fa-user text-orange-500 mr-1"></i> Username:
                </label>
                <input type="text" name="username" id="username" placeholder="admin atau kasir" 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition" required>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[11px]">
                    <i class="fa-solid fa-lock text-orange-500 mr-1"></i> Password:
                </label>
                <input type="password" name="password" id="password" placeholder="admin123 / kasir123" 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition" required>
            </div>

            <button type="submit" class="w-full py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl text-sm shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center justify-center space-x-2 mt-2">
                <span>Masuk Sekarang</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <!-- Pilihan Cepat Akun -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2 text-center">Pilihan Cepat Demo:</span>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" onclick="fillAccount('admin', 'admin123')" class="px-3 py-2 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 rounded-xl transition border border-slate-200 font-bold text-slate-700 text-center">
                    Admin
                </button>
                <button type="button" onclick="fillAccount('kasir', 'kasir123')" class="px-3 py-2 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 rounded-xl transition border border-slate-200 font-bold text-slate-700 text-center">
                    Kasir
                </button>
            </div>
        </div>

        <div class="mt-5 text-center">
            <a href="../index.php" class="text-xs font-bold text-orange-600 hover:text-orange-700 transition inline-flex items-center space-x-1.5">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Halaman Resto</span>
            </a>
        </div>
    </div>

    <script>
        function fillAccount(user, pass) {
            document.getElementById('username').value = user;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
