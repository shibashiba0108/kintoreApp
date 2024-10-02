document.addEventListener('DOMContentLoaded', function() {
    const deleteLinks = document.querySelectorAll('.delete-link');

    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // 通常のリンク動作をキャンセル
            const userId = this.getAttribute('data-user-id');
            const confirmed = confirm('本当にこのユーザーを削除しますか？');
            if (confirmed) {
                // 確認後、POST リクエストで削除を実行
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin.php';

                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'delete';
                form.appendChild(inputAction);

                const inputUserId = document.createElement('input');
                inputUserId.type = 'hidden';
                inputUserId.name = 'user_id';
                inputUserId.value = userId;
                form.appendChild(inputUserId);

                document.body.appendChild(form);
                form.submit();  // フォームを送信
            }
        });
    });

    document.getElementById('hamburger').addEventListener('click', function() {
        var menu = document.getElementById('menu');
        menu.classList.toggle('open');
    });
});
