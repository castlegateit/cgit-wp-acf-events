document.addEventListener('DOMContentLoaded', function () {
    const calendar = document.querySelector('.cgit-events-calendar');

    if (!calendar) {
        return;
    }

    const full = calendar.classList.contains('cgit-events-calendar-full');

    const minYear = parseInt(calendar.dataset.cgitEventsMinYear, 10);
    const minMonth = parseInt(calendar.dataset.cgitEventsMinMonth, 10);
    const maxYear = parseInt(calendar.dataset.cgitEventsMaxYear, 10);
    const maxMonth = parseInt(calendar.dataset.cgitEventsMaxMonth, 10);

    let prevYearLink;
    let prevMonthLink;
    let nextYearLink;
    let nextMonthLink;

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

    // Initialize previous and next links
    initPrevNextLinks();

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

    // Initialize previous and next links
    function initPrevNextLinks() {
        prevYearLink = document.querySelector('.cgit-events-prev-year a');
        prevMonthLink = document.querySelector('.cgit-events-prev-month a');
        nextYearLink = document.querySelector('.cgit-events-next-year a');
        nextMonthLink = document.querySelector('.cgit-events-next-month a');

        // Check links exist
        if (!prevYearLink) {
            prevYearLink = document.createElement('a');
            prevYearLink.innerHTML = '&laquo;';
            prevYearLink.setAttribute('href', '#');
            document.querySelector('.cgit-events-prev-year').append(prevYearLink);
        }

        if (!prevMonthLink) {
            prevMonthLink = document.createElement('a');
            prevMonthLink.innerHTML = '&lsaquo;';
            prevMonthLink.setAttribute('href', '#');
            document.querySelector('.cgit-events-prev-month').append(prevMonthLink);
        }

        if (!nextYearLink) {
            nextYearLink = document.createElement('a');
            nextYearLink.innerHTML = '&raquo;';
            nextYearLink.setAttribute('href', '#');
            document.querySelector('.cgit-events-next-year').append(nextYearLink);
        }

        if (!nextMonthLink) {
            nextMonthLink = document.createElement('a');
            nextMonthLink.innerHTML = '&rsaquo;';
            nextMonthLink.setAttribute('href', '#');
            document.querySelector('.cgit-events-next-month').append(nextMonthLink);
        }

        // Get AJAX response on click or do nothing if target date is before the
        // earliest event or after than the latest event.
        prevYearLink.addEventListener('click', function (e) {
            e.preventDefault();

            let destYear = parseInt(calendar.dataset.year, 10) - 1;
            let destMonth = parseInt(calendar.dataset.month, 10);

            if (destYear < minYear) {
                return;
            }

            send({ year: destYear, month: destMonth });
        });

        prevMonthLink.addEventListener('click', function (e) {
            e.preventDefault();

            let destYear = parseInt(calendar.dataset.year, 10);
            let destMonth = parseInt(calendar.dataset.month, 10) - 1;

            if (destMonth < 1) {
                destYear = destYear - 1;
                destMonth = 12;
            }

            if (destYear < minYear || (destYear === minYear && destMonth < minMonth)) {
                return;
            }

            send({ year: destYear, month: destMonth });
        });

        nextYearLink.addEventListener('click', function (e) {
            e.preventDefault();

            let destYear = parseInt(calendar.dataset.year, 10) + 1;
            let destMonth = parseInt(calendar.dataset.month, 10);

            if (destYear > maxYear) {
                return;
            }

            send({ year: destYear, month: destMonth });
        });

        nextMonthLink.addEventListener('click', function (e) {
            e.preventDefault();

            let destYear = parseInt(calendar.dataset.year, 10);
            let destMonth = parseInt(calendar.dataset.month, 10) + 1;

            if (destMonth > 12) {
                destYear = destYear + 1;
                destMonth = 1;
            }

            if (destYear > maxYear || (destYear === maxYear && destMonth > maxMonth)) {
                return;
            }

            send({ year: destYear, month: destMonth });
        });
    }
});
