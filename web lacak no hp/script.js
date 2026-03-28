// Global variables
let map;
let markers = {};
let accuracyCircles = []; // Array untuk menyimpan accuracy circles
let userLocation = null;
let sharingEnabled = true;
let watchId = null;

// Initialize map
function initMap() {
    // Map akan di-center ke lokasi user setelah mendapat GPS
    map = L.map('map').setView([0, 0], 2); // Mulai dari world view
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    console.log('Map initialized - waiting for GPS location...');
}

// Get user's location
function startLocationTracking() {
    if (!navigator.geolocation) {
        alert('❌ Geolocation tidak didukung oleh browser Anda. Gunakan browser modern seperti Chrome, Firefox, atau Edge.');
        return;
    }
    
    console.log('🔍 Meminta akses lokasi GPS...');
    
    const options = {
        enableHighAccuracy: true,  // Gunakan GPS untuk akurasi tinggi
        timeout: 30000,            // Timeout 30 detik
        maximumAge: 0              // Jangan gunakan cache, selalu ambil lokasi fresh
    };
    
    // Watch position for real-time updates
    watchId = navigator.geolocation.watchPosition(
        handleLocationSuccess,
        handleLocationError,
        options
    );
    
    console.log('Location tracking started');
}

// Handle successful location
function handleLocationSuccess(position) {
    const latitude = position.coords.latitude;
    const longitude = position.coords.longitude;
    const accuracy = position.coords.accuracy;
    const altitude = position.coords.altitude;
    const speed = position.coords.speed;
    
    userLocation = {
        latitude: latitude,
        longitude: longitude,
        accuracy: accuracy
    };
    
    console.log('✅ GPS Location obtained:', {
        lat: latitude,
        lng: longitude,
        accuracy: Math.round(accuracy) + 'm',
        altitude: altitude ? Math.round(altitude) + 'm' : 'N/A',
        speed: speed ? Math.round(speed * 3.6) + 'km/h' : 'N/A',
        timestamp: new Date().toISOString()
    });
    
    // Update UI dengan info detail
    const now = new Date();
    document.getElementById('location-timestamp').innerHTML = 
        `<strong>📍 Lokasi GPS Real-Time</strong><br>` +
        `Update: ${now.toLocaleTimeString('id-ID')}<br>` +
        `Koordinat: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
    
    document.getElementById('location-accuracy').innerHTML = 
        `<strong>Akurasi: ${Math.round(accuracy)} meter</strong><br>` +
        (altitude ? `Ketinggian: ${Math.round(altitude)}m<br>` : '') +
        (speed ? `Kecepatan: ${Math.round(speed * 3.6)} km/h` : 'Diam');
    
    // Center map ke lokasi REAL user (hanya sekali saat pertama kali)
    if (!window.mapCentered) {
        map.setView([latitude, longitude], 16);
        window.mapCentered = true;
        console.log('🗺️ Map centered to your real GPS location');
    }
    
    // Share location if enabled
    if (sharingEnabled) {
        shareLocation(latitude, longitude, accuracy);
    }
    
    console.log('Location updated:', latitude, longitude, 'Accuracy:', accuracy);
}

// Handle location error
function handleLocationError(error) {
    console.error('❌ GPS Error:', error);
    let message = '';
    let instruction = '';
    
    switch(error.code) {
        case error.PERMISSION_DENIED:
            message = '❌ Izin lokasi ditolak!';
            instruction = '<br><small>Klik ikon gembok/info di address bar → Izinkan akses lokasi → Refresh halaman</small>';
            alert('⚠️ PENTING: Aplikasi memerlukan izin akses lokasi untuk berfungsi!\n\nCara mengizinkan:\n1. Klik ikon gembok/info di address bar browser\n2. Pilih "Izinkan" untuk Location/Lokasi\n3. Refresh halaman ini');
            break;
        case error.POSITION_UNAVAILABLE:
            message = '❌ Lokasi GPS tidak tersedia';
            instruction = '<br><small>Pastikan GPS aktif & Anda berada di area dengan sinyal GPS baik</small>';
            break;
        case error.TIMEOUT:
            message = '⏱️ GPS timeout - Mencoba lagi...';
            instruction = '<br><small>Pindah ke area terbuka atau dekat jendela untuk sinyal GPS lebih baik</small>';
            // Coba lagi setelah timeout
            setTimeout(startLocationTracking, 3000);
            break;
        default:
            message = '❌ Error GPS tidak diketahui';
            instruction = '<br><small>Coba refresh halaman atau gunakan browser lain</small>';
    }
    
    document.getElementById('location-timestamp').innerHTML = message + instruction;
}

// Share location to server
async function shareLocation(latitude, longitude, accuracy) {
    try {
        // Get battery level (if supported)
        let batteryLevel = null;
        if ('getBattery' in navigator) {
            const battery = await navigator.getBattery();
            batteryLevel = Math.round(battery.level * 100);
        }

        const payload = {
            latitude: latitude,
            longitude: longitude,
            accuracy: accuracy,
            battery_level: batteryLevel,
            sharing_enabled: sharingEnabled ? 1 : 0
        };
        
        console.log('Sending location:', payload);
        
        const response = await fetch('share-location.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        console.log('Share location response:', data);
        
        if (!data.success) {
            console.error('Failed to share location:', data.message);
        }
    } catch (error) {
        console.error('Error sharing location:', error);
    }
}

// Get all family members' locations
async function updateFamilyLocations() {
    try {
        const response = await fetch('get-locations.php');
        const data = await response.json();
        
        console.log('Family locations received:', data);
        
        if (data.success) {
            updateMapMarkers(data.locations);
            updateStats(data.locations);
        } else {
            console.error('Failed to get locations:', data.message);
        }
    } catch (error) {
        console.error('Error getting locations:', error);
    }
}

// Update markers on map
function updateMapMarkers(locations) {
    // Remove old markers
    Object.values(markers).forEach(marker => map.removeLayer(marker));
    markers = {};
    
    // Remove old accuracy circles
    accuracyCircles.forEach(circle => map.removeLayer(circle));
    accuracyCircles = [];
    
    // Add new markers
    locations.forEach(location => {
        const icon = L.divIcon({
            className: 'custom-marker',
            html: `
                <div style="background: ${location.is_me ? '#667eea' : '#ff6b6b'}; 
                            width: 40px; height: 40px; 
                            border-radius: 50%; 
                            border: 3px solid white;
                            display: flex; align-items: center; justify-content: center;
                            font-size: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
                    ${location.role === 'parent' ? '👨‍👩‍👧‍👦' : (location.role === 'child' ? '👶' : '👤')}
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
        
        const marker = L.marker([location.latitude, location.longitude], {icon: icon})
            .addTo(map);
        
        // Popup
        const popupContent = `
            <div style="text-align: center;">
                <strong style="font-size: 1.1em;">${location.name}</strong><br>
                <small>Role: ${location.role}</small><br>
                <small>Akurasi: ${Math.round(location.accuracy)}m</small><br>
                ${location.battery_level ? `<small>Baterai: ${location.battery_level}%</small><br>` : ''}
                <small>${formatTimestamp(location.updated_at)}</small>
            </div>
        `;
        marker.bindPopup(popupContent);
        
        markers[location.id] = marker;
        
        // Add accuracy circle
        const circle = L.circle([location.latitude, location.longitude], {
            radius: location.accuracy,
            color: location.is_me ? '#667eea' : '#ff6b6b',
            fillColor: location.is_me ? '#667eea' : '#ff6b6b',
            fillOpacity: 0.1,
            weight: 1
        }).addTo(map);
        
        // Simpan reference circle untuk dihapus nanti
        accuracyCircles.push(circle);
    });
    
    // Auto-fit bounds if multiple locations
    if (locations.length > 1) {
        const bounds = L.latLngBounds(locations.map(l => [l.latitude, l.longitude]));
        map.fitBounds(bounds, {padding: [50, 50]});
    }
}

