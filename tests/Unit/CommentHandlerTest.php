<?php
declare(strict_types=1);

namespace Whostyles\WordPress\Tests\Unit;

use Whostyles\WordPress\CommentHandler;
use Brain\Monkey\Functions;

test('comment text is unmodified if comments option is disabled', function (): void {
    Functions\expect('get_option')
        ->with('whostyles_enable_comments', '1')
        ->andReturn('');

    $handler = new CommentHandler();
    $hash = '{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}';
    $comment = 'Hello ' . $hash;

    expect($handler->parseCommentText($comment))->toBe($comment);
});

test('comment text is unmodified if no hash exists', function (): void {
    Functions\expect('get_option')
        ->with('whostyles_enable_comments', '1')
        ->andReturn('1');

    $handler = new CommentHandler();
    $comment = 'Hello World';

    expect($handler->parseCommentText($comment))->toBe($comment);
});

test('comment text is parsed and wrapped if comments option is enabled', function (): void {
    Functions\expect('get_option')
        ->with('whostyles_enable_comments', '1')
        ->andReturn('1');

    $handler = new CommentHandler();
    $hash = '{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}';
    $comment = 'Hello ' . $hash;
    $result = $handler->parseCommentText($comment);

    expect($result)->toContain('whostyle-container');
    expect($result)->toContain('ws-typography-tahoma');
    expect($result)->toContain('Hello');
    expect($result)->not->toContain($hash);
});

test('comment text is parsed using metadata if comment object and meta exist', function (): void {
    Functions\expect('get_option')
        ->with('whostyles_enable_comments', '1')
        ->andReturn('1');

    $commentObj = new \stdClass();
    $commentObj->comment_ID = 999;

    Functions\expect('get_comment_meta')
        ->once()
        ->with(999, '_whostyle_hash', true)
        ->andReturn('{ws1:tP3ClmkB9fXwGhouAFXM3d3dGhou9fXwZqr_MzNE}');

    $handler = new CommentHandler();
    $comment = 'Hello World';

    /** @var \WP_Comment $wpComment */
    $wpComment = $commentObj;
    $result = $handler->parseCommentText($comment, $wpComment);

    expect($result)->toContain('whostyle-container');
    expect($result)->toContain('ws-typography-tahoma');
    expect($result)->toContain('Hello World');
});
