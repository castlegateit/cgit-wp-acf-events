jQuery(document).ready(function($) {

    function cgitEventsGetYear() {
        return $('.cgit-events-calendar').data('cgit-events-year');
    }

    function cgitEventsGetMonth() {
        return $('.cgit-events-calendar').data('cgit-events-month');
    }

    function cgitEventsDrawCalendar(response) {
        var calendar = $('.cgit-events-calendar');

        // Trigger data loading event.
        calendar.trigger('cgit-wp-acf-events:data:loading');

        // Update the year data attribute.
        calendar.data('cgit-events-year', response.year);

        // Update the month data attribute.
        calendar.data('cgit-events-month', response.month);

        // Update current date indicator & link
        $('.cgit-events-current span').html(response.current);
        $('.cgit-events-current a').attr('href', '/event/' + response.year + '/' + response.month);

        // Update each cell
        $('.cgit-events-calendar tbody td').each(function(index, element) {
            var cell = $(this);
            var anchor = cell.children('a');

            // Add date number.
            anchor.html(response.days[index].date);

            //$(this).children('a').attr('href', response.days[index].link);
            if (response.days[index].events.length == 1) {
                // If the day has events, give it a link
                $(this).children('a').attr('href', response.days[index].events[0].permalink);
            } else if (response.days[index].events.length > 1) {
                $(this).children('a').attr('href', response.days[index].link);
            } else {
                // Days without events have no href attribute
                anchor.removeAttr('href');
            }

            // Give the cell its correct class.
            cell.attr('class', '').addClass(response.days[index].class);
        });

        // Trigger data loaded event
        $('.cgit-events-calendar').trigger('cgit-wp-acf-events:data:loaded');
    }

    /**
     * Cleans date data to ensure no odd dates are returned.
     *
     * @return array
     */
    function cgitEventsCleanData(data) {
        if (data.month > 12) {
            data.month = 1;
            data.year++;
        } else if (data.month < 1) {
            data.month = 12;
            data.year--;
        }
        return data;
    }

    /**
     * Click event for next year
     */
    jQuery('.cgit-events-next-year').click(function(e){

        // Define the data
        var data = {
            'year' : parseInt(cgitEventsGetYear()) + 1,
            'month' : cgitEventsGetMonth(),
            'action' : 'cgit_events_calendar'
        };

        jQuery.post(ajax_object.ajax_url, cgitEventsCleanData(data), function(response) {
            cgitEventsDrawCalendar(response);
        }, 'json');

        e.preventDefault();
        return;
    });

    /**
     * Click event for previous year
     */
    jQuery('.cgit-events-prev-year').click(function(e){

        // Define the data
        var data = {
            'year' : parseInt(cgitEventsGetYear()) - 1,
            'month' : cgitEventsGetMonth(),
            'action' : 'cgit_events_calendar'
        };

        jQuery.post(ajax_object.ajax_url, cgitEventsCleanData(data), function(response) {
            cgitEventsDrawCalendar(response);
        }, 'json');

        e.preventDefault();
        return;
    });

    /**
     * Click event for next month
     */
    jQuery('.cgit-events-next-month').click(function(e){

        // Define the data
        var data = {
            'year' : cgitEventsGetYear(),
            'month' : parseInt(cgitEventsGetMonth()) + 1,
            'action' : 'cgit_events_calendar'
        };

        jQuery.post(ajax_object.ajax_url, cgitEventsCleanData(data), function(response) {
            cgitEventsDrawCalendar(response);
        }, 'json');

        e.preventDefault();
        return;
    });

    /**
     * Click event for previous month
     */
    jQuery('.cgit-events-prev-month').click(function(e){

        // Define the data
        var data = {
            'year' : cgitEventsGetYear(),
            'month' : parseInt(cgitEventsGetMonth()) - 1,
            'action' : 'cgit_events_calendar'
        };

        jQuery.post(ajax_object.ajax_url, cgitEventsCleanData(data), function(response) {
            cgitEventsDrawCalendar(response);
        }, 'json');

        e.preventDefault();
        return;
    });



});
