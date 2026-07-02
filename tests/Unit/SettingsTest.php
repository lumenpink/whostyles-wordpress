<?php
declare(strict_types=1);

namespace Whostyles\WordPress\Tests\Unit;

use Whostyles\WordPress\Settings;
use Brain\Monkey\Functions;

test('comments are enabled by default', function (): void {
    Functions\expect('get_option')
        ->once()
        ->with('whostyles_enable_comments', '1')
        ->andReturn('1');

    expect(Settings::isCommentsEnabled())->toBeTrue();
});

test('comments can be disabled', function (): void {
    Functions\expect('get_option')
        ->once()
        ->with('whostyles_enable_comments', '1')
        ->andReturn('');

    expect(Settings::isCommentsEnabled())->toBeFalse();
});

test('posts are enabled by default', function (): void {
    Functions\expect('get_option')
        ->once()
        ->with('whostyles_enable_posts', '1')
        ->andReturn('1');

    expect(Settings::isPostsEnabled())->toBeTrue();
});

test('posts can be disabled', function (): void {
    Functions\expect('get_option')
        ->once()
        ->with('whostyles_enable_posts', '1')
        ->andReturn('');

    expect(Settings::isPostsEnabled())->toBeFalse();
});
