document.addEventListener('DOMContentLoaded', function() {
    // メニューのハンバーガーアイコンをクリックした時の動作
    document.getElementById('hamburger').addEventListener('click', function() {
        var menu = document.getElementById('menu');
        menu.classList.toggle('open');
    });
});