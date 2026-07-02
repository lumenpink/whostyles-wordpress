<?php
declare(strict_types=1);

namespace Whostyles\WordPress;

use Whostyles\Whostyles;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles WordPress comment formatting and Whostyle parsing.
 */
class CommentHandler {
    /**
     * Registers comment related hooks.
     *
     * @return void
     */
    public function registerHooks(): void {
        add_filter('comment_text', [$this, 'parseCommentText'], 10, 1);
    }

    /**
     * Parses the comment text, extracts the Whostyle hash, wraps in dynamic style tags and cleans the body.
     *
     * @param string $content The original comment content.
     * @return string Modified comment content with CSS containers or clean text.
     */
    public function parseCommentText(string $content): string {
        // Only run if Whostyles for comments is enabled in site settings.
        if (!Settings::isCommentsEnabled()) {
            return $content;
        }

        // Discover Whostyle details from the comment body text.
        $result = Whostyles::discover($content);
        if ($result && !empty($result['attributes'])) {
            $cleanContent = Whostyles::clean($content);
            
            // Wrap in safe custom properties container and return.
            return sprintf(
                '<div %s>%s</div>',
                $result['attributes'],
                $cleanContent
            );
        }

        return $content;
    }
}
