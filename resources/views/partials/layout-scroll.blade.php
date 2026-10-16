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

        function syncFooterHeight() {
            const footer = document.querySelector('.site-footer');
            document.documentElement.style.setProperty(
                '--footer-height',
                footer ? footer.offsetHeight + 'px' : '0px'
            );
        }

        function syncAppShell() {
            syncNavbarHeight();
            syncFooterHeight();
        }

        window.lockAppShellScroll = function (lock) {
            const main = appShellMain();
            if (main) {
                main.style.overflow = lock ? 'hidden' : '';
            }
            document.body.style.overflow = lock ? 'hidden' : '';
        };

        document.addEventListener('DOMContentLoaded', syncAppShell);
        window.addEventListener('resize', syncAppShell);
        window.addEventListener('load', syncAppShell);
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(syncAppShell);
        }
    })();
</script>
