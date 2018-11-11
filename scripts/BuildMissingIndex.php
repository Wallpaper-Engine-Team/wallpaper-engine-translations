<?php
declare(strict_types=1);

namespace WPE\Localization;

use WPE\Localization\AbstractScript\AbstractScript;

require 'vendor/autoload.php';

class BuildMissingIndex extends AbstractScript
{
    const OUTPUT_DIRECTORY = 'missing_translations/';

    public function execute(): int
    {
        $hasFailed = false;
        $this->clearOutputDirectory();
        foreach ($this->getLanguageFiles() as $languageFile) {
            if (count($languageFile->getMissingKeys())) {
                echo sprintf(
                    "Writing missing keys to %s\n",
                    self::OUTPUT_DIRECTORY.$languageFile->getName()
                );
                $missingKeys = [];
                foreach ($languageFile->getMissingKeys() as $missingKey) {
                    $missingKeys[$missingKey] = $languageFile->getBaseFile()->getJsonData()[$missingKey];
                }
                $OK = file_put_contents(
                    self::OUTPUT_DIRECTORY.$languageFile->getName(),
                    json_encode($missingKeys, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
                );
                if ($OK === false) {
                    $hasFailed = true;
                }
            }
        }
        return (int) $hasFailed;
    }

    private function clearOutputDirectory(): void {
        $outputFiles = scandir(self::OUTPUT_DIRECTORY);
        $outputFiles = array_filter($outputFiles, array(AbstractScript::class, 'filterSourceFiles'));
        foreach ($outputFiles as $outputFile) {
            unlink(self::OUTPUT_DIRECTORY.$outputFile);
        }
    }
}

$languageStats = new BuildMissingIndex();
exit($languageStats->execute());