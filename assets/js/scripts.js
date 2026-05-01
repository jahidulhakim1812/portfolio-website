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
// Counter animation
function animateCounters() {
    const counters = document.querySelectorAll('.counter-num');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        let current = 0;
        const increment = target / 50;
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.innerText = Math.ceil(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target;
            }
        };
        updateCounter();
    });
}

// Bangladesh map using Leaflet + GeoJSON (simplified districts)
// We'll embed a sample GeoJSON for 8 divisions (you can replace with district-level)
const bangladeshGeoJSON = {
  "type": "FeatureCollection",
  "features": [
    {"type":"Feature","properties":{"name":"Dhaka"},"geometry":{"type":"Polygon","coordinates":[[[90.0,24.0],[90.5,24.0],[90.5,23.5],[90.0,23.5],[90.0,24.0]]]}},
    {"type":"Feature","properties":{"name":"Chattogram"},"geometry":{"type":"Polygon","coordinates":[[[91.5,22.5],[92.0,22.5],[92.0,22.0],[91.5,22.0],[91.5,22.5]]]}},
    {"type":"Feature","properties":{"name":"Rajshahi"},"geometry":{"type":"Polygon","coordinates":[[[88.5,24.8],[89.0,24.8],[89.0,24.3],[88.5,24.3],[88.5,24.8]]]}},
    {"type":"Feature","properties":{"name":"Khulna"},"geometry":{"type":"Polygon","coordinates":[[[89.0,22.8],[89.5,22.8],[89.5,22.3],[89.0,22.3],[89.0,22.8]]]}},
    {"type":"Feature","properties":{"name":"Sylhet"},"geometry":{"type":"Polygon","coordinates":[[[91.8,25.0],[92.3,25.0],[92.3,24.5],[91.8,24.5],[91.8,25.0]]]}},
    {"type":"Feature","properties":{"name":"Barishal"},"geometry":{"type":"Polygon","coordinates":[[[90.2,22.5],[90.7,22.5],[90.7,22.0],[90.2,22.0],[90.2,22.5]]]}},
    {"type":"Feature","properties":{"name":"Rangpur"},"geometry":{"type":"Polygon","coordinates":[[[88.8,26.0],[89.3,26.0],[89.3,25.5],[88.8,25.5],[88.8,26.0]]]}},
    {"type":"Feature","properties":{"name":"Mymensingh"},"geometry":{"type":"Polygon","coordinates":[[[90.2,24.8],[90.7,24.8],[90.7,24.3],[90.2,24.3],[90.2,24.8]]]}}
  ]
};

// Fetch active districts from PHP via AJAX or embed inline
let activeDistricts = <?php 
    $stmt = $pdo->query("SELECT DISTINCT district FROM customers WHERE country='Bangladesh' AND district IS NOT NULL");
    $active = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($active);
?>;

// Initialize map
function initBangladeshMap() {
    const map = L.map('bangladeshMap').setView([23.685, 90.356], 7);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors'
    }).addTo(map);

    function getColor(districtName) {
        return activeDistricts.includes(districtName) ? '#2ecc71' : '#d3d3d3';
    }

    function style(feature) {
        return {
            fillColor: getColor(feature.properties.name),
            weight: 1,
            opacity: 1,
            color: 'white',
            fillOpacity: 0.7
        };
    }

    L.geoJSON(bangladeshGeoJSON, { style: style, onEachFeature: function(feature, layer) {
        layer.bindPopup(`<b>${feature.properties.name}</b><br>${activeDistricts.includes(feature.properties.name) ? '✅ AR Service Active' : '❌ No Service Yet'}`);
    }}).addTo(map);
}

document.addEventListener('DOMContentLoaded', function() {
    animateCounters();
    if (typeof L !== 'undefined') {
        initBangladeshMap();
    } else {
        console.warn("Leaflet not loaded");
    }
});