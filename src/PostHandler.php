<?php
declare(strict_types=1);

namespace Whostyles\WordPress;

use Whostyles\Whostyles;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles post-specific Whostyles integrations (meta fields, HTTP headers, head tags).
 */
class PostHandler {
    /**
     * Meta key used to store the Whostyle hash for a post.
     */
    public const META_KEY_HASH = '_whostyle_hash';

    /**
     * Registers post related hooks.
     *
     * @return void
     */
    public function registerHooks(): void {
        add_action('init', [$this, 'registerPostMeta']);
        add_action('add_meta_boxes', [$this, 'addPostMetaBox']);
        add_action('save_post', [$this, 'savePostMetaBox']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);
        add_action('send_headers', [$this, 'sendHttpHeader']);
        add_action('wp_head', [$this, 'injectMetaTag']);
        add_filter('the_content', [$this, 'applyPostWhostyle']);
    }

    /**
     * Registers post meta fields for Gutenberg REST API compatibility.
     *
     * @return void
     */
    public function registerPostMeta(): void {
        $screens = ['post', 'page'];
        foreach ($screens as $screen) {
            register_post_meta($screen, self::META_KEY_HASH, [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
                'auth_callback' => function (): bool {
                    return current_user_can('edit_posts');
                }
            ]);
        }
    }

    /**
     * Enqueues the Whostyles CSS assets.
     *
     * @return void
     */
    public function enqueueAssets(): void {
        wp_enqueue_style(
            'whostyles-combined',
            plugins_url('assets/css/whostyles-combined.css', dirname(__FILE__)),
            [],
            '0.1.0'
        );
    }

    /**
     * Enqueues admin styling and scripts for live preview.
     *
     * @param string $hook The current admin page hook.
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        // Enqueue the main combined styling for preview visual rendering.
        $this->enqueueAssets();

        // Enqueue core JS decoder.
        wp_enqueue_script(
            'whostyle-core',
            plugins_url('assets/js/whostyle-core.js', dirname(__FILE__)),
            [],
            '0.1.0',
            true
        );

        // Enqueue admin DOM live preview script.
        wp_enqueue_script(
            'whostyles-admin',
            plugins_url('assets/js/whostyles-admin.js', dirname(__FILE__)),
            ['whostyle-core'],
            '0.1.0',
            true
        );
    }

    /**
     * Enqueues assets for the Gutenberg Block Editor.
     *
     * @return void
     */
    public function enqueueBlockEditorAssets(): void {
        if (!Settings::isPostsEnabled()) {
            return;
        }

        // Core JS decoder.
        wp_enqueue_script(
            'whostyle-core',
            plugins_url('assets/js/whostyle-core.js', dirname(__FILE__)),
            [],
            '0.1.0',
            true
        );

        // Gutenberg React Sidebar integration.
        wp_enqueue_script(
            'whostyles-gutenberg',
            plugins_url('assets/js/whostyles-gutenberg.js', dirname(__FILE__)),
            [
                'wp-plugins',
                'wp-edit-post',
                'wp-element',
                'wp-components',
                'wp-data',
                'whostyle-core'
            ],
            '0.1.0',
            true
        );
    }

    /**
     * Adds a metabox in the WordPress post editor to set the Whostyle hash.
     *
     * @return void
     */
    public function addPostMetaBox(): void {
        if (!Settings::isPostsEnabled()) {
            return;
        }

        // Apply to posts and pages.
        $screens = ['post', 'page'];
        foreach ($screens as $screen) {
            add_meta_box(
                'whostyles_post_meta',
                __('Whostyle Configuration', 'whostyles'),
                [$this, 'renderMetaBox'],
                $screen,
                'side',
                'default'
            );
        }
    }

