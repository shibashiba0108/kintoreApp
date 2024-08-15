document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const menu = document.getElementById('menu');
    const contactButton = document.getElementById('contactButton');
    const contactForm = document.getElementById('contactForm');
    const toggleButtons = document.querySelectorAll('.toggle-details');

    hamburger.addEventListener('click', function() {
        menu.classList.toggle('open');
    });

    contactButton.addEventListener('click', function() {
        contactForm.style.display = contactForm.style.display === 'block' ? 'none' : 'block';
    });

    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-target'));
            if (target.style.display === 'none') {
                target.style.display = 'block';
                this.textContent = '閉じる';
            } else {
                target.style.display = 'none';
                this.textContent = '詳細を見る';
            }
        });
    });

    document.querySelectorAll('.delete-button').forEach(function(button) {
        button.addEventListener('click', function() {
            var performanceId = this.getAttribute('data-id');
            if (confirm('このトレーニングを削除しますか？')) {
                fetch('performance.php?id=' + performanceId, {
                    method: 'GET'
                })
                .then(response => {
                    if (response.ok) {
                        document.getElementById('performance-' + performanceId).remove();
                    } else {
                        alert('削除に失敗しました');
                    }
                });
            }
        });
    });
});

