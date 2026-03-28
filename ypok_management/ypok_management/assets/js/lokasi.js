// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Lokasi.js loaded and DOM ready');
    
    // Check if modals exist
    const provinsiModal = document.getElementById('provinsiModal');
    const detailModal = document.getElementById('detailProvinsiModal');
    const dojoModal = document.getElementById('dojoModal');
    
    console.log('Provinsi Modal:', provinsiModal ? 'Found' : 'Not found');
    console.log('Detail Modal:', detailModal ? 'Found' : 'Not found');
    console.log('Dojo Modal:', dojoModal ? 'Found' : 'Not found');
});

// Provinsi Modal Functions
function openProvinsiModal() {
    console.log('openProvinsiModal called');
    const modal = document.getElementById('provinsiModal');
    if(!modal) {
        console.error('Modal provinsiModal not found!');
        return;
    }
    
    // Set display block
    modal.style.display = 'block';
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
    
    // Update form
    document.getElementById('provinsiModalTitle').textContent = '📍 Tambah Provinsi';
    document.getElementById('provinsi_action').value = 'create';
    document.getElementById('provinsiForm').reset();
    document.getElementById('provinsi_id').value = '';
    
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    console.log('Modal display:', modal.style.display);
    console.log('Modal visibility:', modal.style.visibility);
}

function closeProvinsiModal() {
    console.log('closeProvinsiModal called');
    const modal = document.getElementById('provinsiModal');
    if(modal) {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
        
        // Enable body scroll
        document.body.style.overflow = 'auto';
    }
}

