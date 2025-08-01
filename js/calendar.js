document.addEventListener('DOMContentLoaded', function () {
    const calendar = document.querySelector('.cgit-events-calendar');

    if (!calendar) {
        return;
    }

    const full = calendar.classList.contains('cgit-events-calendar-full');

    // Set initial year and month
    let today = new Date();

    calendar.dataset.year = today.getFullYear();
    calendar.dataset.month = today.getMonth() + 1;

    if (typeof calendar.dataset.cgitEventsYear !== 'undefined') {
        calendar.dataset.year = parseInt(calendar.dataset.cgitEventsYear, 10);
    }

    if (typeof calendar.dataset.cgitEventsMonth !== 'undefined') {
        calendar.dataset.month = parseInt(calendar.dataset.cgitEventsMonth, 10);
    }

    // Redraw calendar with response data
    function draw(response) {
        let current = document.querySelector('.cgit-events-current');
        let cells = document.querySelectorAll('.cgit-events-calendar tbody td');

        // Set current year and month based on response data
        calendar.dataset.year = response.year;
        calendar.dataset.month = response.month;

        // Update current date indicator and link
        current.querySelector('span').innerHTML = response.current;
        current.querySelector('a').setAttribute('href', '/event/' + response.year + '/' + response.month);

        // Update full table body?
        if (typeof response.body !== 'undefined') {
            const template = document.createElement('template');

            template.innerHTML = response.body.trim();
            calendar.querySelector('tbody').replaceWith(template.content.firstChild);

            return;
        }

        // Update all cells in calendar table
        let i = 0;

        for (let cell of cells) {
            let link = cell.querySelector('a');
            let day = response.days[i];

            // Set link text
            link.innerHTML = day.date;

            // Link to event(s)
            if (day.events.length === 1) {
                link.setAttribute('href', day.events[0].permalink);
            } else if (day.events.length > 1) {
                link.setAttribute('href', day.link);
            } else {
                link.removeAttribute('href');
            }

            // Update cell class attribute
            cell.setAttribute('class', day.class);

            i++;
        }
    }

    // Sanitize year and month data
    function sanitize(data) {
        if (data.month > 12) {
            data.month = 1;
            data.year++;
        } else if (data.month < 1) {
            data.month = 12;
            data.year--;
        }

        return data;
    }

    // Send an AJAX request for calendar data and redraw the calendar based on
    // the response data.
    function send(data) {
        let request = new XMLHttpRequest();
        let form = new FormData();

        let method = 'POST';
        let url = ajax_object.ajax_url;

        data = sanitize(data);

        form.append('action', 'cgit_events_calendar');
        form.append('year', data.year);
        form.append('month', data.month);

        if (full) {
            form.append('full', 1);
        }

        request.open(method, url, true);

        request.onreadystatechange = function () {
            if (request.readyState === 4 && request.status === 200) {
                draw(JSON.parse(request.responseText));
            }
        }

        request.send(form);
    }

    // Show next year
    document.querySelector('.cgit-events-next-year').addEventListener('click', function (e) {
        e.preventDefault();

        send({
            year: parseInt(calendar.dataset.year, 10) + 1,
            month: parseInt(calendar.dataset.month, 10)
        });
    });

    // Show previous year
    document.querySelector('.cgit-events-prev-year').addEventListener('click', function (e) {
        e.preventDefault();

        send({
            year: parseInt(calendar.dataset.year, 10) - 1,
            month: parseInt(calendar.dataset.month, 10)
        });
    });

    // Show next month
    document.querySelector('.cgit-events-next-month').addEventListener('click', function (e) {
        e.preventDefault();

        send({
            year: parseInt(calendar.dataset.year, 10),
            month: parseInt(calendar.dataset.month, 10) + 1
        });
    });

    // Show previous month
    document.querySelector('.cgit-events-prev-month').addEventListener('click', function (e) {
        e.preventDefault();

        send({
            year: parseInt(calendar.dataset.year, 10),
            month: parseInt(calendar.dataset.month, 10) - 1
        });
    });
});
