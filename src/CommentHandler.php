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
        add_filter('comment_text', [$this, 'parseCommentText'], 10, 2);
    }

    /**
     * Parses the comment text, extracts the Whostyle hash, wraps in dynamic style tags and cleans the body.
     *
     * @param string $content The original comment content.
     * @param \WP_Comment|null $comment The WordPress comment object.
     * @return string Modified comment content with CSS containers or clean text.
     */
    public function parseCommentText(string $content, ?\WP_Comment $comment = null): string {
        // Only run if Whostyles for comments is enabled in site settings.
        if (!Settings::isCommentsEnabled()) {
            return $content;
        }

        $hash = null;

        // 1. Try to read comment-specific whostyle hash metadata (Webmentions cache).
        if ($comment !== null) {
            $commentId = (int) $comment->comment_ID;
            $metaHash = get_comment_meta($commentId, '_whostyle_hash', true);
            if (is_string($metaHash) && preg_match('/^{ws1:[A-Za-z0-9\-_]{40}}$/', $metaHash)) {
                $hash = $metaHash;
            }
        }

        // 2. Discover attributes.
        if ($hash !== null) {
            $result = Whostyles::discover($hash);
        } else {
            // Fallback: discover inline hashes inside comment content.
            $result = Whostyles::discover($content);
        }

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
