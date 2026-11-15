document.addEventListener('DOMContentLoaded', function() {
    const btnRequest = document.getElementById('btn-request-otp');
    const btnVerify = document.getElementById('btn-verify-otp');
    const csrfToken = document.getElementById('csrf_token').name;
    const ajaxUrl = document.getElementById('ajax_url').value;

    // مرحله 1: درخواست کد OTP
    btnRequest.addEventListener('click', function() {
        const mobile = document.getElementById('mobile_number').value;
        if (mobile.length < 10) {
            alert('لطفا شماره موبایل معتبر وارد کنید');
            return;
        }

        const formData = new FormData();
        formData.append('mobile', mobile);
        formData.append(csrfToken, '1');

        fetch(ajaxUrl + 'requestOtp', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('step-phone').style.display = 'none';
                document.getElementById('step-otp').style.display = 'block';
                // نمایش کد به صورت تستی
                document.getElementById('mock-otp-display').innerText = 'کد تستی شما: ' + data.data.mock_code;
            } else {
                alert(data.message);
            }
        });
    });

    // مرحله 2: تایید کد OTP
    btnVerify.addEventListener('click', function() {
        const otp = document.getElementById('otp_code').value;
        if (otp.length !== 4) {
            alert('کد باید 4 رقمی باشد');
            return;
        }

        const formData = new FormData();
        formData.append('otp', otp);
        formData.append(csrfToken, '1');

        fetch(ajaxUrl + 'verifyOtp', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // اگر لاگین موفق بود، رفرش می‌کنیم تا پلاگینی که در مراحل قبل نوشتیم کاربر را ریدایرکت کند
                window.location.href = data.data.redirect;
            } else {
                alert(data.message);
            }
        });
    });
});