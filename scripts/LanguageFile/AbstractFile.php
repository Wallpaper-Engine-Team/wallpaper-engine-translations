<?php
declare(strict_types=1);

namespace WPE\Localization\LanguageFile;

use Seld\JsonLint\JsonParser;

abstract class AbstractFile implements LanguageFileInterface
{
    private $filePath;
    private $jsonValues;
    private $fileGroup;

    protected function __construct(string $filePath, string $fileGroup)
    {
        $this->filePath = $filePath;
        $this->fileGroup = $fileGroup;
    }

    protected static function parseFileGroup(string $filePath): string
    {
        $groupNamePos = strpos(basename($filePath), '_');
        if ($groupNamePos === false) {
            throw new \InvalidArgumentException('Invalid file name: '.$filePath);
        }

        return substr($filePath, 0, $groupNamePos);
    }

    public function getFileGroup(): string {
        return $this->fileGroup;
    }

    public function getName(): string
    {
        return basename($this->filePath);
    }

    public function getJsonData(): array
    {
        if ($this->jsonValues === null) {
            $this->parseJson();
        }

        return $this->jsonValues;
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