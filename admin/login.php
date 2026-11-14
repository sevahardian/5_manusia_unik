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
    <title>Login Petugas - Resto Enak Rasa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#7C3E12',
                            orange: '#FF7E24',
                            peach: '#FFC079',
                            cream: '#FFEFAB',
                            creamLight: '#FFF9EE',
                            bg: '#FFFDF9',
                            espresso: '#4A240A'
                        },
                        orange: {
                            50: '#FFF9EE',
                            100: '#FFEFAB',
                            200: '#FFE2B8',
                            300: '#FFC079',
                            400: '#FFA04E',
                            500: '#FF7E24',
                            600: '#E66810',
                            700: '#7C3E12',
                            800: '#5C2D0B',
                            900: '#4A240A'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FFFDF9; color: #4A240A; }
    </style>
</head>
<body class="bg-gradient-to-b from-[#FFEFAB]/50 via-[#FFEFAB]/20 to-[#FFFDF9] min-h-screen flex items-center justify-center p-4 text-[#4A240A]">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-[#FFC079]/40 p-7 sm:p-8">
        
        <!-- Brand Logo & Judul persis seperti Landing Page -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#7C3E12] to-[#FF7E24] text-white flex items-center justify-center mx-auto mb-3 text-2xl shadow-md shadow-[#FF7E24]/20">
                <i class="fa-solid fa-utensils"></i>
            </div>
            <h2 class="font-black text-2xl text-[#7C3E12]">Resto Enak Rasa</h2>
            <span class="text-[11px] text-[#FF7E24] font-bold tracking-wider uppercase block mt-0.5">Portal Masuk Petugas</span>
            <p class="text-xs text-[#7C3E12]/70 mt-1 font-medium">Akses khusus kasir dan staf resto</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-[#FFEFAB] text-[#7C3E12] border border-[#FFC079] text-xs p-3.5 rounded-xl mb-5 flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation text-[#FF7E24] text-sm flex-shrink-0"></i>
                <span class="font-semibold"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-[#7C3E12] mb-1.5 uppercase tracking-wider text-[11px]">
                    <i class="fa-solid fa-user text-[#FF7E24] mr-1"></i> Username:
                </label>
                <input type="text" name="username" id="username" placeholder="admin atau kasir" 
                       class="w-full px-4 py-3 bg-[#FFFDF9] border border-[#FFC079]/40 rounded-xl text-sm font-bold text-[#7C3E12] focus:outline-none focus:border-[#FF7E24] focus:bg-white transition" required>
            </div>

            <div>
                <label class="block font-bold text-[#7C3E12] mb-1.5 uppercase tracking-wider text-[11px]">
                    <i class="fa-solid fa-lock text-[#FF7E24] mr-1"></i> Password:
                </label>
                <input type="password" name="password" id="password" placeholder="admin123 / kasir123" 
                       class="w-full px-4 py-3 bg-[#FFFDF9] border border-[#FFC079]/40 rounded-xl text-sm font-bold text-[#7C3E12] focus:outline-none focus:border-[#FF7E24] focus:bg-white transition" required>
            </div>

            <button type="submit" class="w-full py-3.5 bg-[#FF7E24] hover:bg-[#7C3E12] text-white font-bold rounded-xl text-sm shadow-md shadow-[#FF7E24]/20 active:scale-95 transition flex items-center justify-center space-x-2 mt-2">
                <span>Masuk Sekarang</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <!-- Pilihan Cepat Akun -->
        <div class="mt-6 pt-5 border-t border-[#FFC079]/20">
            <span class="block text-[11px] font-bold text-[#7C3E12]/60 uppercase tracking-wider mb-2 text-center">Pilihan Cepat Demo:</span>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" onclick="fillAccount('admin', 'admin123')" class="px-3 py-2 bg-[#FFEFAB]/40 hover:bg-[#FFEFAB] text-[#7C3E12] rounded-xl transition border border-[#FFC079]/40 font-bold text-center">
                    Admin
                </button>
                <button type="button" onclick="fillAccount('kasir', 'kasir123')" class="px-3 py-2 bg-[#FFEFAB]/40 hover:bg-[#FFEFAB] text-[#7C3E12] rounded-xl transition border border-[#FFC079]/40 font-bold text-center">
                    Kasir
                </button>
            </div>
        </div>

        <div class="mt-5 text-center">
            <a href="../index.php" class="text-xs font-bold text-[#FF7E24] hover:text-[#7C3E12] transition inline-flex items-center space-x-1.5">
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
