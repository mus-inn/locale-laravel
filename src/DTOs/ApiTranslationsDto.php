<?php

namespace UseLocale\LocaleLaravel\DTOs;

use JsonSerializable;

class ApiTranslationsDto implements JsonSerializable
{
    public string $locale;
    public array $jsonData;
    public array $phpData;

    /**
     * @param string $locale
     * @param array<string, string> $jsonData
     * @param array<string, string> $phpData
     */
    public function __construct(string $locale, array $jsonData, array $phpData)
    {
        $this->locale = $locale;
        $this->jsonData = $jsonData;
        $this->phpData = $phpData;
    }

    public function jsonSerialize(): array
    {
        return [
            $this->locale,
            $this->jsonData,
            $this->phpData,
        ];
    }
}
