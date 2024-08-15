document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;

    function showSlide(index) {
        slides[currentSlide].style.opacity = 0; // 現在のスライドを非表示にする（フェードアウト）
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].style.opacity = 1; // 次のスライドを表示する（フェードイン）
    }

    // 初期化: 最初のスライドを表示
    slides.forEach((slide, index) => {
        slide.style.display = 'block'; // 全てのスライドをブロック表示に設定
        slide.style.opacity = index === 0 ? 1 : 0; // 最初のスライドのみ表示、他は非表示
    });

    setInterval(function() {
        showSlide(currentSlide + 1);
    }, 5000); // 5秒ごとにスライドを切り替え

    const popup = document.getElementById('statsPopup');
    const closePopupButton = document.getElementById('closePopup');

    // ポップアップを表示
    popup.style.display = 'block';

    // 10秒後にポップアップを自動的に閉じる
    setTimeout(function() {
        popup.style.display = 'none';
    }, 10000);

    // ポップアップを手動で閉じる
    closePopupButton.addEventListener('click', function() {
        popup.style.display = 'none';
    });

    const contactButton = document.getElementById('contactButton');
    const contactForm = document.getElementById('contactForm');

    contactButton.addEventListener('click', function() {
        contactForm.style.display = contactForm.style.display === 'block' ? 'none' : 'block';
    });

    const hamburger = document.getElementById('hamburger');
    const menu = document.getElementById('menu');

    hamburger.addEventListener('click', function() {
        menu.classList.toggle('open');
    });
});
