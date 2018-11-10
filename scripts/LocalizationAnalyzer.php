<?php
declare(strict_types=1);

namespace WPE\Localization;

use WPE\Localization\AbstractScript\AbstractScript;
use WPE\Localization\LanguageFile\LanguageFile;

require 'vendor/autoload.php';

/**
 * Class LocalizationAnalyzer
 * Returns error code 1 on failure and 0 on successful validation
 */
class LocalizationAnalyzer extends AbstractScript
{
    private $printMissingKeys;

    public function __construct(bool $printMissingKeys)
    {
        parent::__construct();
        $this->printMissingKeys = $printMissingKeys;
    }

    public function execute(): int
    {
        foreach (self::getLanguageFiles() as $languageFile) {
            $this->printCompletionStats($languageFile);
            if ((count($languageFile->getMissingKeys())) && $this->printMissingKeys) {
                echo "\n------Missing Keys------\n\n";
                $this->printMissingKeys($languageFile);
            }
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

    private function printMissingKeys(LanguageFile $languageFile): void
    {
        foreach ($languageFile->getMissingKeys() as $missingKey) {
            echo sprintf(
                "%s\n",
                $missingKey
            );
        }
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

$printMissing = false;
foreach ($argv as $argument) {
    if ($argument === '--print-missing') {
        $printMissing = true;
    }
}
$languageStats = new LocalizationAnalyzer($printMissing);
exit($languageStats->execute());