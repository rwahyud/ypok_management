function toggleKategoriInput() {
    const select = document.getElementById('kategori_select');
    const customGroup = document.getElementById('kategori_custom_group');
    const customInput = document.getElementById('kategori_custom');
    const hiddenInput = document.getElementById('kategori');

    if (select.value === 'lainnya') {
        customGroup.style.display = 'block';
        customInput.required = true;
        hiddenInput.value = '';
    } else {
        customGroup.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
        hiddenInput.value = select.value;
    }
}

function togglePembayaranSebagian() {
    const status = document.getElementById('status').value;
    const sebagianFields = document.getElementById('pembayaranSebagianFields');
    const totalTagihan = document.getElementById('total_tagihan');
    const nominalDibayar = document.getElementById('nominal_dibayar');

    if (status === 'Sebagian') {
        sebagianFields.style.display = 'block';
        totalTagihan.required = true;
        nominalDibayar.required = true;
    } else {
        sebagianFields.style.display = 'none';
        totalTagihan.required = false;
        nominalDibayar.required = false;
        totalTagihan.value = '';
        nominalDibayar.value = '';
        document.getElementById('sisa').value = '';
    }
}

function calculateSisa() {
    const totalTagihan = parseFloat(document.getElementById('total_tagihan').value) || 0;
    const nominalDibayar = parseFloat(document.getElementById('nominal_dibayar').value) || 0;
    const sisaField = document.getElementById('sisa');

    const sisa = totalTagihan - nominalDibayar;
    sisaField.value = sisa >= 0 ? sisa : 0;
}

// Before submit, set the kategori value
document.getElementById('pembayaranForm').addEventListener('submit', function(e) {
    const select = document.getElementById('kategori_select');
    const customInput = document.getElementById('kategori_custom');
    const hiddenInput = document.getElementById('kategori');
    
    if (select.value === 'lainnya') {
        if (!customInput.value.trim()) {
            e.preventDefault();
            alert('Silahkan masukkan kategori baru');
            return false;
        }
        hiddenInput.value = customInput.value.trim();
    } else {
        hiddenInput.value = select.value;
    }
});

function openPembayaranModal() {
    document.getElementById('pembayaranModal').style.display = 'block';
    document.getElementById('modalTitle').textContent = '✨ Tambah Pembayaran';
    document.getElementById('formAction').value = 'tambah';
    document.getElementById('pembayaranForm').reset();
    document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('kategori_custom_group').style.display = 'none';
    document.getElementById('pembayaranSebagianFields').style.display = 'none';
}