// Update statistics
function updateStats(locations) {
    const activeLocations = locations.filter(l => l.latitude && l.longitude).length;
    document.getElementById('active-locations').textContent = activeLocations;
    
    if (locations.length > 0) {
        const latestUpdate = new Date(Math.max(...locations.map(l => new Date(l.updated_at))));
        document.getElementById('last-update').textContent = formatTimestamp(latestUpdate);
    }
}

// Format timestamp
function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = (now - date) / 1000; // seconds
    
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
    if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
    return date.toLocaleDateString('id-ID') + ' ' + date.toLocaleTimeString('id-ID');
}

// Focus on specific member
function focusOnMember(userId) {
    if (markers[userId]) {
        const marker = markers[userId];
        map.setView(marker.getLatLng(), 16);
        marker.openPopup();
    }
}

// Refresh my location manually
function refreshMyLocation() {
    console.log('🔄 Manual refresh requested');
    document.getElementById('location-timestamp').innerHTML = 
        '⏳ Mengambil lokasi GPS terbaru...<br><small>Tunggu sebentar...</small>';
    
    // Stop current tracking
    if (watchId) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }
    
    // Get fresh location
    navigator.geolocation.getCurrentPosition(
        function(position) {
            handleLocationSuccess(position);
            // Restart continuous tracking
            startLocationTracking();
            // Center map to my location
            map.setView([position.coords.latitude, position.coords.longitude], 16);
        },
        handleLocationError,
        {
            enableHighAccuracy: true,
            timeout: 30000,
            maximumAge: 0  // Force fresh location
        }
    );
}

