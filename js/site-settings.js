/**
 * Anjuman Eraquee INDIA - Dynamic Site Settings Hydration
 * Fetches settings dynamically from api/settings.php (or data/settings.json)
 * and reflects updates made by Admin (such as topbar phone numbers, emails, etc.)
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
                if (phones[idx] && idx < 3) { // there are 3 phone numbers
                    strong.textContent = phones[idx];
                }
            });
        }

        // 3. Update Topbar Login Link to point to admin/login.php
        var loginLinks = document.querySelectorAll('.header-social a, .mobile-menu a');
        loginLinks.forEach(function (link) {
            if (link.textContent.trim() === 'Login' || link.textContent.trim() === 'Login |') {
                link.setAttribute('href', 'admin/login.php');
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

        // 4. Update WhatsApp links if present
        var waLinks = document.querySelectorAll('a[href*="whatsapp"], a[href*="wa.me"]');
        if (data.whatsapp_number) {
            waLinks.forEach(function (wa) {
                wa.setAttribute('href', 'https://wa.me/' + cleanPhone(data.whatsapp_number));
            });
        }
    }

    function fetchSettings() {
        // Try fetching from api/settings.php, fallback to data/settings.json
        var apiPath = 'api/settings.php';
        var fallbackPath = 'data/settings.json';

        // Check if page is in a subfolder or root
        if (window.location.pathname.indexOf('/admin/') !== -1) {
            return; // don't execute inside admin panel itself
        }

        fetch(apiPath)
            .then(function (res) {
                if (!res.ok) throw new Error('API not ok');
                return res.json();
            })
            .then(function (data) {
                applySettings(data);
            })
            .catch(function () {
                // Fallback to static JSON file
                fetch(fallbackPath)
                    .then(function (res) { return res.json(); })
                    .then(function (data) { applySettings(data); })
                    .catch(function (err) {
                        console.warn('Site settings could not be fetched dynamically:', err);
                    });
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchSettings);
    } else {
        fetchSettings();
    }
})();

