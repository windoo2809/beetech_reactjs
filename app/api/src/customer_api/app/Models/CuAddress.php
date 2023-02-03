<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CuAddress
 * @property string $address_cd
 * @property string $prefecture_cd
 * @property string $city_cd
 * @property string $town_cd
 * @property string $zip_cd
 * @property string $company_flg
 * @property string $delete_flg
 * @property string $prefecture_name
 * @property string $prefecture_kana
 * @property string $city_name
 * @property string $city_kana
 * @property string $town_name
 * @property string $town_kana
 * @property string $town_info
 * @property string $kyoto_street_name
 * @property string $street_name
 * @property string $street_kana
 * @property string $information
 * @property string $company_name
 * @property string $company_kana
 * @property string $company_address
 * @property string $new_address_cd
 */

class CuAddress extends Model
{
    protected $table = 'cu_address';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'address_cd';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'address_cd',
        'prefecture_cd',
        'city_cd',
        'town_cd',
        'zip_cd',
        'company_flg',
        'delete_flg',
        'prefecture_name',
        'prefecture_kana',
        'city_name',
        'city_kana',
        'town_name',
        'town_kana',
        'town_info',
        'kyoto_street_name',
        'street_name',
        'street_kana',
        'information',
        'company_name',
        'company_kana',
        'company_address',
        'new_address_cd'
    ];

    public function scopeSelectForList($query) {
        return $query->select([
            "address_cd",
            "prefecture_cd",
            "city_cd",
            "town_cd",
            "zip_cd",
            "company_flg",
            "delete_flg",
            "prefecture_name",
            "prefecture_kana",
            "city_name",
            "city_kana",
            "town_name",
            "town_kana",
            "town_info",
            "kyoto_street_name",
            "street_name",
            "street_kana",
            "information",
            "company_name",
            "company_kana",
            "company_address",
            "new_address_cd",
        ]);
    }
}
