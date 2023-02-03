<?php

namespace App\Services;

/**
 * Base service class
 */
class Service
{

    /**
     * Convert data from UTF-8 into Shift-JIS
     *
     * @param array $aryData
     * @return string[]
     */
    public function convertDataFromUtf8ToSjis($aryData) {
        $result = [];
        foreach ($aryData as $data) {
            $result[] = mb_convert_encoding($data, 'SJIS-win', 'UTF-8');
        }

        return $result;
    }


    /**
     * Download data
     * @param Array $aryColumns
     * @param Array $aryData
     * @param String $fileName
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadData($aryColumns, $aryData, $fileName)
    {
        $headers = array(
            "Content-type" => "text/csv; charset=Shift-JIS",
            "Content-Encoding" => "Shift-JIS",
            "Content-Transfer-Encoding" => "binary",
            "Content-Disposition" => "attachment; filename={$fileName}"
        );

        $callback = function() use ($aryData, $aryColumns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $this->convertDataFromUtf8ToSjis(array_values($aryColumns)));

            $aryKey = array_keys($aryColumns);
            foreach($aryData as $data) {

                $row = [];
                foreach ($aryKey as $colmn) {
                    $row[] = (isset($data[$colmn])) ? $data[$colmn] : "";
                }

                fputcsv($file, $this->convertDataFromUtf8ToSjis($row));
            }
            fclose($file);
        };

        return Response()->streamDownload($callback, $fileName, $headers);
    }

    public function removeEmailDomain($email)
    {
        return !empty($email) ? substr_replace($email, '********', 3, strpos($email, '@') - 2) : "";
    }
}
