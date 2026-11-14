<!-- components/scanner_modal.php -->
<!-- Modal Scanner QR Code untuk Kasir & Admin Resto -->

<!-- Library HTML5 QR Code Scanner -->
<script src="https://unpkg.com/html5-qrcode"></script>

<!-- Modal Overlay -->
<div id="scanner-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto">
    <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden my-auto animate-fade-in">
        
        <!-- Header Modal -->
        <div class="bg-slate-50 px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-base shadow-sm">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 leading-tight">Pemindai QR Code Pesanan</h3>
                    <p class="text-[11px] text-slate-400">Validasi pembayaran tunai & digital anti-fake order</p>
                </div>
            </div>
            <button type="button" onclick="closeScannerModal()" class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-rose-100 hover:text-rose-600 text-slate-500 flex items-center justify-center transition text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="p-5 space-y-4 max-h-[80vh] overflow-y-auto">
            
            <!-- Viewfinder Kamera Scanner -->
            <div id="scanner-camera-box" class="space-y-3">
                <div class="relative bg-slate-950 rounded-2xl overflow-hidden shadow-inner border border-slate-800 flex items-center justify-center min-h-[260px]">
                    <div id="qr-reader" class="w-full"></div>
                    <div id="scanner-loading" class="absolute inset-0 flex flex-col items-center justify-center text-white bg-slate-900/90 text-xs space-y-2 pointer-events-none">
                        <i class="fa-solid fa-camera fa-fade text-2xl text-orange-500"></i>
                        <span>Menyiapkan kamera scanner...</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Arahkan kamera ke QR di HP pembeli</span>
                    </span>
                    <button type="button" id="btn-toggle-camera" onclick="restartCamera()" class="text-orange-600 font-bold hover:underline">
                        <i class="fa-solid fa-arrows-rotate mr-1"></i>Ganti Kamera
                    </button>
                </div>
            </div>

            <!-- Alternatif: Input Manual / Scanner Gun Barcode -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 text-xs">
                <label class="block font-bold text-slate-700 uppercase tracking-wider text-[10px] mb-1.5">
                    <i class="fa-solid fa-barcode text-orange-500 mr-1"></i> Atau Gunakan Scanner Gun / Input Kode:
                </label>
                <div class="flex gap-2">
                    <input type="text" id="manual-order-code" placeholder="Contoh: ORD-20261113-001" 
                           class="flex-1 px-3 py-2 bg-white border border-slate-200 rounded-xl font-bold text-slate-800 uppercase focus:outline-none focus:border-orange-500 transition text-xs"
                           onkeypress="if(event.key === 'Enter') handleManualLookup()">
                    <button type="button" onclick="handleManualLookup()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl active:scale-95 transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                        <span>Cari</span>
                    </button>
                </div>
            </div>

            <!-- Pesan Notifikasi Status -->
            <div id="scanner-alert" class="hidden p-3 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm">
                <span id="scanner-alert-msg"></span>
                <button type="button" onclick="hideScannerAlert()" class="text-xs font-bold underline ml-2">Tutup</button>
            </div>

            <!-- Kartu Hasil Pemindaian (Muncul setelah QR terdeteksi) -->
            <div id="scan-result-card" class="hidden bg-orange-50/50 border border-orange-200 rounded-2xl p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-orange-100 pb-2.5">
                    <div class="flex items-center space-x-2">
                        <span id="res-meja" class="px-2.5 py-0.5 rounded-md bg-orange-500 text-white font-bold text-xs">Meja #--</span>
                        <span id="res-kode" class="font-mono font-black text-slate-800 text-xs">ORD-XXXX</span>
                    </div>
                    <span id="res-status-bayar" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase">--</span>
                </div>

                <div class="flex justify-between items-start text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Nama Pelanggan:</span>
                        <h4 id="res-pelanggan" class="font-bold text-slate-900 text-sm">--</h4>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 block text-[10px]">Total Tagihan:</span>
                        <h4 id="res-total" class="font-black text-orange-600 text-base">Rp 0</h4>
                    </div>
                </div>

                <!-- Daftar Rincian Menu -->
                <div class="bg-white rounded-xl p-3 border border-orange-100/80">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pesanan Menu:</span>
                    <div id="res-items-list" class="space-y-1.5 text-xs divide-y divide-slate-100 max-h-32 overflow-y-auto">
                        <!-- Diisi via JS -->
                    </div>
                </div>

                <!-- Tombol Aksi Validasi Pembayaran -->
                <div id="res-action-box" class="pt-1">
                    <button type="button" id="btn-validate-order" onclick="validateCurrentScannedOrder()" 
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs shadow-md shadow-emerald-600/20 active:scale-95 transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                        <span>Validasi Pembayaran (Set Lunas & Masak)</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Footer Modal -->
        <div class="bg-slate-50 px-5 py-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
            <span>Sistem POS Resto Enak Rasa</span>
            <button type="button" onclick="closeScannerModal()" class="px-3.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition">
                Tutup Scanner
            </button>
        </div>

    </div>
