<?php
declare(strict_types=1);

namespace WPE\Localization;

use WPE\Localization\LanguageFile\BaseFile;
use WPE\Localization\LanguageFile\LanguageFile;

require 'vendor/autoload.php';

/**
 * Class LocalizationAnalyzer
 * Returns error code 1 on failure and 0 on successful validation
 */
class LocalizationAnalyzer
{
    private const BASE_FILES = ['core_en-us.json', 'ui_en-us.json'];

    private $baseDir;
    private $baseFiles = [];
    private $languageFiles;

    public function __construct()
    {
        $this->baseDir = getcwd().DIRECTORY_SEPARATOR.'locale'.DIRECTORY_SEPARATOR;
        $this->loadBaseFiles();
    }

    public function execute(): int
    {
        $languageFiles = self::getLanguageFiles();
        foreach ($languageFiles as $languageFile) {
            $this->printCompletionStats($languageFile);
        }

        $this->printViolations();

        return intval($this->hasFailed());
    }

    private function printCompletionStats(LanguageFile $languageFile): void
    {
        echo sprintf(
            "%s:\t%s%%\tMissing: %s\n",
            $languageFile->getName(),
            $languageFile->getFileCompletion(),
            count($languageFile->getMissingKeys())
        );
    }

    private function loadBaseFiles()
    {
        foreach (self::BASE_FILES as $baseFile) {
            $baseFile = BaseFile::createBaseFile($this->baseDir.$baseFile);
            $this->baseFiles[$baseFile->getFileGroup()] = $baseFile;
        }
    }

    /**
     * @return LanguageFile[]
     */
    private function getLanguageFiles(): array {
        if ($this->languageFiles === null) {
            foreach ($this->getLanguageFileNames() as $file) {
                $this->languageFiles[] = LanguageFile::createLanguageFile($this->baseDir.$file, $this->baseFiles);
            }
        }
        return $this->languageFiles;
    }

    private function getLanguageFileNames(): array
    {
        $languageFiles = scandir($this->baseDir);

        return array_filter($languageFiles, array(LocalizationAnalyzer::class, 'filterSourceFiles'));
    }

    private function filterSourceFiles(string $file): bool
    {
        return pathinfo($file, PATHINFO_EXTENSION) === 'json' && !in_array($file, self::BASE_FILES);
    }

    private function printViolations(): void
    {
        foreach ($this->getLanguageFiles() as $languageFile) {
            if (!$languageFile->hasErrors()) {
                continue;
            }
            echo $languageFile->getName()."\n";
            foreach ($languageFile->getViolations() as $violation) {
                echo $violation."\n";
            }
            echo "\n\n";
        }
    }

    private function hasFailed(): bool {
        foreach ($this->getLanguageFiles() as $languageFile) {
            if ($languageFile->hasErrors()) {
                return true;
            }
        }
        return false;
    }
}

$languageStats = new LocalizationAnalyzer();
exit($languageStats->execute());