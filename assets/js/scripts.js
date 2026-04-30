// assets/js/scripts.js - JavaScript for Swiper & Interactions
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Swiper fullscreen slider
    const swiper = new Swiper('.heroSwiper', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
    
    // Add scroll effect to navbar (optional)
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.main-navbar');
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(10, 11, 16, 0.98)';
            navbar.style.backdropFilter = 'blur(5px)';
        } else {
            navbar.style.background = 'rgba(10, 11, 16, 0.85)';
        }
    });
});