<?php
declare(strict_types=1);

namespace WPE\Localization;

use WPE\Localization\AbstractScript\AbstractScript;
use WPE\Localization\LanguageFile\LanguageFile;

require 'vendor/autoload.php';

class ImportLocales extends AbstractScript
{
    /**
     * @var LanguageFile[]
     */
    private $importFiles;
    private $importDirectory;
    private $outputDirectory;

    public function __construct()
    {
        parent::__construct();
        $mergeDirectory = getcwd().DIRECTORY_SEPARATOR.'terminal_import'.DIRECTORY_SEPARATOR;
        $this->importDirectory = $mergeDirectory.'input'.DIRECTORY_SEPARATOR;
        $this->outputDirectory = $mergeDirectory.'output'.DIRECTORY_SEPARATOR;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function execute(): int
    {
        $hasFailed = false;
        $this->clearOutputDirectory();
        if (count($this->getImportFiles()) === 0) {
            echo "\nNo import files found.\n";
            return 0;
        }
        foreach ($this->getImportFiles() as $file) {
            $this->importFiles[] = LanguageFile::createLanguageFile($this->importDirectory.$file, $this->baseFiles);
        }

        foreach ($this->importFiles as $importFile) {
            $importData = $importFile->getJsonData();
            $localeFile = $this->getLocaleFile($importFile);
            $outputJson = [];
            $newStringCount = 0;
            $updatedStringCount = 0;
            $skippedCount = 0;
            echo sprintf(
                "Importing %s...\n",
                $importFile->getName()
            );
            foreach ($localeFile->getJsonData() as $localeKey => $localeString) {
                $outputJson[$localeKey] = $localeString;
                if (isset($importData[$localeKey]) && $importData[$localeKey] !== $localeString) {
                    $outputJson[$localeKey] = $importData[$localeKey];
                    $updatedStringCount++;
                }
            }
            foreach ($importFile->getJsonData() as $importKey => $importString) {
                if (isset($outputJson[$importKey]) === false) {
                    if (isset($importFile->getBaseFile()->getJsonData()[$importKey]) === true) {
                        $outputJson[$importKey] = $importData[$importKey];
                        $newStringCount++;
                    } else {
                        echo sprintf(
                            "Skipping unknown string %s...\n",
                            $importKey
                        );
                        $skippedCount++;
                    }
                }
            }

            $baseKeyOrder = array_keys($localeFile->getBaseFile()->getJsonData());
            uksort($outputJson, function ($a, $b) use ($baseKeyOrder) {
                $pos_a = array_search($a, $baseKeyOrder);
                $pos_b = array_search($b, $baseKeyOrder);
                return $pos_a - $pos_b;
            });

            $OK = file_put_contents(
                $this->outputDirectory.$importFile->getName(),
                json_encode($outputJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
            );
            echo sprintf(
                "Updated %s strings (%s new strings, %s updated strings, %s skipped strings).\n\n",
                $newStringCount + $updatedStringCount,
                $newStringCount,
                $updatedStringCount,
                $skippedCount

            );
            if ($OK === false) {
                $hasFailed = true;
            }
        }

        if ($hasFailed === false) {
            echo "\nMerged files written to output directory.\n";
        }
        return (int) $hasFailed;
    }

    private function getLocaleFile(LanguageFile $importFile): LanguageFile {
        foreach ($this->getLanguageFiles() as $languageFile) {
            if ($languageFile->getName() === $importFile->getName()) {
                return $languageFile;
            }
        }
        throw new \Exception('No locale file found for ' . $importFile->getName());
    }

    protected function getImportFiles(): array
    {
        $languageFiles = scandir($this->importDirectory);
        return array_filter($languageFiles, array(AbstractScript::class, 'filterSourceFiles'));
    }

    private function clearOutputDirectory(): void {
        $outputFiles = scandir($this->outputDirectory);
        $outputFiles = array_filter($outputFiles, array(AbstractScript::class, 'filterSourceFiles'));
        foreach ($outputFiles as $outputFile) {
            unlink($this->outputDirectory.$outputFile);
        }
    }
}

$languageStats = new ImportLocales();
exit($languageStats->execute());