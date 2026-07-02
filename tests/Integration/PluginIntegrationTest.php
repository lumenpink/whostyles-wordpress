<?php
declare(strict_types=1);

namespace Whostyles\WordPress\Tests\Integration;

use Whostyles\WordPress\Plugin;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

test('plugin registers all wordpress hooks on initialize', function (): void {
    $plugin = Plugin::getInstance();
    $plugin->initialize();

    // Verify comment and post content filters.
    expect(Filters\has_added('comment_text'))->toBeGreaterThan(0);
    expect(Filters\has_added('the_content'))->toBeGreaterThan(0);

    // Verify action hooks for assets, headers, metadata, and metaboxes.
    expect(Actions\has_added('wp_enqueue_scripts'))->toBeGreaterThan(0);
    expect(Actions\has_added('send_headers'))->toBeGreaterThan(0);
    expect(Actions\has_added('wp_head'))->toBeGreaterThan(0);
    expect(Actions\has_added('add_meta_boxes'))->toBeGreaterThan(0);
    expect(Actions\has_added('save_post'))->toBeGreaterThan(0);

    // Verify administration settings panel actions.
    expect(Actions\has_added('admin_menu'))->toBeGreaterThan(0);
    expect(Actions\has_added('admin_init'))->toBeGreaterThan(0);
});