</div>

<script>
    let html5QrCode = null;
    let currentScannedOrder = null;
    let cameraList = [];
    let currentCameraIndex = 0;
    let isScanningActive = false;

    // Suara Beep via Web Audio API saat scan berhasil
    function playScannerBeep() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) {
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.14);
            }
        } catch (e) {
            console.log('Audio Context tidak diizinkan otomatis:', e);
        }
    }

    function openScannerModal() {
        const modal = document.getElementById('scanner-modal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        document.getElementById('manual-order-code').value = '';
        hideScannerAlert();
        document.getElementById('scan-result-card').classList.add('hidden');
        
        startCameraScanner();
    }

    function closeScannerModal() {
        const modal = document.getElementById('scanner-modal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        stopCameraScanner();
    }

    async function startCameraScanner() {
        const loading = document.getElementById('scanner-loading');
        loading.classList.remove('hidden');

        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }

            cameraList = await Html5Qrcode.getCameras();
            if (cameraList && cameraList.length > 0) {
                const cameraId = cameraList[currentCameraIndex % cameraList.length].id;
                
                await html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 220, height: 220 }
                    },
                    (decodedText, decodedResult) => {
                        onQrCodeSuccess(decodedText);
                    },
                    (errorMessage) => {
                        // Error parse frame diabaikan saat scanning
                    }
                );
                isScanningActive = true;
                loading.classList.add('hidden');
            } else {
                loading.innerHTML = `<span class="text-rose-400 p-3 text-center">Kamera tidak terdeteksi. Silakan gunakan input manual / barcode scanner gun di bawah.</span>`;
            }
        } catch (err) {
            console.error('Gagal mengakses kamera:', err);
            loading.innerHTML = `<div class="p-3 text-center text-rose-300">
                <i class="fa-solid fa-triangle-exclamation text-xl mb-1 block"></i>
                <span>Izin kamera tidak aktif. Gunakan input kode di bawah.</span>
            </div>`;
        }
    }

    async function stopCameraScanner() {
        if (html5QrCode && isScanningActive) {
            try {
                await html5QrCode.stop();
                isScanningActive = false;
            } catch (err) {
                console.warn('Gagal mematikan kamera:', err);
            }
        }
    }

    async function restartCamera() {
        if (cameraList.length > 1) {
            await stopCameraScanner();
            currentCameraIndex++;
            startCameraScanner();
        } else {
            showScannerAlert('Hanya ada 1 kamera yang terdeteksi di perangkat ini.', 'info');
        }
    }

    function onQrCodeSuccess(decodedText) {
        playScannerBeep();
        console.log('QR Code terdeteksi:', decodedText);
        lookupOrder(decodedText);
    }

    function handleManualLookup() {
        const code = document.getElementById('manual-order-code').value.trim();
        if (!code) {
            showScannerAlert('Silakan masukkan Kode Pesanan terlebih dahulu!', 'error');
            return;
        }
        lookupOrder(code);
    }

    async function lookupOrder(qrData) {
        showScannerAlert('Sedang memverifikasi pesanan...', 'info');

        try {
            // Tentukan base URL API tergantung posisi folder (admin atau kasir)
            const apiUrl = '../api/scan_validate.php';

            const res = await fetch(`${apiUrl}?qr_data=${encodeURIComponent(qrData)}&action=check`);
            const json = await res.json();

            if (json.status === 'success' && json.data) {
                currentScannedOrder = json.data;
                displayScannedOrder(json.data);
                showScannerAlert('Pesanan berhasil ditemukan!', 'success');
            } else {
                showScannerAlert(json.message || 'Pesanan tidak ditemukan!', 'error');
                document.getElementById('scan-result-card').classList.add('hidden');
            }
        } catch (err) {
            console.error('Fetch scan error:', err);
            showScannerAlert('Gagal terhubung ke API scanner!', 'error');
        }
    }

    function displayScannedOrder(order) {
        const card = document.getElementById('scan-result-card');
        card.classList.remove('hidden');

        document.getElementById('res-meja').innerText = `Meja #${order.nomor_meja}`;
        document.getElementById('res-kode').innerText = order.kode_pesanan;
        document.getElementById('res-pelanggan').innerText = order.nama_pelanggan;
        document.getElementById('res-total').innerText = order.total_harga_formatted || `Rp ${order.total_harga}`;

        const statusEl = document.getElementById('res-status-bayar');
        const actionBox = document.getElementById('res-action-box');

        if (order.status_pembayaran === 'lunas') {
            statusEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200';
            statusEl.innerHTML = '<i class="fa-solid fa-check mr-1"></i>LUNAS';
            actionBox.innerHTML = `
                <div class="p-3 bg-emerald-100/60 border border-emerald-200 rounded-xl text-center text-xs font-bold text-emerald-800 flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>Pesanan ini sudah lunas & sedang diproses dapur.</span>
                </div>
            `;
        } else {
            statusEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-200';
            statusEl.innerHTML = '<i class="fa-solid fa-hourglass-half mr-1"></i>BELUM BAYAR';
            actionBox.innerHTML = `
                <button type="button" onclick="validateCurrentScannedOrder()" 
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs shadow-md shadow-emerald-600/20 active:scale-95 transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-circle-check text-sm"></i>
                    <span>Validasi Pembayaran (Set Lunas & Mulai Masak)</span>
                </button>
            `;
        }

        // Tampilkan daftar item
        const itemsList = document.getElementById('res-items-list');
        let html = '';
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                html += `
                    <div class="flex justify-between items-start py-1">
                        <div>
                            <span class="font-bold text-slate-800">${item.jumlah}x ${item.nama_menu}</span>
                            ${item.catatan ? `<p class="text-[10px] text-orange-600 italic">"${item.catatan}"</p>` : ''}
                        </div>
                        <span class="font-semibold text-slate-700">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</span>
                    </div>
                `;
            });
        }
        itemsList.innerHTML = html;
    }

    async function validateCurrentScannedOrder() {
        if (!currentScannedOrder) return;

        const btn = document.querySelector('#res-action-box button');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i><span>Memvalidasi...</span>`;
        }

        try {
            const apiUrl = '../api/scan_validate.php';
            const res = await fetch(`${apiUrl}?kode_pesanan=${encodeURIComponent(currentScannedOrder.kode_pesanan)}&action=validate`);
            const json = await res.json();

            if (json.status === 'success' && json.data) {
                currentScannedOrder = json.data;
                displayScannedOrder(json.data);
                showScannerAlert('Pembayaran berhasil divalidasi! Pesanan siap dimasak dapur.', 'success');

                // Jika di halaman kasir, trigger reload antrean agar langsung update
                if (typeof fetchOrders === 'function') {
                    fetchOrders();
                }
            } else {
                showScannerAlert(json.message || 'Gagal memvalidasi pesanan!', 'error');
            }
        } catch (err) {
            showScannerAlert('Terjadi kesalahan saat memvalidasi pembayaran!', 'error');
        }
    }

    function showScannerAlert(msg, type) {
        const alertBox = document.getElementById('scanner-alert');
        const alertMsg = document.getElementById('scanner-alert-msg');
        alertBox.classList.remove('hidden', 'bg-rose-50', 'text-rose-700', 'border-rose-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200', 'bg-blue-50', 'text-blue-700', 'border-blue-200');

        if (type === 'error') {
            alertBox.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
        } else if (type === 'success') {
            alertBox.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
        } else {
            alertBox.classList.add('bg-blue-50', 'text-blue-700', 'border', 'border-blue-200');
        }

        alertMsg.innerText = msg;
    }

    function hideScannerAlert() {
        document.getElementById('scanner-alert').classList.add('hidden');
    }
</script>

