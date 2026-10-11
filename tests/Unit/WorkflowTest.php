<?php

declare(strict_types=1);

namespace Whostyles\WordPress\Tests\Unit;

test('forgejo ci workflow exists and defines required jobs and triggers', function (): void {
    $workflowPath = dirname(__DIR__, 2) . '/.forgejo/workflows/ci.yaml';
    $altWorkflowPath = dirname(__DIR__, 2) . '/.forgejo/workflows/ci.yml';

    $exists = file_exists($workflowPath) || file_exists($altWorkflowPath);
    expect($exists)->toBeTrue('O arquivo de workflow .forgejo/workflows/ci.yaml (ou ci.yml) deve existir.');

    $path = file_exists($workflowPath) ? $workflowPath : $altWorkflowPath;
    $content = (string) file_get_contents($path);

    expect($content)->not->toBeEmpty();
    expect($content)->toContain('push:');
    expect($content)->toContain('pull_request:');
    expect($content)->toContain('composer');
    expect($content)->toContain('phpstan');
    expect($content)->toContain('pest');
});
