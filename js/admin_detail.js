document.addEventListener('DOMContentLoaded', function() {
    const deleteLinks = document.querySelectorAll('.delete-link');

    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // 通常のリンク動作をキャンセル
            const pfmcId = this.getAttribute('data-pfmc-id'); // パフォーマンスIDを取得
            const confirmed = confirm('本当にこのトレーニング履歴を削除しますか？');
            if (confirmed) {
                // 確認後、POST リクエストで削除を実行
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `admin_detail.php?id=${userId}`;  // userIdを展開

                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'delete';
                form.appendChild(inputAction);

                const inputPfmcId = document.createElement('input');
                inputPfmcId.type = 'hidden';
                inputPfmcId.name = 'pfmc_id';
                inputPfmcId.value = pfmcId;
                form.appendChild(inputPfmcId);

                document.body.appendChild(form);

                // フォーム送信の確認ログ
                form.addEventListener('submit', function(e) {
                    console.log('フォーム送信が行われました');
                });

                form.submit();  // フォームを送信
            }
        });
    });

    document.getElementById('hamburger').addEventListener('click', function() {
        var menu = document.getElementById('menu');
        menu.classList.toggle('open');
    });
});
