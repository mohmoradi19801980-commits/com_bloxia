document.addEventListener('DOMContentLoaded', function() {
    const btnRequest = document.getElementById('btn-request-otp');
    const btnVerify = document.getElementById('btn-verify-otp');
    const btnBack = document.getElementById('btn-back-phone');
    const mobileInput = document.getElementById('mobile_number');
    const otpInput = document.getElementById('otp_code');
    const mockOtpBox = document.getElementById('mock-otp-display');
    const csrfToken = document.getElementById('csrf_token').name;
    const ajaxUrl = document.getElementById('ajax_url').value;

    // مرحله 1: درخواست کد OTP
    function handleRequestOtp() {
        const mobile = mobileInput.value.trim();
        if (mobile.length < 10) {
            alert('لطفاً شماره موبایل معتبر وارد کنید');
            mobileInput.focus();
            return;
        }

        btnRequest.disabled = true;
        const origText = btnRequest.textContent;
        btnRequest.textContent = '⏳ در حال ارسال...';

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
                if (data.data && data.data.mock_code) {
                    mockOtpBox.style.display = 'block';
                    mockOtpBox.innerText = 'کد تستی شما: ' + data.data.mock_code;
                }
                otpInput.focus();
            } else {
                alert(data.message || 'خطا در ارسال کد تایید');
            }
        })
        .catch(() => alert('خطا در ارتباط با سرور'))
        .finally(() => {
            btnRequest.disabled = false;
            btnRequest.textContent = origText;
        });
    }

    // مرحله 2: تایید کد OTP
    function handleVerifyOtp() {
        const otp = otpInput.value.trim();
        if (otp.length !== 4) {
            alert('کد باید ۴ رقمی باشد');
            otpInput.focus();
            return;
        }

        btnVerify.disabled = true;
        const origText = btnVerify.textContent;
        btnVerify.textContent = '⏳ در حال ورود...';

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
                window.location.href = data.data.redirect;
            } else {
                alert(data.message || 'کد تایید اشتباه است');
            }
        })
        .catch(() => alert('خطا در ارتباط با سرور'))
        .finally(() => {
            btnVerify.disabled = false;
            btnVerify.textContent = origText;
        });
    }

    btnRequest.addEventListener('click', handleRequestOtp);
    btnVerify.addEventListener('click', handleVerifyOtp);

    if (btnBack) {
        btnBack.addEventListener('click', function() {
            document.getElementById('step-otp').style.display = 'none';
            document.getElementById('step-phone').style.display = 'block';
            mobileInput.focus();
        });
    }

    mobileInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') handleRequestOtp(); });
    otpInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') handleVerifyOtp(); });
});
