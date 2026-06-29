<script>
    (function () {
        function appShellNavbar() {
            return document.querySelector('nav.navbar, header.navbar');
        }

        function appShellMain() {
            return document.querySelector('main[role="main"], main.content, main');
        }

        function syncNavbarHeight() {
            const nav = appShellNavbar();
            if (nav) {
                document.documentElement.style.setProperty('--navbar-height', nav.offsetHeight + 'px');
            }
        }

        window.lockAppShellScroll = function (lock) {
            const main = appShellMain();
            if (main) {
                main.style.overflow = lock ? 'hidden' : '';
            }
            document.body.style.overflow = lock ? 'hidden' : '';
        };

        document.addEventListener('DOMContentLoaded', syncNavbarHeight);
        window.addEventListener('resize', syncNavbarHeight);
        window.addEventListener('load', syncNavbarHeight);
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(syncNavbarHeight);
        }
    })();
</script>
