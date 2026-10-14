# Changelog

All notable changes to this project will be documented in this file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.9.5 (2026-10-13)

### Added

*   Added the `cgit_wp_events_calendar_link_events` filter to link calendar days to the day archive instead of directly to events.
*   Added a `Disallow` rule for calendar navigation URLs to the WordPress virtual robots.txt.

### Changed

*   Calendar JavaScript is now deferred and versioned with the plugin version number.
*   The calendar now starts on the month being viewed on event date archives.
*   Calendar previous/next month and year links now have `rel="nofollow"` to discourage crawling.

### Fixed

*   Return a 404 for calendar query strings, date archives and AJAX requests outside the range of event dates.
*   Fixed calendar previous/next month links not rolling over the year (e.g. month 13 when viewing December).
*   Fixed calendar links to the first and last months being hidden when events start or end mid-month.
*   Fixed previous/next year links going beyond the earliest and latest event months.
*   Fixed calendar navigation links not updating their URLs or hiding at the earliest and latest event months after AJAX navigation.
*   Use the WordPress table prefix when finding the earliest and latest event dates.

## 1.9.4 (2026-06-02)

### Fixed

*   Fixed nullable parameters.

## 1.9.3 (2025-08-15)

### Fixed

*   Fix for situations where FacetWP overwrites the main query.

## 1.9.2 (2025-08-15)

### Fixed

*   Don't hide future events from lists of future events.

## 1.9.1 (2025-08-06)

### Fixed

*   Avoid generating infinite links to dates before the earliest event and after the latest event in the calendar.

## 1.9.0 (2025-08-01)

### Added

*   Added a full calendar option, showing a list of events on each day.

## 1.8.2 (2024-12-16)

### Changed

*   Use end date instead of start date to determine which are past events to avoid "archiving" ongoing events.

## 1.8.1 (2023-07-05)

### Fixed

*   Fixed calendar date link attribute.

## 1.8.0 (2023-05-05)

### Added

*   Added a filter for post type and taxonomy options.
*   Added date and time range utility functions.

### Changed

*   JavaScript has been written to remove the jQuery dependency.
*   Improved post type labels.

### Removed

*   Removed jQuery from script requirements.
*   Removed legacy user guide plugin support.
*   Removed legacy Custom Meta Boxes plugin support.
*   Removed the option to use the post tags taxonomy.
