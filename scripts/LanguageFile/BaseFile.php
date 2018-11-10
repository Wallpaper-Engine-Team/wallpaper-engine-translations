<?php
declare(strict_types=1);

namespace WPE\Localization\LanguageFile;

class BaseFile extends AbstractFile implements LanguageFileInterface
{

    private function __construct(string $filePath, string $fileGroup)
    {
        parent::__construct($filePath, $fileGroup);
    }

    public static function createBaseFile(string $filePath): BaseFile
    {
        $fileGroup = self::parseFileGroup($filePath);
        return new BaseFile($filePath, $fileGroup);
    }
}