// Center map to my current location
function centerToMyLocation() {
    if (userLocation) {
        map.setView([userLocation.latitude, userLocation.longitude], 17);
        console.log('🎯 Map centered to your GPS location');
        
        // Flash my marker
        if (markers[userId]) {
            markers[userId].openPopup();
        }
    } else {
        alert('⏳ GPS belum mendapatkan lokasi Anda. Tunggu sebentar...');
    }
}

// Toggle sharing
function toggleSharing(enabled) {
    sharingEnabled = enabled;
    console.log('Sharing toggled:', enabled ? 'Aktif' : 'Nonaktif');
    document.getElementById('sharing-status').textContent = enabled ? 'Aktif' : 'Nonaktif';
    
    if (enabled) {
        startLocationTracking();
    } else {
        if (watchId) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
        // Send disabled status to server
        if (userLocation) {
            console.log('Sending disabled status to server');
            shareLocation(userLocation.latitude, userLocation.longitude, userLocation.accuracy);
        }
    }
}

// Initialize everything
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing Family Location Tracker...');
    console.log('📱 Your browser:', navigator.userAgent);
    console.log('📍 Geolocation support:', 'geolocation' in navigator ? '✅ Yes' : '❌ No');
    
    // Show loading
    document.getElementById('location-timestamp').innerHTML = 
        '⏳ Meminta akses GPS...<br><small>Klik "Allow/Izinkan" pada popup browser</small>';
    
    // Initialize map
    initMap();
    
    // Start location tracking
    startLocationTracking();
    
    // Setup sharing toggle
    const sharingToggle = document.getElementById('sharing-toggle');
    if (sharingToggle) {
        sharingToggle.addEventListener('change', function() {
            toggleSharing(this.checked);
        });
    }
    
    // Update family locations periodically (real-time)
    updateFamilyLocations();
    setInterval(updateFamilyLocations, 3000); // Every 3 seconds untuk real-time
    
    console.log('✅ Dashboard initialized - Waiting for GPS lock...');
    console.log('💡 Tip: Gunakan di luar ruangan untuk GPS akurasi terbaik');
});