function closePembayaranModal() {
    document.getElementById('pembayaranModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // Re-enable scroll
}

function editPembayaran(id) {
    fetch(`../../actions/pembayaran_action.php?action=get&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                console.error('Server error:', data.error);
                return;
            }

            // Pastikan semua field ada
            if (!data.id) {
                alert('Data pembayaran tidak valid');
                console.error('Invalid data:', data);
                return;
            }

            document.getElementById('modalTitle').textContent = '✏️ Edit Pembayaran';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('pembayaranId').value = data.id;
            document.getElementById('tanggal').value = data.tanggal;

            // Set kategori
            const kategoriSelect = document.getElementById('kategori_select');
            const kategoriOptions = Array.from(kategoriSelect.options).map(opt => opt.value);

            if (kategoriOptions.includes(data.kategori)) {
                kategoriSelect.value = data.kategori;
                document.getElementById('kategori_custom_group').style.display = 'none';
                document.getElementById('kategori_custom').required = false;
            } else {
                kategoriSelect.value = 'lainnya';
                document.getElementById('kategori_custom_group').style.display = 'block';
                document.getElementById('kategori_custom').value = data.kategori;
                document.getElementById('kategori_custom').required = true;
            }
            document.getElementById('kategori').value = data.kategori;

            document.getElementById('nama_kohai').value = data.nama_kohai || '';
            document.getElementById('keterangan').value = data.keterangan || '';
            document.getElementById('jumlah').value = data.jumlah || '';
            document.getElementById('metode_pembayaran').value = data.metode_pembayaran || '';
            document.getElementById('status').value = data.status || '';

            // Handle pembayaran sebagian fields
            if (data.status === 'Sebagian') {
                document.getElementById('pembayaranSebagianFields').style.display = 'block';
                document.getElementById('total_tagihan').value = data.total_tagihan || '';
                document.getElementById('nominal_dibayar').value = data.nominal_dibayar || '';
                document.getElementById('sisa').value = data.sisa || '';
                document.getElementById('total_tagihan').required = true;
                document.getElementById('nominal_dibayar').required = true;
            } else {
                document.getElementById('pembayaranSebagianFields').style.display = 'none';
                document.getElementById('total_tagihan').required = false;
                document.getElementById('nominal_dibayar').required = false;
            }

            document.getElementById('pembayaranModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent background scroll
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Terjadi kesalahan saat mengambil data pembayaran: ' + error.message);
        });
}

function viewPembayaran(id) {
    fetch(`../../actions/pembayaran_action.php?action=get&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                console.error('Server error:', data.error);
                return;
            }
            
            // Pastikan semua field ada
            if (!data.id) {
                alert('Data pembayaran tidak valid');
                console.error('Invalid data:', data);
                return;
            }
            
            const getBadgeClass = (kategori) => {
                const badges = {
                    'Ujian': 'primary',
                    'Kyu': 'info',
                    'Rakernas': 'warning'
                };
                return badges[kategori] || 'secondary';
            };
            
            const getStatusBadge = (status) => {
                const badges = {
                    'Lunas': 'success',
                    'Sebagian': 'warning',
                    'Belum Bayar': 'danger'
                };
                return badges[status] || 'secondary';
            };
            
            const formatDate = (dateString) => {
                try {
                    return new Date(dateString).toLocaleDateString('id-ID', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                } catch(e) {
                    return dateString;
                }
            };
            
            const formatDateTime = (dateString) => {
                try {
                    return new Date(dateString).toLocaleString('id-ID');
                } catch(e) {
                    return dateString;
                }
            };

            let jumlahHTML = '';
            if (data.status === 'Sebagian' && data.total_tagihan && data.nominal_dibayar) {
                jumlahHTML = `
                    <div style="margin-bottom: 10px;">
                        <span class="text-warning" style="font-weight: bold; font-size: 18px;">Rp ${Number(data.nominal_dibayar).toLocaleString('id-ID')}</span>
                        <small style="display: block; color: #718096; margin-top: 5px;">dari Rp ${Number(data.total_tagihan).toLocaleString('id-ID')}</small>
                    </div>
                    <div style="padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                        <strong style="color: #856404;">⚠️ Sisa Pembayaran:</strong>
                        <span style="color: #dc3545; font-weight: bold; font-size: 16px; margin-left: 10px;">Rp ${Number(data.sisa).toLocaleString('id-ID')}</span>
                    </div>
                `;
            } else {
                jumlahHTML = `<span class="text-success" style="font-weight: bold; font-size: 18px;">Rp ${Number(data.jumlah).toLocaleString('id-ID')}</span>`;
            }

            const content = `
                <div class="detail-row">
                    <label>📅 Tanggal:</label>
                    <span>${formatDate(data.tanggal)}</span>
                </div>
                <div class="detail-row">
                    <label>📂 Kategori:</label>
                    <span><span class="badge badge-${getBadgeClass(data.kategori)}">${data.kategori}</span></span>
                </div>
                <div class="detail-row">
                    <label>👤 Nama Kohai:</label>
                    <span><strong>${data.nama_kohai || '-'}</strong></span>
                </div>
                <div class="detail-row">
                    <label>📝 Keterangan:</label>
                    <span>${data.keterangan}</span>
                </div>
                <div class="detail-row">
                    <label>💰 Jumlah:</label>
                    <div style="flex: 1;">${jumlahHTML}</div>
                </div>
                <div class="detail-row">
                    <label>💳 Metode Pembayaran:</label>
                    <span>${data.metode_pembayaran}</span>
                </div>
                <div class="detail-row">
                    <label>✅ Status:</label>
                    <span><span class="badge badge-${getStatusBadge(data.status)}">${data.status}</span></span>
                </div>
                <div class="detail-row">
                    <label>🕒 Dibuat:</label>
                    <span>${formatDateTime(data.created_at)}</span>
                </div>
            `;
            
            document.getElementById('viewContent').innerHTML = content;
            document.getElementById('viewModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent background scroll
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Terjadi kesalahan saat mengambil data pembayaran: ' + error.message);
        });
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // Re-enable scroll
}

function deletePembayaran(id) {
    if(confirm('Apakah Anda yakin ingin menghapus data pembayaran ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../../actions/pembayaran_action.php';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'hapus';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;

        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function printInvoice(id) {
    // Open invoice in new window for printing
    window.open('invoice.php?id=' + id, '_blank', 'width=900,height=700');
}

function openExportModal() {
    document.getElementById('exportModal').style.display = 'block';
    document.getElementById('exportForm').reset();
    // Admin name sudah di-set dari PHP di HTML, tidak perlu set ulang
    document.getElementById('customDateFields').style.display = 'none';
}

function closeExportModal() {
    document.getElementById('exportModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // Re-enable scroll
}

function togglePeriodeFields() {
    const periode = document.getElementById('periode').value;
    const customFields = document.getElementById('customDateFields');
    const dariTanggal = document.getElementById('dari_tanggal');
    const sampaiTanggal = document.getElementById('sampai_tanggal');
    
    if (periode === 'custom') {
        customFields.style.display = 'block';
        dariTanggal.required = true;
        sampaiTanggal.required = true;
    } else {
        customFields.style.display = 'none';
        dariTanggal.required = false;
        sampaiTanggal.required = false;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const pembayaranModal = document.getElementById('pembayaranModal');
    const viewModal = document.getElementById('viewModal');
    const exportModal = document.getElementById('exportModal');
    
    if (event.target == pembayaranModal) {
        closePembayaranModal();
    }
    if (event.target == viewModal) {
        closeViewModal();
    }
    if (event.target == exportModal) {
        closeExportModal();
    }
}
