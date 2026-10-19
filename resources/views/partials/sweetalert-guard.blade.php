@once
<style>
    /* Popups must sit above custom dropdown menus (z-index 12050) */
    .swal2-container {
        z-index: 20000 !important;
    }
    .swal2-container.swal2-backdrop-show,
    .swal2-container.swal2-noanimation {
        background: rgba(15, 23, 42, 0.45) !important;
        border: 0 !important;
        box-shadow: none !important;
        box-sizing: border-box;
    }
    .swal2-container .swal2-popup {
        border: none !important;
        outline: none !important;
    }
    .swal2-container .swal2-popup.request-details-popup,
    .swal2-container .swal2-popup.attachment-preview-popup {
        box-shadow: 0 28px 70px rgba(0, 0, 0, 0.58) !important;
    }
    /* Never show custom filter dropdown UI inside alert popups */
    .swal2-container .filter-select-wrap {
        display: none !important;
    }
</style>
<script>
    (function () {
        if (window.__sweetAlertGuardInit) return;
        window.__sweetAlertGuardInit = true;

        const cleanupSwalPopup = (popup) => {
            if (!popup) return;
            popup.querySelectorAll('.filter-select-wrap').forEach((wrap) => {
                const select = wrap.querySelector('select');
                if (!select || !wrap.parentNode) return;
                select.classList.remove('filter-select__native');
                delete select.dataset.filterEnhanced;
                wrap.parentNode.insertBefore(select, wrap);
                wrap.remove();
            });
        };

        const patchSwalOptions = (options) => {
            if (!options || typeof options !== 'object') {
                return options;
            }

            const config = Object.assign({}, options);
            const previousDidOpen = config.didOpen;

            config.didOpen = (popup) => {
                if (typeof window.closeAllFilterSelects === 'function') {
                    window.closeAllFilterSelects();
                }
                cleanupSwalPopup(popup);
                if (typeof previousDidOpen === 'function') {
                    previousDidOpen(popup);
                }
            };

            return config;
        };

        const installGuard = () => {
            if (!window.Swal || window.__swalFireGuarded) return;
            window.__swalFireGuarded = true;

            const originalFire = Swal.fire.bind(Swal);
            Swal.fire = function (...args) {
                if (typeof window.closeAllFilterSelects === 'function') {
                    window.closeAllFilterSelects();
                }

                if (args[0] && typeof args[0] === 'object') {
                    args[0] = patchSwalOptions(args[0]);
                }

                return originalFire(...args);
            };
        };

        if (window.Swal) {
            installGuard();
        } else if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', installGuard);
        } else {
            installGuard();
        }
    })();
</script>
@endonce
