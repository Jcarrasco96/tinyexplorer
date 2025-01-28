<?php

namespace app\services;

use app\core\App;

class Language
{

    private string $code;
    private mixed $languages;

    public function __construct(string $code = 'en')
    {
        $this->code = $code;
        $this->languages = require_once LANGUAGES . "$code.php";
    }

    public function isCode(string $code): bool
    {
        return $this->code === $code;
    }

    public function t(string $key, array $params = []): string
    {
        if (isset($this->languages[$key])) {
            $translation = $this->languages[$key];

            preg_match_all('/\{(.*?)}/', $translation, $matches);

            foreach ($matches[1] as $index => $match) {
                if (isset($params[$index])) {
                    $translation = str_replace("{" . $match . "}", $params[$index], $translation);
                } else {
                    $translation = str_replace("{" . $match . "}", "{" . $match . "}", $translation);
                }
            }

            return $translation;
        }

        App::$logger->warning("Language key: '$key' not found.");
        return $key;
    }

}