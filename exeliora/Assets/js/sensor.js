// JavaScript untuk halaman Sensor
document.addEventListener('DOMContentLoaded', function() {
    // Handle toggle switches untuk lampu
    const toggles = document.querySelectorAll('.toggle-switch input');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const roomItem = this.closest('.room-light-item');
            const slider = roomItem.querySelector('.brightness-slider-inline');
            const valueDisplay = roomItem.querySelector('.brightness-value');
            
            if (this.checked) {
                slider.disabled = false;
                slider.value = 75;
                valueDisplay.textContent = '75%';
            } else {
                slider.disabled = true;
                slider.value = 0;
                valueDisplay.textContent = '0%';
            }
        });
    });
    
    // Handle brightness sliders
    const sliders = document.querySelectorAll('.brightness-slider-inline');
    
    sliders.forEach(slider => {
        slider.addEventListener('input', function() {
            const roomItem = this.closest('.room-light-item');
            const valueDisplay = roomItem.querySelector('.brightness-value');
            valueDisplay.textContent = this.value + '%';
        });
    });
});

// Fungsi untuk menampilkan detail sensor
function showSensorDetail(sensorType) {
    const listView = document.getElementById('sensor-list-view');
    const detailView = document.getElementById('sensor-detail-view');
    const pageTitle = document.getElementById('page-title');
    
    // Sembunyikan list, tampilkan detail
    listView.style.display = 'none';
    detailView.style.display = 'block';
    
    // Sembunyikan semua section sensor
    const allSections = detailView.querySelectorAll('.sensor-section');
    allSections.forEach(section => {
        section.style.display = 'none';
    });
    
    // Tampilkan sensor yang dipilih
    if (sensorType === 'suhu') {
        pageTitle.textContent = 'Sensor Suhu - DHT 11';
        allSections[0].style.display = 'block'; // Sensor Suhu
    } else if (sensorType === 'ldr') {
        pageTitle.textContent = 'Sensor Cahaya - LDR';
        allSections[1].style.display = 'block'; // Sensor Cahaya
    }
    
    // Tampilkan tombol kembali
    document.querySelector('.back-button-container').style.display = 'block';
}

// Fungsi untuk kembali ke daftar sensor
function backToSensorList() {
    const listView = document.getElementById('sensor-list-view');
    const detailView = document.getElementById('sensor-detail-view');
    const pageTitle = document.getElementById('page-title');
    
    // Tampilkan list, sembunyikan detail
    listView.style.display = 'block';
    detailView.style.display = 'none';
    
    // Reset judul
    pageTitle.textContent = 'Data Sensor';
}

// Fungsi untuk Sensor Suhu
function lihatGrafik(sensor) {
    alert('Menampilkan grafik untuk sensor ' + sensor);
    // Implementasi untuk menampilkan grafik
}

function lihatRiwayat(sensor) {
    alert('Menampilkan riwayat untuk sensor ' + sensor);
    // Implementasi untuk menampilkan riwayat
}

function TambahSensor(sensor) {
    alert('Menambah sensor ' + sensor);
    // Implementasi untuk menambah sensor
}

function EditSensor(sensor) {
    alert('Menambah sensor ' + sensor);
    // Implementasi untuk menambah sensor
}

function editBatasNilai(sensor) {
    const batasAtas = prompt('Masukkan batas nilai atas:', '30');
    const batasBawah = prompt('Masukkan batas nilai bawah:', '20');
    if (batasAtas && batasBawah) {
        alert('Batas nilai sensor ' + sensor + ' telah diubah\nAtas: ' + batasAtas + '°C\nBawah: ' + batasBawah + '°C');
    }
}

function klikButtonBaca(sensor) {
    alert('Membaca data sensor ' + sensor + '...');
    // Implementasi untuk membaca data sensor
}

function hapusSensor(sensor) {
    if (confirm('Apakah Anda yakin ingin menghapus sensor ' + sensor + '?')) {
        alert('Sensor ' + sensor + ' telah dihapus');
        // Implementasi untuk menghapus sensor
    }
}

// Fungsi untuk Sensor Cahaya (LDR)
function readIdBahan(sensor) {
    alert('Membaca ID Bahan Sensor ' + sensor);
    // Implementasi untuk read ID bahan sensor
}

function editRuang(sensor) {
    const namaRuang = prompt('Masukkan nama ruang baru:', 'Ruang Tamu');
    if (namaRuang) {
        alert('Ruang sensor ' + sensor + ' telah diubah menjadi: ' + namaRuang);
    }
}

function pilihRuang(sensor) {
    const ruangan = ['Ruang Tamu', 'Kamar Tidur', 'Dapur', 'Teras', 'Kamar Mandi'];
    let pilihan = 'Pilih Ruang:\n';
    ruangan.forEach((ruang, index) => {
        pilihan += (index + 1) + '. ' + ruang + '\n';
    });
    const selected = prompt(pilihan, '1');
    if (selected) {
        alert('Ruang dipilih: ' + ruangan[parseInt(selected) - 1]);
    }
}

function tambahBaca(sensor) {
    alert('Menambah pembacaan untuk sensor ' + sensor);
    // Implementasi untuk menambah pembacaan
}
