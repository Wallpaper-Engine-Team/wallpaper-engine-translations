<?php
declare(strict_types=1);

namespace WPE\Localization;

use PHPHtmlParser\Dom;
use Seld\JsonLint\JsonParser;

class LanguageFile
{
    private $filePath;
    private $jsonValues;
    private $missingKeys;
    private $violations = [];

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function getName(): string
    {
        return basename($this->filePath);
    }

    public function getFileGroup(): string
    {
        $groupNamePos = strpos($this->getName(), '_');
        if ($groupNamePos === false) {
            throw new \InvalidArgumentException('Invalid file name: '.$this->filePath);
        }

        return substr($this->getName(), 0, $groupNamePos);
    }

    public function getJsonData(): array
    {
        if ($this->jsonValues === null) {
            $this->parseJson();
        }

        return $this->jsonValues;
    }

    public function getFileCompletion(LanguageFile $baseFile): float
    {
        return 100 - floor(count($this->getMissingKeys($baseFile)) / count($baseFile->getJsonData()) * 100);
    }

    public function getMissingKeys(LanguageFile $baseFile): array
    {
        if ($this->missingKeys !== null) {
            return $this->missingKeys;
        }
        $this->missingKeys = [];
        foreach ($baseFile->getJsonData() as $baseKey => $baseString) {
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
        $this->findInvalidKeys($baseFile);

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
                foreach ($localizedVariables[0] as $localizedVariable) {
                    if ($baseVariable === $localizedVariable) {
                        $found = true;
                    }
                }
                if ($found === false) {
                    $this->addViolation('Key "'.$jsonKey.'" was translated but is missing variable '.$baseVariable);
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
                $this->addViolation('HTML content for "'.$jsonKey.'" does not match base string. Check near "' . $localizedTag->outerHtml() . '"');
            }
        }
    }

    private function findInvalidKeys(LanguageFile $baseFile)
    {
        $baseKeys = $baseFile->getJsonData();
        foreach ($this->getJsonData() as $jsonKey => $jsonData) {
            if (isset($baseKeys[$jsonKey]) === false) {
                $this->addViolation('Key "'.$jsonKey.'" does not exist in base file');
            }
        }
    }

    public function addViolation(string $errorMessage): void
    {
        $this->violations[] = $errorMessage;
    }

    public function hasErrors(): bool
    {
        return !empty($this->violations);
    }

    public function getViolations(): array
    {
        return $this->violations;
    }

    private function parseJson(): void
    {
        $content = file_get_contents($this->filePath);
        $jsonParser = new JsonParser();
        if ($content == '') {
            throw new \InvalidArgumentException($this->filePath.' is not readable or empty.');
        }
        if ($error = $jsonParser->lint($content, JsonParser::DETECT_KEY_CONFLICTS)) {
            echo $this->filePath.': '.$error->getMessage();
            exit(1);

        }
        $this->jsonValues = $jsonParser->parse($content, JsonParser::PARSE_TO_ASSOC);
    }
}