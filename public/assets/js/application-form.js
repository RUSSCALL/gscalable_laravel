// Turns the application form into 5 steps. Without this script every step is shown in order and the form still submits.
(function () {
    var form = document.getElementById('application-form');
    if (!form) {
        return;
    }

    var steps = Array.prototype.slice.call(form.querySelectorAll('.application-step'));
    var stepperItems = Array.prototype.slice.call(form.querySelectorAll('[data-step-goto]'));
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var current = 0;
    var visited = {};

    initAvailabilityOther();

    form.classList.add('is-stepped');
    // Validation is done step by step below; the browser can't point at a field on a hidden step.
    form.noValidate = true;
    form.querySelectorAll('[data-js-only]').forEach(function (el) { el.hidden = false; });

    function fields(step) {
        return Array.prototype.slice.call(step.querySelectorAll('input, select, textarea'))
            .filter(function (el) { return el.type !== 'hidden' && !el.disabled; });
    }

    function firstInvalid(step) {
        return fields(step).find(function (el) { return !el.checkValidity(); }) || null;
    }

    function refreshStepper() {
        stepperItems.forEach(function (item, i) {
            var step = steps[i];
            var hasError = step.hasAttribute('data-has-errors');
            var complete = visited[i] && !hasError && !firstInvalid(step);
            var status = '';

            item.classList.toggle('is-current', i === current);
            item.classList.toggle('is-complete', !!complete);
            item.classList.toggle('has-error', hasError);

            if (i === current) {
                item.setAttribute('aria-current', 'step');
                status = ', current step';
            } else {
                item.removeAttribute('aria-current');
            }
            if (hasError) {
                status += ', needs attention';
            } else if (complete) {
                status += ', complete';
            }
            item.querySelector('[data-step-status]').textContent = status;
        });
    }

    function show(index, moveFocus) {
        visited[current] = true;
        current = index;
        visited[current] = true;

        steps.forEach(function (step, i) { step.classList.toggle('is-active', i === index); });
        refreshStepper();

        if (moveFocus) {
            var top = form.getBoundingClientRect().top + window.scrollY - 110;
            window.scrollTo({ top: Math.max(top, 0), behavior: reduceMotion ? 'auto' : 'smooth' });
            steps[index].querySelector('.application-step-title').focus({ preventScroll: true });
        }
    }

    function report(field) {
        field.reportValidity();
        field.focus();
    }

    stepperItems.forEach(function (item, i) {
        item.addEventListener('click', function () { show(i, true); });
    });

    form.addEventListener('click', function (e) {
        if (e.target.closest('[data-step-prev]')) {
            show(Math.max(current - 1, 0), true);
        } else if (e.target.closest('[data-step-next]')) {
            var invalid = firstInvalid(steps[current]);
            if (invalid) {
                report(invalid);
                return;
            }
            show(Math.min(current + 1, steps.length - 1), true);
        }
    });

    // Editing a step that came back with server errors clears its red marker; client checks take over from there.
    form.addEventListener('change', function (e) {
        var step = e.target.closest('.application-step');
        if (step) {
            step.removeAttribute('data-has-errors');
        }
        refreshStepper();
    });

    form.addEventListener('submit', function (e) {
        for (var i = 0; i < steps.length; i++) {
            var invalid = firstInvalid(steps[i]);
            if (invalid) {
                e.preventDefault();
                steps[i].setAttribute('data-has-errors', '');
                show(i, true);
                report(invalid);
                return;
            }
        }
    });

    // After a failed submission every step has been filled in, so finished ones can show their check mark straight away.
    if (form.querySelector('[data-has-errors]')) {
        steps.forEach(function (step, i) { visited[i] = true; });
    }

    var initial = parseInt(form.dataset.initialStep, 10) || 1;
    show(Math.min(Math.max(initial, 1), steps.length) - 1, false);

    function initAvailabilityOther() {
        var availability = document.getElementById('availability');
        var otherField = document.getElementById('availability_other_field');
        var otherInput = document.getElementById('availability_other');
        if (!availability || !otherField || !otherInput) {
            return;
        }

        function sync() {
            var isOther = availability.value === 'other';
            otherField.hidden = !isOther;
            otherInput.required = isOther;
        }

        availability.addEventListener('change', sync);
        sync();
    }
})();
