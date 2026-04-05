(function () {
    var toggle = document.querySelector('[data-pikit-header-toggle]');
    var nav = document.getElementById('pikit-site-nav');

    if (!toggle || !nav) {
        return;
    }

    toggle.addEventListener('click', function () {
        var isOpen = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}());
