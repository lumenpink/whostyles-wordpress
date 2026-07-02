<?php
declare(strict_types=1);

namespace Whostyles\WordPress;

use Whostyles\Whostyles;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles Webmention integrations, fetching, and caching remote Whostyles hashes.
 */
class WebmentionHandler {
    /**
     * Registers Webmention integration hooks.
     *
     * @return void
     */
    public function registerHooks(): void {
        add_action('wp_insert_comment', [$this, 'fetchAuthorWhostyle'], 10, 2);
    }

    /**
     * Checks if the comment is an external webmention and fetches/caches the Whostyle hash.
     *
     * @param int $commentId The comment ID.
     * @param \WP_Comment $comment The WordPress comment object.
     * @return void
     */
    public function fetchAuthorWhostyle(int $commentId, \WP_Comment $comment): void {
        // Run only if comments styling is enabled globally.
        if (!Settings::isCommentsEnabled()) {
            return;
        }

        $authorUrl = $comment->comment_author_url;
        if (empty($authorUrl)) {
            return;
        }

        // Only process external URLs.
        $homeUrl = get_home_url();
        if (strpos($authorUrl, $homeUrl) === 0) {
            return;
        }

        // Validate comment type.
        $allowedTypes = ['webmention', 'pingback', 'trackback'];
        if (!in_array($comment->comment_type, $allowedTypes, true)) {
            return;
        }

        $hash = $this->discoverRemoteHash($authorUrl);
        if ($hash !== null) {
            update_comment_meta($commentId, '_whostyle_hash', $hash);
        }
    }

    /**
     * Queries the external author URL to discover their syndicated Whostyle hash.
     *
     * @param string $url The target URL.
     * @return string|null The discovered hash or null on failure.
     */
    private function discoverRemoteHash(string $url): ?string {
        // Step 1: Query HTTP headers using a HEAD request (most efficient).
        $headResponse = wp_safe_remote_head($url, [
            'timeout' => 3,
            'redirection' => 2,
        ]);

        if (!is_wp_error($headResponse)) {
            $headerVal = wp_remote_retrieve_header($headResponse, 'x-whostyle');
            if (is_string($headerVal)) {
                $hash = trim($headerVal);
                if (preg_match('/^{ws1:[A-Za-z0-9\-_]{40}}$/', $hash)) {
                    return $hash;
                }
            }
        }

        // Step 2: Query HTML body using a GET request (limit size to 150KB).
        $getResponse = wp_safe_remote_get($url, [
            'timeout' => 5,
            'redirection' => 2,
            'limit_response_size' => 153600, // Read max 150KB
        ]);

        if (!is_wp_error($getResponse)) {
            $html = wp_remote_retrieve_body($getResponse);
            if (is_string($html) && !empty($html)) {
                $hash = Whostyles::extract($html);
                if ($hash !== null && preg_match('/^{ws1:[A-Za-z0-9\-_]{40}}$/', $hash)) {
                    return $hash;
                }
            }
        }

        return null;
    }
}