function editProvinsi(id) {
    console.log('editProvinsi called with id:', id);
    fetch(`/actions/get_provinsi.php?id=${id}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Data received:', data);
                if(data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                
                document.getElementById('provinsiModal').style.display = 'block';
                document.getElementById('provinsiModal').style.visibility = 'visible';
                document.getElementById('provinsiModal').style.opacity = '1';
                document.body.style.overflow = 'hidden';
                
                document.getElementById('provinsiModalTitle').textContent = '✏️ Edit Provinsi';
                document.getElementById('provinsi_action').value = 'update';
                document.getElementById('provinsi_id').value = data.id;
                document.getElementById('nama_provinsi').value = data.nama_provinsi;
                document.getElementById('ibu_kota').value = data.ibu_kota || '';
                document.getElementById('url_logo_eksternal').value = data.logo_provinsi || '';
            } catch(e) {
                console.error('JSON parse error:', e);
                alert('Error parsing response: ' + text);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data provinsi: ' + error.message);
        });
}

function deleteProvinsi(id) {
    console.log('deleteProvinsi called with id:', id);
    if(confirm('⚠️ PERINGATAN!\n\nApakah Anda yakin ingin menghapus provinsi ini?\n\nSemua dojo dan data terkait di provinsi ini juga akan terhapus secara permanen!\n\nTindakan ini tidak dapat dibatalkan.')) {
        window.location.href = `/actions/provinsi_action.php?action=delete&id=${id}`;
    }
}

// Detail Provinsi Modal Functions
function viewProvinsiDetail(id) {
    console.log('viewProvinsiDetail called with id:', id);
    const modal = document.getElementById('detailProvinsiModal');
    if(!modal) {
        console.error('Modal detailProvinsiModal not found!');
        return;
    }
    
    modal.style.display = 'block';
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
    document.body.style.overflow = 'hidden';
    
    document.getElementById('detailProvinsiContent').innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner"></div><p>Memuat data...</p></div>';
    
    fetch(`/actions/get_provinsi_detail.php?id=${id}`)
        .then(response => {
            console.log('Detail response status:', response.status);
            return response.text();
        })
        .then(html => {
            console.log('Detail HTML received, length:', html.length);
            document.getElementById('detailProvinsiContent').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailProvinsiContent').innerHTML = '<div style="text-align: center; padding: 40px; color: red;">Gagal memuat data: ' + error.message + '</div>';
        });
}

function closeDetailProvinsiModal() {
    console.log('closeDetailProvinsiModal called');
    const modal = document.getElementById('detailProvinsiModal');
    if(modal) {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
        document.body.style.overflow = 'auto';
    }
}

// Dojo Modal Functions
function openDojoModal(provinsiId) {
    console.log('openDojoModal called with provinsiId:', provinsiId);
    const modal = document.getElementById('dojoModal');
    if(!modal) {
        console.error('Modal dojoModal not found!');
        return;
    }
    
    modal.style.display = 'block';
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
    document.body.style.overflow = 'hidden';
    
    document.getElementById('dojoModalTitle').textContent = '🥋 Tambah Dojo';
    document.getElementById('dojo_action').value = 'create';
    document.getElementById('dojoForm').reset();
    document.getElementById('dojo_id').value = '';
    document.getElementById('dojo_provinsi_id').value = provinsiId;
}

function closeDojoModal() {
    console.log('closeDojoModal called');
    const modal = document.getElementById('dojoModal');
    if(modal) {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
        document.body.style.overflow = 'auto';
    }
}

function editDojo(id) {
    console.log('editDojo called with id:', id);
    fetch(`/actions/get_dojo.php?id=${id}`)
        .then(response => {
            console.log('Dojo response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Dojo data received:', data);
                if(data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                
                const modal = document.getElementById('dojoModal');
                modal.style.display = 'block';
                modal.style.visibility = 'visible';
                modal.style.opacity = '1';
                document.body.style.overflow = 'hidden';
                
                document.getElementById('dojoModalTitle').textContent = '✏️ Edit Dojo';
                document.getElementById('dojo_action').value = 'update';
                document.getElementById('dojo_id').value = data.id;
                document.getElementById('dojo_provinsi_id').value = data.provinsi_id;
                document.getElementById('nama_dojo').value = data.nama_dojo;
                document.getElementById('alamat_lengkap').value = data.alamat_lengkap;
                document.getElementById('nama_ketua').value = data.nama_ketua;
                document.getElementById('no_telepon').value = data.no_telepon;
                document.getElementById('total_anggota').value = data.total_anggota;
                document.getElementById('anggota_aktif').value = data.anggota_aktif;
                document.getElementById('anggota_non_aktif').value = data.anggota_non_aktif;
                document.getElementById('status').value = data.status;
            } catch(e) {
                console.error('JSON parse error:', e);
                alert('Error parsing response: ' + text);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data dojo: ' + error.message);
        });
}

function deleteDojo(id, provinsiId) {
    console.log('deleteDojo called with id:', id, 'provinsiId:', provinsiId);
    if(confirm('⚠️ Apakah Anda yakin ingin menghapus dojo ini?\n\nData dojo akan dihapus secara permanen!')) {
        window.location.href = `/actions/dojo_action.php?action=delete&id=${id}&provinsi_id=${provinsiId}`;
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const modals = ['provinsiModal', 'detailProvinsiModal', 'dojoModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal && event.target == modal) {
            modal.style.display = 'none';
            modal.style.visibility = 'hidden';
            modal.style.opacity = '0';
            document.body.style.overflow = 'auto';
            console.log('Modal closed by clicking outside:', modalId);
        }
    });
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if(event.key === 'Escape') {
        closeProvinsiModal();
        closeDetailProvinsiModal();
        closeDojoModal();
        document.body.style.overflow = 'auto';
    }
});

// Enhanced search functionality with visual feedback
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const searchForm = document.getElementById('searchForm');

if(searchInput && searchForm) {
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);

        // Visual feedback saat mengetik
        searchInput.style.borderColor = '#fbbf24';
        searchInput.style.background = '#fffbeb';

        searchTimeout = setTimeout(() => {
            // Show loading indicator
            searchInput.style.borderColor = '#3b82f6';
            searchInput.style.background = '#eff6ff';

            searchForm.submit();
        }, 500); // Submit setelah 500ms user berhenti mengetik
    });

    // Clear search dengan ESC
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            searchInput.value = '';
            searchForm.submit();
        }
    });

    // Reset style on focus
    searchInput.addEventListener('focus', function() {
        if (!this.value) {
            searchInput.style.borderColor = '#667eea';
            searchInput.style.background = 'white';
        }
    });
}

// Enhanced search dojo in detail modal with visual feedback
let dojoSearchTimeout;
document.addEventListener('input', function(e) {
    if(e.target && e.target.id === 'searchDojo') {
        clearTimeout(dojoSearchTimeout);

        const searchInput = e.target;

        // Visual feedback saat mengetik
        searchInput.style.borderColor = '#fbbf24';
        searchInput.style.background = '#fffbeb';

        dojoSearchTimeout = setTimeout(() => {
            // Show filtering indicator
            searchInput.style.borderColor = '#3b82f6';
            searchInput.style.background = '#eff6ff';

            const searchTerm = searchInput.value.toLowerCase();
            const dojoCards = document.querySelectorAll('.dojo-card');

            dojoCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if(text.includes(searchTerm)) {
                    card.style.display = '';
                    card.style.animation = 'fadeIn 0.3s ease';
                } else {
                    card.style.display = 'none';
                }
            });

            // Reset style after filtering
            setTimeout(() => {
                if(searchInput.value) {
                    searchInput.style.borderColor = '#667eea';
                    searchInput.style.background = 'white';
                }
            }, 300);
        }, 500); // 500ms debounce
    }
});

// ESC key to clear dojo search
document.addEventListener('keydown', function(e) {
    const searchDojo = document.getElementById('searchDojo');
    if(searchDojo && document.activeElement === searchDojo && e.key === 'Escape') {
        e.preventDefault();
        searchDojo.value = '';

        // Show all dojo cards
        const dojoCards = document.querySelectorAll('.dojo-card');
        dojoCards.forEach(card => {
            card.style.display = '';
            card.style.animation = 'fadeIn 0.3s ease';
        });

        // Reset input style
        searchDojo.style.borderColor = '#667eea';
        searchDojo.style.background = 'white';
    }
});

// Reset dojo search input style on focus
document.addEventListener('focus', function(e) {
    if(e.target && e.target.id === 'searchDojo') {
        if(!e.target.value) {
            e.target.style.borderColor = '#667eea';
            e.target.style.background = 'white';
        }
    }
}, true);

// Make functions globally available
window.openProvinsiModal = openProvinsiModal;
window.closeProvinsiModal = closeProvinsiModal;
window.editProvinsi = editProvinsi;
window.deleteProvinsi = deleteProvinsi;
window.viewProvinsiDetail = viewProvinsiDetail;
window.closeDetailProvinsiModal = closeDetailProvinsiModal;
window.openDojoModal = openDojoModal;
window.closeDojoModal = closeDojoModal;
window.editDojo = editDojo;
window.deleteDojo = deleteDojo;

console.log('All functions registered globally');
