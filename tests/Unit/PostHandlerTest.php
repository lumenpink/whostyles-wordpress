<?php
declare(strict_types=1);

namespace Whostyles\WordPress\Tests\Unit;

use Whostyles\WordPress\PostHandler;
use Brain\Monkey\Functions;

test('enqueueAssets calls wp_enqueue_style with combined stylesheet', function (): void {
    Functions\expect('plugins_url')
        ->once()
        ->with('assets/css/whostyles-combined.css', Mockery::any())
        ->andReturn('http://example.com/whostyles-combined.css');

    Functions\expect('wp_enqueue_style')
        ->once()
        ->with('whostyles-combined', 'http://example.com/whostyles-combined.css', [], '0.1.0');

    $handler = new PostHandler();
    $handler->enqueueAssets();
    expect(true)->toBeTrue(); // Assertion to avoid warning.
});

test('injectMetaTag prints meta tag in head when singular post has hash', function (): void {
    Functions\expect('is_admin')->andReturn(false);
    Functions\expect('is_singular')->andReturn(true);
    Functions\expect('get_option')->with('whostyles_enable_posts', '1')->andReturn('1');
    Functions\expect('get_the_ID')->andReturn(456);
    Functions\expect('get_post_meta')
        ->with(456, PostHandler::META_KEY_HASH, true)
        ->andReturn('{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}');
    Functions\expect('esc_attr')->andReturnUsing(fn($x) => $x);

    ob_start();
    $handler = new PostHandler();
    $handler->injectMetaTag();
    $output = ob_get_clean();

    expect($output)->toContain(
        '<meta name="whostyle" content="{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}" />'
    );
});

test('applyPostWhostyle wraps post body content if active and configured', function (): void {
    Functions\expect('is_admin')->andReturn(false);
    Functions\expect('is_singular')->andReturn(true);
    Functions\expect('get_option')->with('whostyles_enable_posts', '1')->andReturn('1');
    Functions\expect('get_the_ID')->andReturn(456);
    Functions\expect('get_post_meta')
        ->with(456, PostHandler::META_KEY_HASH, true)
        ->andReturn('{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}');

    $handler = new PostHandler();
    $body = '<p>Post Content</p>';
    $result = $handler->applyPostWhostyle($body);

    expect($result)->toContain('whostyle-container');
    expect($result)->toContain('ws-typography-tahoma');
    expect($result)->toContain('Post Content');
});

test('applyPostWhostyle does not touch content if post has no hash', function (): void {
    Functions\expect('is_admin')->andReturn(false);
    Functions\expect('is_singular')->andReturn(true);
    Functions\expect('get_option')->with('whostyles_enable_posts', '1')->andReturn('1');
    Functions\expect('get_the_ID')->andReturn(456);
    Functions\expect('get_post_meta')
        ->with(456, PostHandler::META_KEY_HASH, true)
        ->andReturn('');

    $handler = new PostHandler();
    $body = '<p>Post Content</p>';

    expect($handler->applyPostWhostyle($body))->toBe($body);
});

test('registerPostMeta calls register_post_meta for post and page', function (): void {
    Functions\expect('register_post_meta')
        ->twice()
        ->with(Mockery::any(), PostHandler::META_KEY_HASH, Mockery::type('array'));

    $handler = new PostHandler();
    $handler->registerPostMeta();
    expect(true)->toBeTrue();
});

test('enqueueAdminAssets enqueues assets on post edit page', function (): void {
    Functions\expect('plugins_url')->andReturn('http://example.com/asset');
    Functions\expect('wp_enqueue_style')->once();
    Functions\expect('wp_enqueue_script')
        ->twice()
        ->with(Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any(), true);

    $handler = new PostHandler();
    $handler->enqueueAdminAssets('post.php');
    expect(true)->toBeTrue();
});

test('enqueueBlockEditorAssets enqueues React files if posts enabled', function (): void {
    Functions\expect('get_option')->with('whostyles_enable_posts', '1')->andReturn('1');
    Functions\expect('plugins_url')->andReturn('http://example.com/asset');
    Functions\expect('wp_enqueue_script')
        ->twice()
        ->with(Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any(), true);

    $handler = new PostHandler();
    $handler->enqueueBlockEditorAssets();
    expect(true)->toBeTrue();
});

test('savePostMetaBox updates meta if nonce is valid and edit permission granted', function (): void {
    $_POST['whostyles_meta_nonce'] = 'valid_nonce';
    $_POST['whostyles_hash_field'] = '{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}';

    Functions\expect('wp_verify_nonce')->once()->with('valid_nonce', 'whostyles_save_meta')->andReturn(true);
    Functions\expect('current_user_can')->once()->with('edit_post', 101)->andReturn(true);
    Functions\expect('sanitize_text_field')->once()->andReturnUsing(fn($x) => $x);
    Functions\expect('wp_unslash')->once()->andReturnUsing(fn($x) => $x);

    Functions\expect('update_post_meta')
        ->once()
        ->with(101, PostHandler::META_KEY_HASH, '{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}');

    $handler = new PostHandler();
    $handler->savePostMetaBox(101);

    // Cleanup globals
    unset($_POST['whostyles_meta_nonce']);
    unset($_POST['whostyles_hash_field']);
    expect(true)->toBeTrue();
});

