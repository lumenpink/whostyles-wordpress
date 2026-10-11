<?php
/**
 * Pest Bootstrap File.
 *
 * Configures the testing environment and bootstraps Brain\Monkey for WordPress mocking.
 */

declare(strict_types=1);

// Initialize Brain\Monkey hooks setup.
uses()
    ->beforeEach(function (): void {
        Brain\Monkey\setUp();
    })
    ->afterEach(function (): void {
        Brain\Monkey\tearDown();
    })
    ->in(__DIR__);
