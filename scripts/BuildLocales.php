<?php
declare(strict_types=1);

namespace WPE\Localization;

use WPE\Localization\AbstractScript\AbstractScript;

require 'vendor/autoload.php';

class BuildLocales extends AbstractScript
{
    const OUTPUT_DIRECTORY = 'missing_translations/';

    public function execute(): int
    {
        $hasFailed = false;
        foreach ($this->getLanguageFiles() as $languageFile) {
            if (count($languageFile->getMissingKeys())) {
                echo sprintf(
                    "Writing missing keys to %s\n",
                    self::OUTPUT_DIRECTORY.$languageFile->getName()
                );
                $OK = file_put_contents(
                    self::OUTPUT_DIRECTORY.$languageFile->getName(),
                    json_encode(array_fill_keys($languageFile->getMissingKeys(), ''), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
                if ($OK === false) {
                    $hasFailed = true;
                }
            }
        }

        return (int) $hasFailed;
    }
}

$languageStats = new BuildLocales();
exit($languageStats->execute());