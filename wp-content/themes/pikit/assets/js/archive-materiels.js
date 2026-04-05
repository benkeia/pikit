(function () {
    var cards = document.querySelectorAll('.pk-archive-card-link');

    cards.forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            card.classList.add('is-hovered');
        });

        card.addEventListener('mouseleave', function () {
            card.classList.remove('is-hovered');
        });
    });
}());