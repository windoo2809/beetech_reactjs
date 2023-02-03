<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{
    const REGEX_INT = "/^[0-9]*$/";

    const REGEX_TINYINT = "/^[0-9+]$/";

    const REGEX_ZIP_CODE = "/^[0-9]{3}-[0-9]{4}$/";

    const REGEX_FAX = "/^[0-9]{10,11}$/";

    const REGEX_FILE_PATH = "/^[a-zA-Z0-9\/\.]+$/";

    const REGEX_KATAKANA = "/^[ァ-ヴ][ァ-ヴー]*([・＝　\s][ァ-ヴ][ァ-ヴー]*)*$/u";

    const REGEX_PHONE_NUMBER = "/^[0-9]{10,11}$/";

    // format 000-0000-0000
    const REGEX_PHONE_NUMBER_CSV = "/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/";

    const REGEX_URL = "/^https?://[\w!?/+\-_~;.,*&@#$%()'[\]]+$/";

    const REGEX_EMAIL = "/^[\w\d._+-]+@[\w\d_-]+\.[\w\d._-]+$/";

    const REGEX_PASSWORD = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*[+=!\"#$%&\'()*,\\-.\\/:;<>?@\\[\\\\\\]\\^_`{|}~])[a-zA-Z0-9+=!\"#$%&\'()*,\\-.\\/:;<>?@\\[\\\\\\]\\^_`{|}~]{8,32}$/";

    // YYYY-MM-DD
    const REGEX_DATE_VALID_023 = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/";

    // YYYY/MM/DD
    const REGEX_DATE_VALID_024 = "/^[0-9]{4}/(0[1-9]|1[0-2])/(0[1-9]|[12][0-9]|3[01])$/";

    // YYYY-MM-DD hh:mm
    const REGEX_DATETIME_VALID_026 = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])( |　)[0-9]{2}:[0-9]{2}$/";

    // YYYY/MM/DD hh:mm
    const REGEX_DATETIME_VALID_027 = "/^[0-9]{4}/(0[1-9]|1[0-2])/(0[1-9]|[12][0-9]|3[01])( |　)[0-9]{2}:[0-9]{2}$/";

    // YYYY-MM
    const REGEX_MONTH_VALID_029 = "/^[0-9]{4}-(0[1-9]|1[0-2])$/";

    // YYYY/MM
    const REGEX_MONTH_VALID_030 = "/^[0-9]{4}/(0[1-9]|1[0-2])$/";

    // yyyy-Www
    const REGEX_WEEK = "/^[0-9]{4}-W([0-5][0-9])$/";

    // hh:mm
    const REGEX_TIME = "/^([01][0-9]|2[0-3]):[0-5][0-9]$/";

    // regex character and number halfwidth
    const REGEX_CUSTOMER_LOGIN_ID = "/^[a-zA-Z0-9]+$/";

    // regex character and number halfwidth
    const REGEX_LIST_EMAIL = "/^([\w.+-]+@[\w-]+\.[\w.-]*)(,[\s]{0,1}+[\w.+-]+@[\w-]+\.[\w.-]*)*(,{0,1})$/";

    public function getCustomMessage($attributes, $type, $prefix)
    {
        $result = [];
        foreach ($attributes as $attr) {
            $item = [
                $attr . "." . $type => __("validation.custom." . $prefix)
            ];
            $result = array_merge($result, $item);
        }

        return $result;
    }

    public function prepareForValidation()
    {
        $this->merge($this->route()->parameters());
    }

    /**
     * format numeric before validation
     */
    protected function formatNumericAttr($sourceData, $attrList)
    {
        foreach ($attrList as $attr) {
            if (is_array($sourceData) && isset($sourceData[$attr])) {
                $qty = mb_convert_kana($sourceData[$attr], "n", "utf-8");
                $sourceData[$attr] = $qty;
            }
        }
        return $sourceData;
    }
}
