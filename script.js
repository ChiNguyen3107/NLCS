document.getElementById('cart-icon').addEventListener('click', function (e) {
    e.preventDefault();
    const cartDropdown = document.getElementById('cart-dropdown');
    cartDropdown.style.display = cartDropdown.style.display === 'block' ? 'none' : 'block';
});
let currentBanner = 0;
const banners = document.querySelectorAll('.banner-image');
const totalBanners = banners.length;

function showBanner(index) {
    banners.forEach((banner, i) => {
        banner.style.display = i === index ? 'block' : 'none';
    });
}

function nextBanner() {
    currentBanner = (currentBanner + 1) % totalBanners;
    showBanner(currentBanner);
}

function prevBanner() {
    currentBanner = (currentBanner - 1 + totalBanners) % totalBanners;
    showBanner(currentBanner);
}
document.getElementById('nextBtn').addEventListener('click', nextBanner);
document.getElementById('prevBtn').addEventListener('click', prevBanner);
setInterval(nextBanner, 4000);
document.getElementById('cart-icon').addEventListener('click', function () {
    const cartDropdown = document.getElementById('cart-dropdown');
    cartDropdown.style.display = cartDropdown.style.display === 'block' ? 'none' : 'block';
});
window.addEventListener('click', function (event) {
    const cartDropdown = document.getElementById('cart-dropdown');
    if (!event.target.matches('#cart-icon') && !event.target.closest('.cart-dropdown')) {
        cartDropdown.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const bannerContainer = document.querySelector('.banner-container');
    const bannerItems = document.querySelectorAll('.banner-item');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    let currentIndex = 0;

    function showBanners(index) {
        bannerContainer.style.transform = `translateX(-${index * 50}%)`;
    }

    function nextBanner() {
        currentIndex = (currentIndex + 1) % (bannerItems.length - 1);
        showBanners(currentIndex);
    }

    function prevBanner() {
        currentIndex = (currentIndex - 1 + bannerItems.length - 1) % (bannerItems.length - 1);
        showBanners(currentIndex);
    }

    nextBtn.addEventListener('click', nextBanner);
    prevBtn.addEventListener('click', prevBanner);

    showBanners(currentIndex);
});

