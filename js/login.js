// Rotación de imágenes de fondo
(function() {
    var slides = document.querySelectorAll('.bg-slide');
    var currentSlide = 0;
    var slideInterval = 3000; // 3 segundos

    function nextSlide() {
        // Remover active del actual
        slides[currentSlide].classList.remove('active');
        
        // Siguiente slide
        currentSlide = (currentSlide + 1) % slides.length;
        
        // Agregar active al siguiente
        slides[currentSlide].classList.add('active');
    }

    // Iniciar rotación
    setInterval(nextSlide, slideInterval);

    // Precargar imágenes para evitar parpadeos
    slides.forEach(function(slide) {
        var img = new Image();
        img.src = slide.style.backgroundImage.slice(5, -2);
    });
})();

// Animación del formulario al cargar
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form');
    if (form) {
        form.style.opacity = '0';
        setTimeout(function() {
            form.style.transition = 'opacity 0.5s ease';
            form.style.opacity = '1';
        }, 300);
    }
});