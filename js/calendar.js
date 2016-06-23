jQuery(document).ready(function($){

    function cgitEventsGetYear() {
        return $('.cgit-events-calendar').data('cgit-events-year');
    }

    function cgitEventsGetMonth() {
        return $('.cgit-events-calendar').data('cgit-events-month');
    }

    function cgitEventsDrawCalendar(response) {
        // Update the year and month
        $('.cgit-events-calendar').data('cgit-events-year', response.year);
        $('.cgit-events-calendar').data('cgit-events-month', response.month);
        $('.cgit-events-current span').html(response.current);
        $('.cgit-events-current a').attr('href', '/event/' + response.year + '/' + response.month);

        $('.cgit-events-calendar tbody td').each(function(index, element){
            $(this).children('a').html(response.days[index].date).attr('class', '');
            $(this).children('a').attr('href', response.days[index].link);
            if (response.days[index].events.length == 1) {
                $(this).children('a').attr('href', response.days[index].events[0].permalink);
            }
            $(this).attr('class', '').addClass(response.days[index].class);
        });
    }

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
