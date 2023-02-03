<?php

namespace App\Common;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class PhpSpreadsheetHelper
{
    /**
     * @return Csv
     */
    public static function initReaderCsv()
    {
        $reader = new Csv();
        $reader->setInputEncoding('SJIS');
        $reader->setDelimiter(';');
        $reader->setEnclosure('');
        $reader->setSheetIndex(0);
        return $reader;
    }

    /**
     * Upload the file to temporary memory.
     *
     * @param $file
     * @return string
     */
    public static function uploadTmpDirectory($file): string
    {
        $tmpPath = 'tmp_upload/' . $file->hashName();
        Storage::disk('public')->put($tmpPath, file_get_contents($file));

        return $tmpPath;
    }

    /**
     * Get file path information from temporary memory.
     *
     * @param $tmpPath
     * @return string
     */
    public static function getPathFilePublic($tmpPath)
    {
        return public_path() . '/storage/' . $tmpPath;
    }

    /**
     * Delete the file information in the temporary memory.
     *
     * @param $tmpPath
     */
    public static function removeFileTmp($tmpPath)
    {
        Storage::disk('public')->delete($tmpPath);
    }
}
