document.addEventListener('DOMContentLoaded', function() {
    var selectedGoal = document.getElementById('goal').value;
    showGoalDetails(selectedGoal);

    var alertMessage = document.getElementById('alert-message').value;
    if (alertMessage) {
        alert(alertMessage);
        if (alertMessage === 'フィットネスゴールが設定されました。') {
            window.location.href = '/DT/kintore/contents/mypage.php';
        }
    }

    document.getElementById('goal').addEventListener('change', function() {
        var goal = this.value;
        showGoalDetails(goal);
    });
});

function showGoalDetails(goal) {
    document.querySelectorAll('.goal-details').forEach(function(element) {
        element.style.display = 'none';
    });
    if (goal == '1') { // 減量
        document.getElementById('weight-loss-details').style.display = 'block';
    } else if (goal == '2') { // 筋肥大
        document.getElementById('muscle-gain-details').style.display = 'block';
    } else if (goal == '3') { // 筋力向上
        document.getElementById('strength-details').style.display = 'block';
    }

    hamburger.addEventListener('click', function() {
        menu.classList.toggle('open');
    });
}
