<?php
declare(strict_types=1);

namespace WPE\Localization\AbstractScript;

/**
 * Returns error code 1 on failure and 0 on successful validation
 */
interface ScriptInterface
{
    public function execute(): int;
}