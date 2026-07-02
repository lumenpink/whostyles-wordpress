<?php
declare(strict_types=1);

namespace Whostyles\WordPress;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Orchestrator.
 *
 * Implements the Singleton pattern to bootstrap handlers and manage lifecycle hooks.
 */
class Plugin {
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Comment parsing handler.
     *
     * @var CommentHandler|null
     */
    private ?CommentHandler $commentHandler = null;

    /**
     * Post styling and metadata handler.
     *
     * @var PostHandler|null
     */
    private ?PostHandler $postHandler = null;

    /**
     * Webmention and integration handler.
     *
     * @var WebmentionHandler|null
     */
    private ?WebmentionHandler $webmentionHandler = null;

    /**
     * Admin settings handler.
     *
     * @var Settings|null
     */
    private ?Settings $settings = null;

    /**
     * Private constructor to enforce Singleton.
     */
    private function __construct() {}

    /**
     * Retrieves the Singleton instance of the Plugin.
     *
     * @return self
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Bootstraps the plugin by instantiating and registering core components.
     *
     * @return void
     */
    public function initialize(): void {
        $this->settings = new Settings();
        $this->settings->registerHooks();

        $this->commentHandler = new CommentHandler();
        $this->commentHandler->registerHooks();

        $this->postHandler = new PostHandler();
        $this->postHandler->registerHooks();

        $this->webmentionHandler = new WebmentionHandler();
        $this->webmentionHandler->registerHooks();
    }

    /**
     * Retrieves the Settings handler instance.
     *
     * @return Settings|null
     */
    public function getSettings(): ?Settings {
        return $this->settings;
    }

    /**
     * Retrieves the Comment handler instance.
     *
     * @return CommentHandler|null
     */
    public function getCommentHandler(): ?CommentHandler {
        return $this->commentHandler;
    }

    /**
     * Retrieves the Post handler instance.
     *
     * @return PostHandler|null
     */
    public function getPostHandler(): ?PostHandler {
        return $this->postHandler;
    }

    /**
     * Retrieves the Webmention handler instance.
     *
     * @return WebmentionHandler|null
     */
    public function getWebmentionHandler(): ?WebmentionHandler {
        return $this->webmentionHandler;
    }
}
