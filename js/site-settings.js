/**
 * Anjuman Eraquee INDIA - Dynamic Site Settings & Join Membership Dialog
 * 1. Fetches site settings dynamically from api/settings.php (or data/settings.json).
 * 2. Injects and handles the responsive Join Membership Sign-Up Dialog for laptop and mobile devices.
 * 3. Provides eye toggle functionality for passwords.
 */
(function () {
    function cleanPhone(phone) {
        if (!phone) return '';
        return phone.replace(/[^0-9+]/g, '');
    }

    function applySettings(data) {
        if (!data) return;

        // 1. Update Topbar Phone Numbers (.header-event .count-list)
        var countLists = document.querySelectorAll('.header-event .count-list, .topbar .count-list');
        countLists.forEach(function (list) {
            var items = list.querySelectorAll('li');
            var phones = [data.topbar_phone_1, data.topbar_phone_2, data.topbar_phone_3];

            items.forEach(function (li, idx) {
                if (phones[idx]) {
                    var a = li.querySelector('a');
                    if (a) {
                        a.setAttribute('href', 'tel:' + cleanPhone(phones[idx]));
                        a.innerHTML = '<i class="fa fa-mobile"></i> ' + phones[idx];
                    }
                }
            });
        });

        // 2. Update Footer Phone Numbers (.about-foo ul li strong)
        var footerPhoneList = document.querySelector('.about-foo ul');
        if (footerPhoneList) {
            var footerPhones = footerPhoneList.querySelectorAll('li strong');
            var phones = [data.topbar_phone_1, data.topbar_phone_2, data.topbar_phone_3];
            
            footerPhones.forEach(function (strong, idx) {
                if (phones[idx] && idx < 3) {
                    strong.textContent = phones[idx];
                }
            });
        }

        // 3. Update Header Navigation Links
        var loginLinks = document.querySelectorAll('.header-social a, .mobile-menu a, .mean-nav a');
        loginLinks.forEach(function (link) {
            var text = link.textContent.trim();
            if (text === 'Login' || text === 'Login |' || text === 'Admin Login' || text === 'ADMIN LOGIN') {
                link.setAttribute('href', 'admin/login.php');
            }
            if (text === 'User Login' || text === 'User Login |' || text === 'USER LOGIN') {
                link.setAttribute('href', 'user-login.php');
            }
            if (text === 'Join Membership' || text === 'Join Membership |' || text === 'JOIN MEMBERSHIP') {
                link.setAttribute('href', 'registration.php');
                link.setAttribute('data-action', 'open-join-modal');
            }
        });

        // 4. Update Banner / Convenor Phone (if present)
        var bannerPhone = document.querySelector('.contact-banner .content h1');
        if (bannerPhone && data.convenor_phone) {
            bannerPhone.textContent = data.convenor_phone;
        }

        var bannerConvenor = document.querySelector('.contact-banner .content .mail');
        if (bannerConvenor && data.convenor_name) {
            bannerConvenor.textContent = data.convenor_name;
        }

        // 5. Update WhatsApp links if present
        var waLinks = document.querySelectorAll('a[href*="whatsapp"], a[href*="wa.me"]');
        if (data.whatsapp_number) {
            waLinks.forEach(function (wa) {
                wa.setAttribute('href', 'https://wa.me/' + cleanPhone(data.whatsapp_number));
            });
        }
    }

    function fetchSettings() {
        if (window.location.pathname.indexOf('/admin/') !== -1) {
            return;
        }

        var apiPath = 'api/settings.php';
        var fallbackPath = 'data/settings.json';

        fetch(apiPath)
            .then(function (res) {
                if (!res.ok) throw new Error('API not ok');
                return res.json();
            })
            .then(function (data) {
                applySettings(data);
            })
            .catch(function () {
                fetch(fallbackPath)
                    .then(function (res) { return res.json(); })
                    .then(function (data) { applySettings(data); })
                    .catch(function (err) {
                        console.warn('Site settings could not be fetched dynamically:', err);
                    });
            });
    }

    /**
     * Join Membership Modal Setup (Desktop & Mobile)
     */
    function setupJoinMembershipModal() {
        // Skip injecting modal if already on registration.php or inside admin folder
        var path = window.location.pathname.toLowerCase();
        if (path.indexOf('registration.php') !== -1 || path.indexOf('/admin/') !== -1) {
            return;
        }

        // Inject modal CSS
        if (!document.getElementById('ae_modal_styles')) {
            var style = document.createElement('style');
            style.id = 'ae_modal_styles';
            style.textContent = `
                .ae-modal-backdrop {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;
                    background: rgba(15, 23, 42, 0.65);
                    backdrop-filter: blur(3px);
                    z-index: 999999;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    padding: 15px;
                    opacity: 0;
                    transition: opacity 0.25s ease;
                }
                .ae-modal-backdrop.show {
                    display: flex;
                    opacity: 1;
                }
                .ae-modal-dialog {
                    background: #ffffff;
                    border-radius: 12px;
                    max-width: 480px;
                    width: 100%;
                    max-height: 90vh;
                    overflow-y: auto;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
                    border-top: 5px solid #009146;
                    padding: 30px 25px;
                    position: relative;
                    animation: aeSlideDown 0.3s ease-out;
                }
                @keyframes aeSlideDown {
                    from { transform: translateY(-20px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                .ae-modal-close {
                    position: absolute;
                    top: 15px;
                    right: 18px;
                    font-size: 24px;
                    background: none;
                    border: none;
                    color: #94a3b8;
                    cursor: pointer;
                    line-height: 1;
                    padding: 4px 8px;
                    border-radius: 4px;
                }
                .ae-modal-close:hover {
                    color: #0f172a;
                    background: #f1f5f9;
                }
                .ae-modal-header {
                    text-align: center;
                    margin-bottom: 22px;
                }
                .ae-modal-header img {
                    max-height: 48px;
                    margin-bottom: 10px;
                }
                .ae-modal-header h3 {
                    font-size: 20px;
                    font-weight: 700;
                    color: #1e293b;
                    margin: 0 0 4px 0;
                }
                .ae-modal-header p {
                    color: #64748b;
                    font-size: 13px;
                    margin: 0;
                }
                .ae-form-group {
                    margin-bottom: 16px;
                }
                .ae-form-group label {
                    display: block;
                    font-size: 13px;
                    font-weight: 600;
                    color: #334155;
                    margin-bottom: 5px;
                }
                .ae-form-group input {
                    width: 100%;
                    height: 42px;
                    padding: 8px 14px;
                    border: 1.5px solid #cbd5e1;
                    border-radius: 6px;
                    font-size: 14px;
                    color: #1e293b;
                    background: #ffffff;
                    outline: none;
                    box-sizing: border-box;
                    transition: border-color 0.2s;
                }
                .ae-form-group input:focus {
                    border-color: #009146;
                    box-shadow: 0 0 0 3px rgba(0, 145, 70, 0.12);
                }
                .ae-password-wrapper {
                    position: relative;
                }
                .ae-password-wrapper input {
                    padding-right: 42px;
                }
                .ae-eye-btn {
                    position: absolute;
                    right: 12px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    cursor: pointer;
                    color: #64748b;
                    font-size: 15px;
                    padding: 4px;
                }
                .ae-eye-btn:hover {
                    color: #009146;
                }
                .ae-btn-submit {
                    width: 100%;
                    height: 44px;
                    background: #009146;
                    color: #ffffff;
                    border: none;
                    border-radius: 6px;
                    font-size: 15px;
                    font-weight: 600;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    transition: background 0.2s;
                    margin-top: 20px;
                }
                .ae-btn-submit:hover {
                    background: #007a3a;
                }
                .ae-modal-footer-note {
                    text-align: center;
                    font-size: 13px;
                    color: #64748b;
                    margin-top: 16px;
                }
                .ae-modal-footer-note a {
                    color: #009146;
                    font-weight: 700;
                    text-decoration: underline;
                }
                .ae-alert {
                    display: none;
                    padding: 10px 14px;
                    border-radius: 6px;
                    font-size: 13px;
                    font-weight: 600;
                    margin-bottom: 14px;
                }
                .ae-alert-danger {
                    background: #fee2e2;
                    color: #991b1b;
                    border: 1px solid #f87171;
                }
                .ae-alert-success {
                    background: #dcfce7;
                    color: #166534;
                    border: 1px solid #86efac;
                }
            `;
            document.head.appendChild(style);
        }

        // Inject modal Markup
        if (!document.getElementById('ae_join_modal')) {
            var modalHTML = `
                <div class="ae-modal-backdrop" id="ae_join_modal">
                    <div class="ae-modal-dialog">
                        <button type="button" class="ae-modal-close" id="ae_modal_close_btn">&times;</button>
                        <div class="ae-modal-header">
                            <img src="images/logo/logo.png" alt="Anjuman Eraquee INDIA">
                            <h3>Join Membership</h3>
                            <p>Enter your details to start your membership application</p>
                        </div>
                        <div class="ae-alert" id="ae_modal_alert"></div>
                        <form id="ae_modal_signup_form">
                            <div class="ae-form-group">
                                <label for="ae_modal_phone">Registered Mobile Number *</label>
                                <input type="text" id="ae_modal_phone" name="phonenumber" placeholder="Enter 10-digit mobile number" maxlength="10" required>
                            </div>
                            <div class="ae-form-group">
                                <label for="ae_modal_email">Email Address *</label>
                                <input type="email" id="ae_modal_email" name="email" placeholder="name@example.com" required>
                            </div>
                            <div class="ae-form-group">
                                <label for="ae_modal_pass">Create Password *</label>
                                <div class="ae-password-wrapper">
                                    <input type="password" id="ae_modal_pass" name="password" placeholder="Minimum 6 characters" minlength="6" required>
                                    <button type="button" class="ae-eye-btn" id="ae_eye_pass_btn">
                                        <i class="fa fa-eye" id="ae_eye_pass_icon"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="ae-form-group">
                                <label for="ae_modal_cpass">Confirm Password *</label>
                                <div class="ae-password-wrapper">
                                    <input type="password" id="ae_modal_cpass" name="confirm_password" placeholder="Re-enter password" minlength="6" required>
                                    <button type="button" class="ae-eye-btn" id="ae_eye_cpass_btn">
                                        <i class="fa fa-eye" id="ae_eye_cpass_icon"></i>
                                    </button>
                                </div>
                                <small id="ae_match_indicator" style="display:none; font-weight:600; margin-top:4px;"></small>
                            </div>
                            <button type="submit" class="ae-btn-submit" id="ae_modal_submit_btn">
                                <span>Register & Continue</span> <i class="fa fa-arrow-right"></i>
                            </button>
                            <div class="ae-modal-footer-note">
                                Already registered? <a href="user-login.php">Log In here</a>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            var div = document.createElement('div');
            div.innerHTML = modalHTML;
            document.body.appendChild(div.firstElementChild);

            // Close events
            var modal = document.getElementById('ae_join_modal');
            var closeBtn = document.getElementById('ae_modal_close_btn');

            function closeModal() {
                modal.classList.remove('show');
            }

            closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });

            // Password eye toggles
            var eyePassBtn = document.getElementById('ae_eye_pass_btn');
            var passInput = document.getElementById('ae_modal_pass');
            var passIcon = document.getElementById('ae_eye_pass_icon');
            eyePassBtn.addEventListener('click', function () {
                if (passInput.type === 'password') {
                    passInput.type = 'text';
                    passIcon.className = 'fa fa-eye-slash';
                } else {
                    passInput.type = 'password';
                    passIcon.className = 'fa fa-eye';
                }
            });

            var eyeCpassBtn = document.getElementById('ae_eye_cpass_btn');
            var cpassInput = document.getElementById('ae_modal_cpass');
            var cpassIcon = document.getElementById('ae_eye_cpass_icon');
            eyeCpassBtn.addEventListener('click', function () {
                if (cpassInput.type === 'password') {
                    cpassInput.type = 'text';
                    cpassIcon.className = 'fa fa-eye-slash';
                } else {
                    cpassInput.type = 'password';
                    cpassIcon.className = 'fa fa-eye';
                }
            });

            // Real-time password match check
            cpassInput.addEventListener('keyup', function () {
                var indicator = document.getElementById('ae_match_indicator');
                if (cpassInput.value.length > 0) {
                    indicator.style.display = 'block';
                    if (passInput.value === cpassInput.value) {
                        indicator.style.color = '#009146';
                        indicator.innerHTML = '<i class="fa fa-check"></i> Passwords match';
                    } else {
                        indicator.style.color = '#dc2626';
                        indicator.innerHTML = '<i class="fa fa-times"></i> Passwords do not match';
                    }
                } else {
                    indicator.style.display = 'none';
                }
            });

            // Form submission via AJAX
            var form = document.getElementById('ae_modal_signup_form');
            var alertBox = document.getElementById('ae_modal_alert');
            var submitBtn = document.getElementById('ae_modal_submit_btn');

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var phone = document.getElementById('ae_modal_phone').value.trim();
                var email = document.getElementById('ae_modal_email').value.trim();
                var pass = passInput.value;
                var cpass = cpassInput.value;

                if (phone.replace(/[^0-9]/g, '').length < 10) {
                    alertBox.className = 'ae-alert ae-alert-danger';
                    alertBox.style.display = 'block';
                    alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> Please enter a valid 10-digit mobile number.';
                    return;
                }

                if (pass.length < 6) {
                    alertBox.className = 'ae-alert ae-alert-danger';
                    alertBox.style.display = 'block';
                    alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> Password must be at least 6 characters.';
                    return;
                }

                if (pass !== cpass) {
                    alertBox.className = 'ae-alert ae-alert-danger';
                    alertBox.style.display = 'block';
                    alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> Passwords do not match.';
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Creating Account...';

                var formData = new FormData(form);

                fetch('signup.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        alertBox.className = 'ae-alert ae-alert-success';
                        alertBox.style.display = 'block';
                        alertBox.innerHTML = '<i class="fa fa-check-circle"></i> ' + data.message;
                        setTimeout(function () {
                            window.location.href = data.redirect || 'registration.php';
                        }, 800);
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<span>Register & Continue</span> <i class="fa fa-arrow-right"></i>';
                        alertBox.className = 'ae-alert ae-alert-danger';
                        alertBox.style.display = 'block';
                        if (data.already_exists) {
                            alertBox.innerHTML = '<i class="fa fa-info-circle"></i> ' + data.message + ' <a href="user-login.php">Log In here</a>';
                        } else {
                            alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + data.message;
                        }
                    }
                })
                .catch(function () {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Register & Continue</span> <i class="fa fa-arrow-right"></i>';
                    alertBox.className = 'ae-alert ae-alert-danger';
                    alertBox.style.display = 'block';
                    alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> Network error. Please try again.';
                });
            });
        }

        // Attach modal trigger to all "Join Membership" links
        function attachJoinListeners() {
            var modal = document.getElementById('ae_join_modal');
            if (!modal) return;

            var targets = document.querySelectorAll('a[href*="registration"], [data-action="open-join-modal"]');
            targets.forEach(function (el) {
                // Ensure it's for Membership registration, not matrimonial
                var href = el.getAttribute('href') || '';
                var text = el.textContent.trim().toLowerCase();
                if (href.indexOf('matrimonial') !== -1) return;

                if (text.indexOf('join membership') !== -1 || href.indexOf('registration') !== -1) {
                    el.addEventListener('click', function (ev) {
                        ev.preventDefault();
                        modal.classList.add('show');
                        var phoneInput = document.getElementById('ae_modal_phone');
                        if (phoneInput) setTimeout(function () { phoneInput.focus(); }, 150);
                    });
                }
            });
        }

        attachJoinListeners();
        // Re-attach if mobile meanmenu creates dynamically cloned links
        setTimeout(attachJoinListeners, 800);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            fetchSettings();
            setupJoinMembershipModal();
        });
    } else {
        fetchSettings();
        setupJoinMembershipModal();
    }
})();
