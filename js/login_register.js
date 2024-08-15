document.addEventListener('DOMContentLoaded', function() {
    function toggleGoogleForm() {
        var form = document.getElementById('google-form');
        var container = form.closest('.form-container');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            container.style.marginTop = '20px'; // フォームが表示されたときに追加の余白を確保
        } else {
            form.style.display = 'none';
            container.style.marginTop = '0'; // フォームが隠れたときに余白をリセット
        }
    }

    function toggleResetForm() {
        var form = document.getElementById('reset-form');
        var container = form.closest('.form-container');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            container.style.marginTop = '20px'; // フォームが表示されたときに追加の余白を確保
        } else {
            form.style.display = 'none';
            container.style.marginTop = '0'; // フォームが隠れたときに余白をリセット
        }
    }

    grecaptcha.ready(function () {
        grecaptcha.execute('6LdkEAgqAAAAACCp40lacU6HPdSeiK38k5nrqc6g', {action: 'homepage'}).then(function (token) {
            var loginRecaptchaResponse = document.getElementById('login-g-recaptcha-response');
            if (loginRecaptchaResponse) {
                loginRecaptchaResponse.value = token;
            }
            var registerRecaptchaResponse = document.getElementById('register-g-recaptcha-response');
            if (registerRecaptchaResponse) {
                registerRecaptchaResponse.value = token;
            }
            var googleRecaptchaResponse = document.getElementById('google-g-recaptcha-response');
            if (googleRecaptchaResponse) {
                googleRecaptchaResponse.value = token;
            }
            var resetRecaptchaResponse = document.getElementById('reset-g-recaptcha-response');
            if (resetRecaptchaResponse) {
                resetRecaptchaResponse.value = token;
            }
        });
    });

    document.getElementById('contactButton').addEventListener('click', function() {
        var contactForm = document.getElementById('contactForm');
        contactForm.style.display = contactForm.style.display === 'block' ? 'none' : 'block';
    });

    document.getElementById('hamburger').addEventListener('click', function() {
        var menu = document.getElementById('menu');
        menu.classList.toggle('open');
    });

    // Expose functions to global scope
    window.toggleGoogleForm = toggleGoogleForm;
    window.toggleResetForm = toggleResetForm;
});
