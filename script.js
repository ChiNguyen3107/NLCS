document.addEventListener('DOMContentLoaded', function () {
    const cartIcon = document.getElementById('cart-icon');
    const cartDropdown = document.getElementById('cart-dropdown');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');

    // Kiểm tra phần tử trước khi thêm sự kiện
    if (cartIcon && cartDropdown) {
        cartIcon.addEventListener('click', function (e) {
            e.preventDefault();
            cartDropdown.style.display = cartDropdown.style.display === 'block' ? 'none' : 'block';
        });
    } else {
        console.error('Không tìm thấy cart-icon hoặc cart-dropdown');
    }

    if (nextBtn && prevBtn) {
        nextBtn.addEventListener('click', nextBanner);
        prevBtn.addEventListener('click', prevBanner);
    } else {
        console.error('Không tìm thấy nextBtn hoặc prevBtn');
    }

    // Đoạn mã điều khiển banner
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

    showBanner(currentBanner);
    setInterval(nextBanner, 4000);
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



function updateTotal() {
    let total = 0;
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const price = parseFloat(row.querySelector('.price').textContent.replace(/\./g, "").replace(" VND", ""));
        const quantity = parseInt(row.querySelector('.quantity').value);
        const itemTotal = price * quantity;
        row.querySelector('.item-total').textContent = itemTotal.toLocaleString('vi-VN') + ' VND';
        total += itemTotal;
    });
    document.getElementById('total-price').textContent = total.toLocaleString('vi-VN') + ' VND';
}


