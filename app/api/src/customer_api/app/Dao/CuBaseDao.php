<?php

namespace App\Dao;

use App\Common\AuthorizationHelper;

class CuBaseDao
{
    
    /**
     * @var AuthorizationHelper
     */
    public $authorizationHelper;
    
    /**
     * CuBaseDao constructor.
     *
     */
    public function __construct()
    {
        $this->authorizationHelper = new AuthorizationHelper();
    }
    
    /**
     * Convert special characters.
     * 
     * @param String $string
     * @return string
     */
    public function escapeLike($string)
    {
        $arySearch = array('\\', '%', '_');
        $aryReplace = array('\\\\', '\%', '\_');
        return str_replace($arySearch, $aryReplace, $string);
    }
    
    /**
     * Convert white space characters to%.
     * 
     * @param String $string
     * @return String
     */
    public function convertSpaceToPercent($string)
    {
        $pattern = "/( |　)+/";
        $replacement = '%';
        return preg_replace($pattern, $replacement, $string);
    }

    /** format data. */
    public function getDataForStatement($listField, $data, $tableName = null) {
        $result = [];
        $tableName = !empty($tableName) ? $tableName . "." : "";
        foreach($listField as $field) {
            if (array_key_exists($field, $data)) {
                $result[$tableName . $field] = $data[$field];
            }
        }
        return $result;
    }
}
