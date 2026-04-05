(function () {
    var elements = document.querySelectorAll('.pk-reveal');

    if (!('IntersectionObserver' in window)) {
        elements.forEach(function (el) {
            el.classList.add('is-visible');
        });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
                return;
            }

            var parent = entry.target.parentElement;
            if (!parent) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
                return;
            }

            var siblings = Array.prototype.slice.call(parent.querySelectorAll('.pk-reveal:not(.is-visible)'));
            var index = siblings.indexOf(entry.target);
            var delay = Math.min(Math.max(index, 0) * 80, 300);

            window.setTimeout(function () {
                entry.target.classList.add('is-visible');
            }, delay);

            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12 });

    elements.forEach(function (el) {
        observer.observe(el);
    });
}());
