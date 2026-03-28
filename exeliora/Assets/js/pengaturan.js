// JavaScript untuk halaman Pengaturan
document.addEventListener('DOMContentLoaded', function() {
    console.log('Halaman Pengaturan dimuat');
});

// Fungsi untuk menyimpan perubahan akun
function simpanPerubahan() {
    const namaPengguna = document.getElementById('nama-pengguna').value;
    const email = document.getElementById('email').value;
    const passwordLama = document.getElementById('password-lama').value;
    const passwordBaru = document.getElementById('password-baru').value;
    
    // Validasi input
    if (!namaPengguna || !email) {
        alert('Nama Pengguna dan Email wajib diisi!');
        return;
    }
    
    if (!passwordLama) {
        alert('Password Lama wajib diisi untuk verifikasi!');
        return;
    }
    
    // Validasi email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Format email tidak valid!');
        return;
    }
    
    // Jika ada password baru, validasi panjang minimum
    if (passwordBaru && passwordBaru.length < 6) {
        alert('Password baru minimal 6 karakter!');
        return;
    }
    
    // Simulasi penyimpanan data
    const dataPerubahan = {
        namaPengguna: namaPengguna,
        email: email,
        passwordLama: passwordLama,
        passwordBaru: passwordBaru || null
    };
    
    console.log('Data yang akan disimpan:', dataPerubahan);
    
    // Tampilkan pesan sukses
    alert('Perubahan berhasil disimpan!\n\nNama: ' + namaPengguna + '\nEmail: ' + email);
    
    // Reset password fields
    document.getElementById('password-lama').value = '';
    document.getElementById('password-baru').value = '';
}

// Fungsi untuk menambah sensor notifikasi
function tambahSensorNotif() {
    const sensorId = document.getElementById('sensor-id').value.trim();
    
    // Validasi input
    if (!sensorId) {
        alert('ID Sensor wajib diisi!');
        return;
    }
    
    // Validasi format ID sensor
    if (sensorId.length < 4) {
        alert('ID Sensor minimal 4 karakter!');
        return;
    }
    
    // Cek apakah sensor ID sudah ada
    const sensorList = document.querySelector('.sensor-list');
    const existingSensors = sensorList.querySelectorAll('.sensor-id-badge');
    
    for (let sensor of existingSensors) {
        if (sensor.textContent === sensorId) {
            alert('ID Sensor sudah terdaftar!');
            return;
        }
    }
    
    // Buat elemen sensor baru
    const sensorItem = document.createElement('div');
    sensorItem.className = 'sensor-item';
    sensorItem.innerHTML = `
        <span class="sensor-id-badge">${sensorId}</span>
        <span class="sensor-name">Sensor Baru - Belum dikonfigurasi</span>
        <button class="btn-remove-sensor" onclick="hapusSensorNotif('${sensorId}')">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Tambahkan ke daftar
    sensorList.appendChild(sensorItem);
    
    // Reset input
    document.getElementById('sensor-id').value = '';
    
    // Tampilkan pesan sukses
    alert('Sensor ' + sensorId + ' berhasil ditambahkan ke daftar notifikasi!');
}

// Fungsi untuk menghapus sensor dari daftar notifikasi
function hapusSensorNotif(sensorId) {
    if (confirm('Apakah Anda yakin ingin menghapus sensor ' + sensorId + ' dari daftar notifikasi?')) {
        // Cari dan hapus elemen sensor
        const sensorList = document.querySelector('.sensor-list');
        const sensorItems = sensorList.querySelectorAll('.sensor-item');
        
        sensorItems.forEach(item => {
            const badge = item.querySelector('.sensor-id-badge');
            if (badge && badge.textContent === sensorId) {
                item.remove();
                alert('Sensor ' + sensorId + ' berhasil dihapus dari daftar notifikasi!');
            }
        });
    }
}
