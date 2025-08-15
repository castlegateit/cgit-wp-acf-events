# Changelog

All notable changes to this project will be documented in this file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
