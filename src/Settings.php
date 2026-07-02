<?php
declare(strict_types=1);

namespace Whostyles\WordPress;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the administrative Settings page and site configuration parameters.
 */
class Settings {
    /**
     * Registers settings related hooks.
     *
     * @return void
     */
    public function registerHooks(): void {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'initializeSettings']);
    }

    /**
     * Check if Whostyles logic is enabled for comment structures.
     *
     * @return bool
     */
    public static function isCommentsEnabled(): bool {
        return (bool) get_option('whostyles_enable_comments', '1');
    }

    /**
     * Check if Whostyles logic is enabled for post/page content structure.
     *
     * @return bool
     */
    public static function isPostsEnabled(): bool {
        return (bool) get_option('whostyles_enable_posts', '1');
    }

    /**
     * Adds the Settings link to the general administration menu.
     *
     * @return void
     */
    public function addSettingsPage(): void {
        add_options_page(
            __('Whostyles Settings', 'whostyles'),
            __('Whostyles', 'whostyles'),
            'manage_options',
            'whostyles',
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Registers plugin options settings sections and fields via the WordPress Settings API.
     *
     * @return void
     */
    public function initializeSettings(): void {
        register_setting('whostyles_settings', 'whostyles_enable_comments', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '1',
        ]);

        register_setting('whostyles_settings', 'whostyles_enable_posts', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '1',
        ]);

        add_settings_section(
            'whostyles_general_section',
            __('General Settings', 'whostyles'),
            [$this, 'renderSectionDescription'],
            'whostyles'
        );

        add_settings_field(
            'whostyles_enable_comments_field',
            __('Enable in Comments', 'whostyles'),
            [$this, 'renderCommentsCheckbox'],
            'whostyles',
            'whostyles_general_section'
        );

        add_settings_field(
            'whostyles_enable_posts_field',
            __('Enable for Posts', 'whostyles'),
            [$this, 'renderPostsCheckbox'],
            'whostyles',
            'whostyles_general_section'
        );
    }

    /**
     * Renders settings section description.
     *
     * @return void
     */
    public function renderSectionDescription(): void {
        echo '<p>';
        echo esc_html__(
            'Configure how Whostyles V2 hashes are parsed and styled.',
            'whostyles'
        );
        echo '</p>';
    }

    /**
     * Renders comment checkbox input markup.
     *
     * @return void
     */
    public function renderCommentsCheckbox(): void {
        $val = get_option('whostyles_enable_comments', '1');
        echo sprintf(
            '<input type="checkbox" name="whostyles_enable_comments" value="1" %s />',
            checked('1', $val, false)
        );
        echo ' <span class="description">';
        echo esc_html__(
            'Parse styles for user comments automatically.',
            'whostyles'
        );
        echo '</span>';
    }

    /**
     * Renders post checkbox input markup.
     *
     * @return void
     */
    public function renderPostsCheckbox(): void {
        $val = get_option('whostyles_enable_posts', '1');
        echo sprintf(
            '<input type="checkbox" name="whostyles_enable_posts" value="1" %s />',
            checked('1', $val, false)
        );
        echo ' <span class="description">';
        echo esc_html__(
            'Enable page/post level styling templates.',
            'whostyles'
        );
        echo '</span>';
    }

    /**
     * Renders settings administration page markup.
     *
     * @return void
     */
    public function renderSettingsPage(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('whostyles_settings');
                do_settings_sections('whostyles');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