    /**
     * Renders the metabox HTML inside the editor.
     *
     * @param \WP_Post $post The post object.
     * @return void
     */
    public function renderMetaBox($post): void {
        // Use nonce for verification.
        wp_nonce_field('whostyles_save_meta', 'whostyles_meta_nonce');

        // Retrieve existing hash.
        $value = get_post_meta($post->ID, self::META_KEY_HASH, true);
        if (!is_string($value)) {
            $value = '';
        }

        echo '<p>';
        echo '<label for="whostyles_hash_field" style="display:block; font-weight:bold; margin-bottom:5px;">';
        echo esc_html__('Enter Whostyle V1 Hash:', 'whostyles');
        echo '</label>';
        echo '<input type="text" id="whostyles_hash_field" name="whostyles_hash_field" value="'
            . esc_attr($value)
            . '" class="widefat" placeholder="{ws1:...}" />';
        echo '</p>';
        echo '<p class="description">';
        echo esc_html__(
            'Syndicates your styling. Format should match {ws1:abcdefgh...}',
            'whostyles'
        );
        echo '</p>';

        // Injected Live Preview area.
        echo '<div id="whostyles_preview_container" style="margin-top:15px; display:none;">';
        echo '<div style="font-weight:bold; margin-bottom:5px;">' . esc_html__('Live Preview', 'whostyles') . '</div>';
        echo '<div style="display:flex; gap:10px; flex-wrap:wrap;">';
        // Light Mode preview.
        echo '<div style="flex:1; min-width:140px; border:1px solid #ccc; padding:10px; border-radius:4px;">';
        echo '<div style="font-size:9px; color:#666; margin-bottom:5px; text-transform:uppercase;">Light Mode</div>';
        echo '<div id="whostyles_preview_light" style="padding:5px;">';
        echo '<h4 style="margin:0 0 5px 0;">Heading</h4>';
        echo '<p style="margin:0; font-size:12px;">This is a preview.</p>';
        echo '</div>';
        echo '</div>';
        // Dark Mode preview.
        echo '<div style="flex:1; min-width:140px; border:1px solid #333; background:#222; color:#fff; padding:10px;'
            . ' border-radius:4px;">';
        echo '<div style="font-size:9px; color:#aaa; margin-bottom:5px; text-transform:uppercase;">Dark Mode</div>';
        echo '<div id="whostyles_preview_dark" style="padding:5px;">';
        echo '<h4 style="margin:0 0 5px 0;">Heading</h4>';
        echo '<p style="margin:0; font-size:12px;">This is a preview.</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Saves the post metabox data when the post is saved.
     *
     * @param int $postId The post ID.
     * @return void
     */
    public function savePostMetaBox(int $postId): void {
        // Check if nonce is set.
        if (!isset($_POST['whostyles_meta_nonce'])) {
            return;
        }

        // Verify nonce.
        if (!wp_verify_nonce($_POST['whostyles_meta_nonce'], 'whostyles_save_meta')) {
            return;
        }

        // Check user permission.
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Check if field is present.
        if (!isset($_POST['whostyles_hash_field'])) {
            return;
        }

        $hash = sanitize_text_field(wp_unslash($_POST['whostyles_hash_field']));

        if (empty($hash)) {
            delete_post_meta($postId, self::META_KEY_HASH);
            return;
        }

        // Validate hash structure: {ws1:48base64chars}
        if (preg_match('/^{ws1:[A-Za-z0-9\-_]{48}}$/', $hash)) {
            update_post_meta($postId, self::META_KEY_HASH, $hash);
        }
    }

    /**
     * Injects the X-Whostyle HTTP header if the single page post has a whostyle set.
     *
     * @param \WP $wp The WP object.
     * @return void
     */
    public function sendHttpHeader($wp): void {
        if (!is_admin() && is_singular() && Settings::isPostsEnabled()) {
            $postId = get_the_ID();
            if ($postId > 0) {
                $hash = get_post_meta($postId, self::META_KEY_HASH, true);
                if (is_string($hash) && !empty($hash)) {
                    header('X-Whostyle: ' . $hash);
                }
            }
        }
    }

    /**
     * Injects the whostyle meta tag inside the HTML <head> for singular posts.
     *
     * @return void
     */
    public function injectMetaTag(): void {
        if (!is_admin() && is_singular() && Settings::isPostsEnabled()) {
            $postId = get_the_ID();
            if ($postId > 0) {
                $hash = get_post_meta($postId, self::META_KEY_HASH, true);
                if (is_string($hash) && !empty($hash)) {
                    echo sprintf(
                        '<meta name="whostyle" content="%s" />' . "\n",
                        esc_attr($hash)
                    );
                }
            }
        }
    }

    /**
     * Intercepts post content and wraps it inside a Whostyle container if post hash is defined.
     *
     * @param string $content The original post body text.
     * @return string Modified content wrapped in Whostyle layout container.
     */
    public function applyPostWhostyle(string $content): string {
        if (is_admin() || !is_singular() || !Settings::isPostsEnabled()) {
            return $content;
        }

        $postId = get_the_ID();
        if ($postId <= 0) {
            return $content;
        }

        $hash = get_post_meta($postId, self::META_KEY_HASH, true);
        if (!is_string($hash) || empty($hash)) {
            return $content;
        }

        // Discover parameters and generate styling attributes.
        $result = Whostyles::discover($hash);
        if ($result && !empty($result['attributes'])) {
            return sprintf(
                '<div %s>%s</div>',
                $result['attributes'],
                $content
            );
        }

        return $content;
    }
}
