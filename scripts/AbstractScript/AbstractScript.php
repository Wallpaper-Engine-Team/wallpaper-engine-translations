<?php
declare(strict_types=1);

namespace WPE\Localization\AbstractScript;

use WPE\Localization\LanguageFile\BaseFile;
use WPE\Localization\LanguageFile\LanguageFile;

require 'vendor/autoload.php';

abstract class AbstractScript implements ScriptInterface
{
    private const BASE_FILES = ['core_en-us.json', 'ui_en-us.json'];

    protected $baseDir;
    protected $baseFiles = [];
    private $languageFiles;

    public function __construct()
    {
        $this->baseDir = getcwd().DIRECTORY_SEPARATOR.'locale'.DIRECTORY_SEPARATOR;
        $this->loadBaseFiles();
    }

    protected function loadBaseFiles()
    {
        foreach (self::BASE_FILES as $baseFile) {
            $baseFile = BaseFile::createBaseFile($this->baseDir.$baseFile);
            $this->baseFiles[$baseFile->getFileGroup()] = $baseFile;
        }
    }

    protected function getBaseFiles(): array {
        return $this->baseFiles;
    }

    /**
     * @return LanguageFile[]
     */
    protected function getLanguageFiles(): array {
        if ($this->languageFiles === null) {
            foreach ($this->getLanguageFileNames() as $file) {
                $this->languageFiles[] = LanguageFile::createLanguageFile($this->baseDir.$file, $this->baseFiles);
            }
        }
        return $this->languageFiles;
    }

    protected function getLanguageFileNames(): array
    {
        $languageFiles = scandir($this->baseDir);

        return array_filter($languageFiles, array(AbstractScript::class, 'filterSourceFiles'));
    }

    protected function filterSourceFiles(string $file): bool
    {
        return pathinfo($file, PATHINFO_EXTENSION) === 'json' && !in_array($file, self::BASE_FILES) && strpos($file, 'qqq') === false;
    }
}