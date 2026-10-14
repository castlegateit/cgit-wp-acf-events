<?php

/**
 * Disallow crawling of calendar navigation URLs in the WordPress virtual
 * robots.txt. Has no effect if the site has a physical robots.txt file.
 *
 * The rule is added as its own user agent group so that it remains valid when
 * other plugins (e.g. Yoast SEO) rewrite the robots.txt output. Crawlers merge
 * groups for the same user agent.
 */
add_filter('robots_txt', function ($output, $public) {
    if (!$public) {
        return $output;
    }

    return rtrim($output) . "\n\nUser-agent: *\nDisallow: /*cgit-year=\n";
}, 10, 2);
