(function () {
    var form = document.querySelector('[data-single-reservation-form]');
    if (!form) {
        return;
    }

    var pickupInput = form.querySelector('#pickup_datetime');
    var returnInput = form.querySelector('#return_datetime');
    var feedback = form.querySelector('[data-form-feedback]');
    var submitButton = form.querySelector('.pk-single-submit');

    function parseLocal(value) {
        if (!value) {
            return null;
        }

        var date = new Date(value);
        return isNaN(date.getTime()) ? null : date;
    }

    function minutes(date) {
        return date.getHours() * 60 + date.getMinutes();
    }

    function setFeedback(message, isError) {
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.classList.toggle('is-error', Boolean(isError));
        feedback.classList.toggle('is-success', !isError && message !== '');
    }

    function validate() {
        var pickupDate = parseLocal(pickupInput && pickupInput.value ? pickupInput.value : '');
        var returnDate = parseLocal(returnInput && returnInput.value ? returnInput.value : '');

        if (!pickupDate || !returnDate) {
            setFeedback('Choisis les deux dates de réservation.', true);
            return false;
        }

        if (returnDate <= pickupDate) {
            setFeedback('La date de retour doit être postérieure à la date de retrait.', true);
            return false;
        }

        var dates = [pickupDate, returnDate];
        var valid = dates.every(function (date) {
            var day = date.getDay();
            var slot = minutes(date);
            var isWeekday = day >= 1 && day <= 5;
            var startsOk = slot >= (8 * 60 + 30);
            var endsOk = slot <= (17 * 60 + 30);
            return isWeekday && startsOk && endsOk;
        });

        if (!valid) {
            setFeedback('Créneaux autorisés : lundi au vendredi, entre 08h30 et 17h30.', true);
            return false;
        }

        setFeedback('Créneau valide. Vérification du stock à l’envoi.', false);
        return true;
    }

    [pickupInput, returnInput].forEach(function (input) {
        if (!input) {
            return;
        }

        input.addEventListener('change', validate);
        input.addEventListener('blur', validate);
    });

    form.addEventListener('submit', function (event) {
        if (!validate()) {
            event.preventDefault();
            if (submitButton) {
                submitButton.focus();
            }
        }
    });
}());
