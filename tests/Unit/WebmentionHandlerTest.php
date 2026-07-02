<?php
declare(strict_types=1);

namespace Whostyles\WordPress\Tests\Unit;

use Whostyles\WordPress\WebmentionHandler;
use Brain\Monkey\Functions;

test('fetchAuthorWhostyle exits early if comments are disabled', function (): void {
    Functions\expect('get_option')
        ->with('whostyles_enable_comments', '1')
        ->andReturn('');

    $commentObj = new \stdClass();
    $commentObj->comment_author_url = 'https://external-author.com/post';
    $commentObj->comment_type = 'webmention';

    Functions\expect('update_comment_meta')->never();

    $handler = new WebmentionHandler();
    /** @var \WP_Comment $wpComment */
    $wpComment = $commentObj;
    $handler->fetchAuthorWhostyle(789, $wpComment);
    expect(true)->toBeTrue();
});

test('fetchAuthorWhostyle exits early if author url is local', function (): void {
    Functions\expect('get_option')->with('whostyles_enable_comments', '1')->andReturn('1');
    Functions\expect('get_home_url')->andReturn('https://my-local-site.com');

    $commentObj = new \stdClass();
    $commentObj->comment_author_url = 'https://my-local-site.com/some-post';
    $commentObj->comment_type = 'webmention';

    Functions\expect('update_comment_meta')->never();

    $handler = new WebmentionHandler();
    /** @var \WP_Comment $wpComment */
    $wpComment = $commentObj;
    $handler->fetchAuthorWhostyle(789, $wpComment);
    expect(true)->toBeTrue();
});

test('fetchAuthorWhostyle exits early if comment type is not webmention', function (): void {
    Functions\expect('get_option')->with('whostyles_enable_comments', '1')->andReturn('1');
    Functions\expect('get_home_url')->andReturn('https://my-local-site.com');

    $commentObj = new \stdClass();
    $commentObj->comment_author_url = 'https://external-author.com/post';
    $commentObj->comment_type = 'comment'; // standard comment

    Functions\expect('update_comment_meta')->never();

    $handler = new WebmentionHandler();
    /** @var \WP_Comment $wpComment */
    $wpComment = $commentObj;
    $handler->fetchAuthorWhostyle(789, $wpComment);
    expect(true)->toBeTrue();
});

test('fetchAuthorWhostyle fetches hash from HEAD response and updates meta', function (): void {
    Functions\expect('get_option')->with('whostyles_enable_comments', '1')->andReturn('1');
    Functions\expect('get_home_url')->andReturn('https://my-local-site.com');
    Functions\expect('is_wp_error')->andReturn(false);

    $commentObj = new \stdClass();
    $commentObj->comment_author_url = 'https://external-author.com/post';
    $commentObj->comment_type = 'webmention';

    // Mock HEAD request
    Functions\expect('wp_safe_remote_head')
        ->once()
        ->with('https://external-author.com/post', Mockery::any())
        ->andReturn(['headers' => []]);

    Functions\expect('wp_remote_retrieve_header')
        ->once()
        ->with(Mockery::any(), 'x-whostyle')
        ->andReturn('{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}');

    Functions\expect('update_comment_meta')
        ->once()
        ->with(789, '_whostyle_hash', '{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}');

    $handler = new WebmentionHandler();
    /** @var \WP_Comment $wpComment */
    $wpComment = $commentObj;
    $handler->fetchAuthorWhostyle(789, $wpComment);
    expect(true)->toBeTrue();
});

test('fetchAuthorWhostyle falls back to GET html body parse if HEAD fails', function (): void {
    Functions\expect('get_option')->with('whostyles_enable_comments', '1')->andReturn('1');
    Functions\expect('get_home_url')->andReturn('https://my-local-site.com');
    Functions\expect('is_wp_error')->andReturn(false);

    $commentObj = new \stdClass();
    $commentObj->comment_author_url = 'https://external-author.com/post';
    $commentObj->comment_type = 'webmention';

    // Mock HEAD request to return empty header.
    Functions\expect('wp_safe_remote_head')->once()->andReturn([]);
    Functions\expect('wp_remote_retrieve_header')->once()->andReturn('');

    // Mock GET request to return HTML with meta tag.
    $html = '<html><head>'
        . '<meta name="whostyle" content="{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}" />'
        . '</head></html>';

    Functions\expect('wp_safe_remote_get')
        ->once()
        ->with('https://external-author.com/post', Mockery::any())
        ->andReturn([]);

    Functions\expect('wp_remote_retrieve_body')
        ->once()
        ->andReturn($html);

    Functions\expect('update_comment_meta')
        ->once()
        ->with(789, '_whostyle_hash', '{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}');

    $handler = new WebmentionHandler();
    /** @var \WP_Comment $wpComment */
    $wpComment = $commentObj;
    $handler->fetchAuthorWhostyle(789, $wpComment);
    expect(true)->toBeTrue();
});
