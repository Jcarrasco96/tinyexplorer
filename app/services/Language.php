<?php

namespace app\services;

use app\core\App;

class Language
{

    public string $lngCode;
    public mixed $lng = [];

    public function __construct(string $code = 'en')
    {
        $this->lngCode = $code;
        $this->lng = require_once LANGUAGES . "$code.php";
    }

    public function setLanguage(string $code): void
    {
        if (file_exists(LANGUAGES . "$code.php")) {
            $this->lngCode = $code;
            $this->lng = require_once LANGUAGES . "$code.php";
        } else {
            App::$logger->error('Language file not found: ' . $code);
        }
    }

    public function t(string $key, array $params = []): string
    {
        if (isset($this->lng['t'][$key])) {
            $translation = $this->lng['t'][$key];

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

        App::$logger->error('Language return key: ' . $key);
        return $key;
    }

}