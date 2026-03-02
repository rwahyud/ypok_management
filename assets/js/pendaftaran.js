function switchTab(tab) {
    window.location.href = 'pendaftaran.php?tab=' + tab;
}

function switchFormTab(tab) {
    // Hide all forms
    document.getElementById('form-msh').style.display = 'none';
    document.getElementById('form-kohai').style.display = 'none';
    
    // Show selected form
    document.getElementById('form-' + tab).style.display = 'block';
    
    // Update tab buttons
    const formTabs = document.querySelectorAll('.form-tab-btn');
    formTabs.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Also switch the table tab
    switchTab(tab);
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            preview.classList.add('active');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function confirmExport(id, type) {
    const typeName = type === 'master_sabuk_hitam' ? 'MSH' : 'Kohai';
    const message = `Apakah Anda yakin ingin export data ini ke tabel ${typeName}?\n\n` +
                   `Data akan dipindahkan ke database utama dan status akan berubah menjadi Aktif.\n` +
                   `Proses ini tidak dapat dibatalkan.`;

    if(confirm(message)) {
        // Show loading toast
        showLoadingToast('Sedang memproses export...');

        // Redirect to export action
        window.location.href = `actions/export_to_database.php?id=${id}&type=${type}`;
    }
}

function confirmDelete(id, type) {
    const typeName = type === 'msh' ? 'MSH' : 'Kohai';
    const message = `Apakah Anda yakin ingin menghapus data pendaftaran ${typeName} ini?\n\n` +
                   `Data yang sudah dihapus tidak dapat dikembalikan.`;
    
    if(confirm(message)) {
        // Show loading toast
        showLoadingToast('Sedang menghapus data...');
        
        window.location.href = `actions/delete_pendaftaran.php?id=${id}&type=${type}`;
    }
}

function editPendaftaran(id, type) {
    // Redirect ke halaman yang sama dengan parameter edit
    window.location.href = `pendaftaran.php?tab=${type}&edit=${id}`;
}

function exportToPDF(id, type) {
    window.open(`export/pendaftaran_pdf.php?id=${id}&type=${type}`, '_blank');
}

function openExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function toggleCustomDate(value) {
    const customDateDiv = document.getElementById('customDateRange');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    if(value === 'custom') {
        customDateDiv.style.display = 'block';
        startDate.required = true;
        endDate.required = true;
    } else {
        customDateDiv.style.display = 'none';
        startDate.required = false;
        endDate.required = false;
    }
}

function handleExportSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Build query string
    const params = new URLSearchParams();
    params.append('format', formData.get('format'));
    params.append('periode', formData.get('periode'));
    params.append('type', formData.get('type'));
    params.append('ketua', formData.get('ketua'));
    params.append('admin', formData.get('admin'));
    
    if (formData.get('periode') === 'custom') {
        const startDate = formData.get('start_date');
        const endDate = formData.get('end_date');
        
        if (!startDate || !endDate) {
            alert('Silakan pilih tanggal mulai dan tanggal akhir');
            return;
        }
        
        params.append('start_date', startDate);
        params.append('end_date', endDate);
    }
    
    // Open export page in new tab
    const url = `actions/export_pendaftaran.php?${params.toString()}`;
    window.open(url, '_blank');
    
    // Close modal
    closeExportModal();
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('exportModal');
    if(modal) {
        modal.addEventListener('click', function(e) {
            if(e.target === modal) {
                closeExportModal();
            }
        });
    }
    
    // Close with ESC key
    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape') {
            closeExportModal();
        }
    });
});

function showLoadingToast(message) {
    // Remove existing toast if any
    const existingToast = document.getElementById('loading-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create loading toast
    const toast = document.createElement('div');
    toast.id = 'loading-toast';
    toast.className = 'toast-notification toast-info';
    toast.innerHTML = `
        <div class="toast-icon">⏳</div>
        <div class="toast-content">
            <div class="toast-title">Memproses...</div>
            <div class="toast-message">${message}</div>
        </div>
    `;
    document.body.appendChild(toast);
}

// Auto hide toast after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const toast = document.getElementById('toast');
    if(toast) {
        setTimeout(function() {
            closeToast();
        }, 5000);
    }
});
