jQuery(document).ready(function($) {
    var lassoHubUrl = (window.lassoLiteOptionsData && window.lassoLiteOptionsData.lasso_hub_url)
        ? window.lassoLiteOptionsData.lasso_hub_url
        : 'https://app.getlasso.co';

    var LASSO_HUB_URL = (lassoHubUrl || 'https://app.getlasso.co').replace(/\/+$/, '');
    var isLoading = false;
    var lastSignupResponse = null;
    var hasShownSignupSuccess = false;
    var googleSignupPopup = null;
    var googleSignupPopupWatcher = null;
    var lastSignupWrapper = null;

    function clearGoogleSignupPopupReference() {
        if (googleSignupPopupWatcher) {
            window.clearInterval(googleSignupPopupWatcher);
            googleSignupPopupWatcher = null;
        }
        googleSignupPopup = null;
    }

    function startGoogleSignupPopupWatcher() {
        if (googleSignupPopupWatcher) {
            window.clearInterval(googleSignupPopupWatcher);
        }

        googleSignupPopupWatcher = window.setInterval(function() {
            if (!googleSignupPopup || googleSignupPopup.closed) {
                clearGoogleSignupPopupReference();
            }
        }, 1000);
    }

    function getOrigin(url) {
        try {
            var a = document.createElement('a');
            a.href = url;
            return a.protocol + '//' + a.host;
        } catch (e) {
            return '';
        }
    }

    var EXPECTED_MESSAGE_ORIGIN = getOrigin(LASSO_HUB_URL);

    // Listen for completion message from the hub callback page (popup).
    window.addEventListener('message', function(event) {
        try {
            if (!EXPECTED_MESSAGE_ORIGIN || event.origin !== EXPECTED_MESSAGE_ORIGIN) {
                return;
            }

            var data = event.data || {};
            if (data.type === 'lasso:external_oauth_complete' && data.success && data.exchange_code) {
                exchangeAndComplete(data.exchange_code);
                if (googleSignupPopup && !googleSignupPopup.closed) {
                    googleSignupPopup.close();
                }
                clearGoogleSignupPopupReference();
            }
        } catch (e) {
            // ignore
        }
    });

    // Fallback: if hub redirected back to this page with ?lasso_exchange_code=...
    (function handleExchangeCodeFromUrl() {
        try {
            var url = new URL(window.location.href);
            var code = url.searchParams.get('lasso_exchange_code');
            if (!code) return;

            // remove param from URL (avoid re-processing on refresh)
            url.searchParams.delete('lasso_exchange_code');
            window.history.replaceState({}, document.title, url.toString());

            exchangeAndComplete(code);
        } catch (e) {
            // ignore
        }
    })();

    $(document)
        .on('click', '#btn-google-signup', handleGoogleSignup)
        .on('click', '#btn-email-signup-toggle', handleEmailFormToggle)
        .on('click', '#btn-create-account', handleEmailSignup)
        .on('click', '#lasso-toggle-password', togglePasswordVisibility)
        .on('click', '#lasso-skip-signup', handleSkipSignup)
        .on('blur', '#lasso-signup-email', validateEmail)
        .on('blur', '#lasso-signup-password', validatePassword)
        .on('input', '#lasso-signup-email', function() {
            var $input = $(this);
            var $wrapper = $input.closest('#lasso-signup-wrapper');
            clearEmailError($input, $wrapper.find('#lasso-email-error'), $wrapper.find('#lasso-general-error'));
        })
        .on('input', '#lasso-signup-password', function() {
            var $input = $(this);
            var $wrapper = $input.closest('#lasso-signup-wrapper');
            clearPasswordError($input, $wrapper.find('#lasso-password-error'), $wrapper.find('#lasso-general-error'));
        })
        .on('keyup', '#lasso-signup-password', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                handleEmailSignup(e);
            }
        });

    function handleGoogleSignup(e) {
        e.preventDefault();
        lastSignupWrapper = $(e.currentTarget).closest('#lasso-signup-wrapper');

        $.ajax({
            url: lassoLiteOptionsData.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'lasso_lite_get_external_signup_config',
                nonce: lassoLiteOptionsData.optionsNonce,
                source: 'lasso-lite',
                callback_url: window.location.href
            },
            success: function(response) {
                if (response && response.success && response.data && response.data.google_auth_url) {
                    // Use a popup so the onboarding page remains in place.
                    googleSignupPopup = window.open(
                        response.data.google_auth_url,
                        'lasso_google_signup',
                        'width=520,height=720,resizable=yes,scrollbars=yes'
                    );
                    if (googleSignupPopup) {
                        googleSignupPopup.focus();
                        startGoogleSignupPopupWatcher();
                    }
                } else {
                    showGeneralError('Unable to initiate Google signup. Please try again.');
                }
            },
            error: function() {
                showGeneralError('Unable to connect to Lasso. Please try again later.');
            }
        });
    }

    function handleEmailFormToggle(e) {
        e.preventDefault();
        var $toggleBtn = $(e.currentTarget);
        var $wrapper = $toggleBtn.closest('#lasso-signup-wrapper');
        var $form = $wrapper.length ? $wrapper.find('#lasso-email-signup-form') : $('#lasso-email-signup-form');
        $toggleBtn.addClass('d-none');
        $form.removeClass('d-none');
    }

    function handleEmailSignup(e) {
        e.preventDefault();

        if (isLoading) return;

        var $btn = $(e.currentTarget);
        var $wrapper = $btn.closest('#lasso-signup-wrapper');
        lastSignupWrapper = $wrapper;
        var $email = $wrapper.length ? $wrapper.find('#lasso-signup-email') : $('#lasso-signup-email');
        var $password = $wrapper.length ? $wrapper.find('#lasso-signup-password') : $('#lasso-signup-password');
        var $emailError = $wrapper.length ? $wrapper.find('#lasso-email-error') : $('#lasso-email-error');
        var $passwordError = $wrapper.length ? $wrapper.find('#lasso-password-error') : $('#lasso-password-error');
        var $generalError = $wrapper.length ? $wrapper.find('#lasso-general-error') : $('#lasso-general-error');

        clearAllErrors($email, $emailError, $password, $passwordError, $generalError);
        var isValid = true;

        if (!validateEmail($email, $emailError)) isValid = false;
        if (!validatePassword($password, $passwordError)) isValid = false;

        if (!isValid) return;

        var email = $email.val().trim();
        var password = $password.val();

        isLoading = true;
        var originalText = $btn.text();
        $btn.html('<span class="spinner-border spinner-border-sm mr-2"></span>Creating your account...');
        $btn.prop('disabled', true);

        $.ajax({
            url: lassoLiteOptionsData.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'lasso_lite_external_signup',
                nonce: lassoLiteOptionsData.optionsNonce,
                email: email,
                password: password,
                source: 'lasso-lite'
            },
            success: function(response) {
                isLoading = false;
                $btn.text(originalText);
                $btn.prop('disabled', false);

                var signupData = (response && response.success && response.data) ? response.data : null;
                if (signupData && (signupData.success || signupData.user_id || signupData.api_key)) {
                    lastSignupResponse = signupData;
                    saveAccountCredentials(signupData);
                    showSuccessAndContinue(signupData, $wrapper);
                } else {
                    var errorMsg = (response && response.data && response.data.error) ? response.data.error : 'Signup failed. Please try again.';
                    showGeneralError(errorMsg);
                }
            },
            error: function(xhr) {
                isLoading = false;
                $btn.text(originalText);
                $btn.prop('disabled', false);

                var errorMsg = 'Signup failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.error) {
                    errorMsg = xhr.responseJSON.data.error;
                }
                showGeneralError(errorMsg);
            }
        });
    }

    function saveAccountCredentials(response) {
        $.ajax({
            url: lassoLiteOptionsData.ajax_url,
            type: 'POST',
            data: {
                action: 'lasso_lite_save_lasso_account',
                nonce: lassoLiteOptionsData.optionsNonce,
                email: response.email,
                api_key: response.api_key,
                user_id: response.user_id
            }
        }).done(function(res) {
            if (res && res.success && res.data && res.data.success) {
                showSuccessAndContinue(lastSignupResponse || {}, lastSignupWrapper);
            }
        });
    }

    function showSuccessAndContinue(response, $contextWrapper) {
        var $wrapper = ($contextWrapper && $contextWrapper.length)
            ? $contextWrapper
            : (lastSignupWrapper && lastSignupWrapper.length ? lastSignupWrapper : null);
        if (!$wrapper || !$wrapper.length) {
            $wrapper = $('#lasso-lite-analytics-modal #lasso-signup-wrapper:visible').first();
        }
        if (!$wrapper.length) {
            $wrapper = $('#lasso-signup-wrapper:visible').first();
        }
        if (!$wrapper.length) {
            $wrapper = $('.lasso-footer-signup-wrapper:visible').first();
        }
        if (!$wrapper.length) {
            return;
        }
        if (hasShownSignupSuccess) {
            return;
        }

        hasShownSignupSuccess = true;
        var isFooterCta = $wrapper.closest('.lasso-footer-cta-inner').length > 0;
        var isOnboarding = !!(window.lassoLiteOptionsData && window.lassoLiteOptionsData.is_onboard_page);
        var messageClass = isFooterCta ? 'text-white' : 'text-muted';
        var successMessage = 'Your Lasso account has been created successfully.'

        $wrapper.html(
            '<div class="lasso-signup-success">' +
                '<div class="lasso-success-icon mb-3">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#22BAA0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                        '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>' +
                        '<polyline points="22 4 12 14.01 9 11.01"></polyline>' +
                    '</svg>' +
                '</div>' +
                '<h3 class="mb-2">Account Created!</h3>' +
                '<p class="' + messageClass + ' mb-4">' + successMessage + '</p>' +
                (isOnboarding ? '' : '<a id="btn-login-after-signup" class="lasso-signup-btn lasso-login-btn w-100" href="' + LASSO_HUB_URL + '/login" target="_blank" rel="noopener noreferrer">Log in</a>') +
            '</div>'
        );

        if (isOnboarding) {
            $('#lasso-signup-success').removeClass('d-none');
        }
    }

    function exchangeAndComplete(exchangeCode) {
        if (!exchangeCode) return;

        clearAllErrors();

        $.ajax({
            url: lassoLiteOptionsData.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'lasso_lite_external_signup_exchange',
                nonce: lassoLiteOptionsData.optionsNonce,
                exchange_code: exchangeCode
            },
            success: function(response) {
                var signupData = (response && response.success && response.data) ? response.data : null;
                if (signupData && (signupData.success || signupData.user_id || signupData.api_key)) {
                    lastSignupResponse = signupData;
                    saveAccountCredentials(signupData);
                    showSuccessAndContinue(signupData, lastSignupWrapper);
                } else {
                    var errorMsg = (response && response.data && response.data.error)
                        ? response.data.error
                        : 'Signup completed, but we could not connect your account. Please try again.';
                    showGeneralError(errorMsg);
                }
            },
            error: function(xhr) {
                var errorMsg = 'Signup completed, but we could not connect your account. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.error) {
                    errorMsg = xhr.responseJSON.data.error;
                }
                showGeneralError(errorMsg);
            }
        });
    }

    function handleSkipSignup(e) {
        e.preventDefault();
        go_to_next_step_action($('#lasso-skip-signup'));
    }

    function togglePasswordVisibility() {
        var $input = $('#lasso-signup-password');
        var $eyeIcon = $('#lasso-eye-icon');
        var $eyeOffIcon = $('#lasso-eye-off-icon');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $eyeIcon.addClass('d-none');
            $eyeOffIcon.removeClass('d-none');
        } else {
            $input.attr('type', 'password');
            $eyeIcon.removeClass('d-none');
            $eyeOffIcon.addClass('d-none');
        }
    }

    function validateEmail($input, $error) {
        $input = $input && $input.length ? $input : $('#lasso-signup-email');
        $error = $error && $error.length ? $error : $('#lasso-email-error');
        var email = $input.val().trim();

        if (!email) {
            showFieldError($input, $error, 'Please provide an email');
            return false;
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showFieldError($input, $error, 'Please provide a valid email');
            return false;
        }

        clearFieldError($input, $error);
        return true;
    }

    function validatePassword($input, $error) {
        $input = $input && $input.length ? $input : $('#lasso-signup-password');
        $error = $error && $error.length ? $error : $('#lasso-password-error');
        var password = $input.val();

        if (!password) {
            showFieldError($input, $error, 'Please provide a password');
            return false;
        }

        if (password.length < 8) {
            showFieldError($input, $error, 'Password must be at least 8 characters');
            return false;
        }

        clearFieldError($input, $error);
        return true;
    }

    function showFieldError($input, $error, message) {
        $input.addClass('invalid-field');
        $error.text(message).removeClass('d-none');
    }

    function clearFieldError($input, $error) {
        $input.removeClass('invalid-field');
        $error.addClass('d-none').text('');
    }

    function clearEmailError($input, $error, $generalError) {
        clearFieldError($input || $('#lasso-signup-email'), $error || $('#lasso-email-error'));
        hideGeneralError($generalError);
    }

    function clearPasswordError($input, $error, $generalError) {
        clearFieldError($input || $('#lasso-signup-password'), $error || $('#lasso-password-error'));
        hideGeneralError($generalError);
    }

    function clearAllErrors($emailInput, $emailError, $passwordInput, $passwordError, $generalError) {
        clearEmailError($emailInput, $emailError, $generalError);
        clearPasswordError($passwordInput, $passwordError, $generalError);
        hideGeneralError($generalError);
    }

    function showGeneralError(message, $generalError) {
        var $error = $generalError && $generalError.length ? $generalError : $('#lasso-general-error');
        $error.text(message).removeClass('d-none');
    }

    function hideGeneralError($generalError) {
        var $error = $generalError && $generalError.length ? $generalError : $('#lasso-general-error');
        $error.addClass('d-none').text('');
    }
});
