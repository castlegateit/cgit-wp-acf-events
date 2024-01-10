<?php

namespace Castlegate\AcfEvents;

class Plugin
{
    public static function init(): void
    {
        // Register ACF fields
        Fields::init();

        // Adjust the query to handle event dates
        Query::init();
    }
}