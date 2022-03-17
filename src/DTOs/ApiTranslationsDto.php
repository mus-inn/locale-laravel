<?php

namespace Localizy\LocalizyLaravel\DTOs;


class ApiTranslationsDto
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
}
