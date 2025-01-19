<?php

namespace app\services;

use ZipArchive;

class Zip
{

    public string $path = '';
    private ZipArchive $zip;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->zip = new ZipArchive();
    }

    public function open(): int|bool
    {
        return $this->zip->open($this->path, ZipArchive::CREATE);
    }

    public function close(): int|bool
    {
        return $this->zip->close();
    }

    public function addFolder(string $folder, string $parentFolder = ''): void
    {
        $folder = rtrim($folder, '/');
        $folderName = basename($folder);
        $zipPath = $parentFolder ? $parentFolder . '/' . $folderName : $folderName;

        $this->zip->addEmptyDir($zipPath);

        $files = scandir($folder);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $folder . '/' . $file;

            if (is_dir($filePath)) {
                $this->addFolder($filePath, $zipPath);
            } else {
                $this->zip->addFile($filePath, $zipPath . '/' . $file);
            }
        }
    }

}