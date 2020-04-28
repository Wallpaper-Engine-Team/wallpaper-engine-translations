<?php
declare(strict_types=1);

namespace WPE\Localization\LanguageFile;

use PHPHtmlParser\Dom;

class LanguageFile extends AbstractFile implements LanguageFileInterface
{
    private $missingKeys;
    private $violations = [];
    private $baseFile;

    private function __construct(string $filePath, string $fileGroup, BaseFile $baseFile)
    {
        $this->baseFile = $baseFile;
        parent::__construct($filePath, $fileGroup);
    }

    public static function createLanguageFile(string $filePath, array $baseFiles): LanguageFile
    {
        $fileGroup = self::parseFileGroup($filePath);
        if (!isset($baseFiles[$fileGroup])) {
            throw new \InvalidArgumentException('Unable to match file to base file: '.$filePath);
        }
        return new LanguageFile($filePath, $fileGroup, $baseFiles[$fileGroup]);
    }

    public function getFileCompletion(): float
    {
        return 100 - floor(count($this->getMissingKeys()) / count($this->baseFile->getJsonData()) * 100);
    }

    public function getMissingKeys(): array
    {
        if ($this->missingKeys !== null) {
            return $this->missingKeys;
        }
        $this->missingKeys = [];
        foreach ($this->baseFile->getJsonData() as $baseKey => $baseString) {
            $found = false;
            foreach ($this->getJsonData() as $jsonKey => $localizedString) {
                if ($baseKey === $jsonKey) {
                    if ($localizedString != '') {
                        $found = true;
                        $this->findStringViolations($baseString, $localizedString, $jsonKey);
                    }
                    break;
                }
            }
            if ($found === false) {
                $this->missingKeys[] = $baseKey;
            }
        }
        $this->findInvalidKeys();

        return $this->missingKeys;
    }

    private function findStringViolations(string $baseString, string $localizedString, string $jsonKey): void
    {
        $this->findMissingVariables($baseString, $localizedString, $jsonKey);
        $this->findMissingLinebreaks($baseString, $localizedString, $jsonKey);
        $this->findMismatchingHtml($baseString, $localizedString, $jsonKey);
    }

    private function findMissingVariables(string $baseString, string $localizedString, string $jsonKey): void
    {
        if (preg_match_all('/{{(.*?)}}/', $baseString, $baseVariables)) {
            preg_match_all('/{{(.*?)}}/', $localizedString, $localizedVariables);
            foreach ($baseVariables[0] as $baseVariable) {
                $found = false;
                foreach ($localizedVariables[0] as $localizedVariableKey => $localizedVariable) {
                    if ($baseVariable === $localizedVariable) {
                        $found = true;
                        unset( $localizedVariables[0][$localizedVariableKey] );
                        break;
                    }
                }
                if ($found === false) {
                    $this->addViolation($jsonKey, 'Key "'.$jsonKey.'" was translated but is missing variable '.$baseVariable);
                }
            }
        }
        if (preg_match_all('/\[\[\[(.*?)\]\]\]/', $baseString, $baseVariables)) {
            preg_match_all('/\[\[\[(.*?)\]\]\]/', $localizedString, $localizedVariables);
            foreach ($baseVariables[0] as $baseVariable) {
                $found = false;
                foreach ($localizedVariables[0] as $localizedVariableKey => $localizedVariable) {
                    if ($baseVariable === $localizedVariable) {
                        $found = true;
                        unset( $localizedVariables[0][$localizedVariableKey] );
                        break;
                    }
                }
                if ($found === false) {
                    $this->addViolation($jsonKey, 'Key "'.$jsonKey.'" was translated but is missing platform variable '.$baseVariable);
                }
            }
        }
    }

    private function findMissingLinebreaks(string $baseString, string $localizedString, string $jsonKey): void
    {
        preg_match_all('/(\\n)/', $baseString, $baseLineBreaks);
        preg_match_all('/(\\n)/', $localizedString, $localizedLineBreaks);
        if (count($localizedLineBreaks[0]) !== count($baseLineBreaks[0])) {
            $this->addViolation(
                $jsonKey,
                'Line breaks ("\n") for key "'.$jsonKey.'" do not match. Base string has '.
                count($baseLineBreaks[0]).' and localized string has '.
                count($localizedLineBreaks[0]).' line breaks.'
            );
        }
    }

    private function findMismatchingHtml(string $baseString, string $localizedString, string $jsonKey): void
    {
        /** If is a workaround to skip strings which look like HTML tags ("< Select >") */
        if (!(
            strpos($baseString, '< ') === 0 &&
            strpos($baseString, ' >') === strlen($baseString) - 2)
        ) {
            $baseHtml = new Dom();
            $baseHtml->loadStr($baseString, []);
            $baseTags = $baseHtml->getElementsByTag('*');
            $localizedHtml = new Dom();
            $localizedHtml->loadStr($localizedString, []);
            $localizedTags = $localizedHtml->getElementsByTag('*');
            /** @var Dom\TextNode $baseTag */
            /** @var Dom\TextNode $localizedTag */
            foreach ($baseTags as $baseTag) {
                foreach ($localizedTags as $id => $localizedTag) {
                    if (
                        $baseTag->getTag()->getAttributes() === $localizedTag->getTag()->getAttributes() &&
                        $baseTag->getTag()->isSelfClosing() === $localizedTag->getTag()->isSelfClosing() &&
                        $baseTag->getTag()->name() === $localizedTag->getTag()->name()) {
                        unset($localizedTags[$id]);
                    }
                }
            }
            if (count($localizedTags) > 0) {
                $this->addViolation(
                    $jsonKey,
                    'HTML content for "'.$jsonKey.'" does not match base string. Check near "'.$localizedTag->outerHtml(
                    ).'"'
                );
            }
            // PHP is stupid and does not select self-closing HTML elements so we'll do it manually
            $baseLineBreaks = substr_count($baseString, '<br/>');
            $localizedLineBreaks = substr_count($localizedString, '<br/>');
            if ($baseLineBreaks !== $localizedLineBreaks) {
                $this->addViolation(
                    $jsonKey,
                    'HTML content for "'.$jsonKey.'" does not match base string, HTML <br/> count does not match.'
                );
            }
        }
    }

    public function getBaseFile(): BaseFile {
        return $this->baseFile;
    }

    public function addViolation( string $jsonKey, string $errorMessage): void
    {
        $this->violations[] = $errorMessage;
        $this->missingKeys[] = $jsonKey;
    }

    public function hasErrors(): bool
    {
        return !empty($this->violations);
    }

    public function getViolations(): array
    {
        return $this->violations;
    }

    private function findInvalidKeys()
    {
        $baseKeys = $this->baseFile->getJsonData();
        foreach ($this->getJsonData() as $jsonKey => $jsonData) {
            if (isset($baseKeys[$jsonKey]) === false) {
                $this->addViolation( $jsonKey, 'Key "'.$jsonKey.'" does not exist in base file');
            }
        }
    }
}