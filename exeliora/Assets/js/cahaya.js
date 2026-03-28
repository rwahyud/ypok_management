// JavaScript untuk halaman Cahaya
document.addEventListener('DOMContentLoaded', function() {
    // Handle toggle switches
    const toggles = document.querySelectorAll('.toggle-switch input');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const card = this.closest('.light-control-card');
            const slider = card.querySelector('.brightness-slider');
            const valueDisplay = card.querySelector('.brightness-value');
            
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
    const sliders = document.querySelectorAll('.brightness-slider');
    
    sliders.forEach(slider => {
        slider.addEventListener('input', function() {
            const card = this.closest('.light-control-card');
            const valueDisplay = card.querySelector('.brightness-value');
            valueDisplay.textContent = this.value + '%';
        });
    });
});
