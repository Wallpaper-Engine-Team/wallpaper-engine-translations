<?php
declare(strict_types=1);

namespace WPE\Localization\LanguageFile;

interface LanguageFileInterface
{
    public function getFileGroup(): string;
    public function getName(): string;
    public function getJsonData(): array;
}