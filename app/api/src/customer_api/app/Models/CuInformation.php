<?php

namespace App\Models;

use App\Common\CodeDefinition;
use App\Models\Model as AppModel;
use Psy\Context;
use Illuminate\Support\Facades\DB;
use App\Dao\DaoConstants;

/**
 * Class CuInformation
 * @property integer $information_id
 * @property Context $subject
 * @property Context $body
 * @property string $image_url
 * @property string $thumbnail_url
 * @property datetime $start_date
 * @property datetime $end_date
 * @property boolean $display_header
 * @property boolean $display_advertisement
 */
class CuInformation extends AppModel
{
    //
    protected $table = 'cu_information';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'information_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'information_id',
        'subject',
        'body',
        'image_url',
        'thumbnail_url',
        'start_date',
        'end_date',
        'display_header',
        'display_advertisement'
    ];

    public function scopeDefaultJoin($query, $user)
    {
        $userId = $user->user_id;
        return $query->leftJoin('cu_user_information_status', function ($join) use ($userId) {
                $join->on('cu_information.information_id', '=', 'cu_user_information_status.information_id');
                $join->on('cu_user_information_status.user_id', '=', DB::raw($userId));
                $join->on('cu_user_information_status.status', '=', DB::raw(DaoConstants::STATUS_ACTIVE));
            })
            ->leftJoin('cu_information_target', 'cu_information.information_id', '=', 'cu_information_target.information_id');
    }

    public function scopeDataTypeCheck($query, $user)
    {
        return $query->where(function ($subQuery) use ($user) {
            $subQuery->where('cu_information.data_type', CodeDefinition::DATA_TYPE_ALL)
            ->orWhere(function ($cQuery) use ($user) {
                return $cQuery->where('cu_information.data_type', CodeDefinition::DATA_TYPE_CUSTOMER_ID)
                ->where('cu_information_target.customer_id', $user->customer_id);
            })
            ->orWhere(function ($cQuery) use ($user) {
                return $cQuery->where('cu_information.data_type', CodeDefinition::DATA_TYPE_CUSTOMER_BRANCH_ID)
                ->where('cu_information_target.customer_id', $user->customer_id)
                ->where('cu_information_target.customer_branch_id', $user->customer_branch_id);
            })
            ->orWhere(function ($cQuery) use ($user) {
                return $cQuery->where('cu_information.data_type', CodeDefinition::DATA_TYPE_CUSTOMER_USER_ID)
                ->where('cu_information_target.customer_id', $user->customer_id)
                ->where('cu_information_target.customer_branch_id', $user->customer_branch_id)
                ->where('cu_information_target.customer_user_id', $user->customer_user_id);
            });
        });
    }

    public function scopeDataTypeCheckList($query, $user)
    {
        return $query->where(function ($subQuery) use ($user) {
            $subQuery->where('cu_information.data_type', CodeDefinition::DATA_TYPE_ALL)
                ->orWhere(function ($cQuery) use ($user) {
                    return $cQuery->where('cu_information.data_type', CodeDefinition::DATA_TYPE_CUSTOMER_ID)
                        ->where('cu_information_target.customer_id', $user->customer_id)
                        ->where('cu_information_target.status', DaoConstants::STATUS_ACTIVE);
                })
                ->orWhere(function ($cQuery) use ($user) {
                    return $cQuery->where('cu_information.data_type', CodeDefinition::DATA_TYPE_CUSTOMER_BRANCH_ID)
                        ->where('cu_information_target.customer_id', $user->customer_id)
                        ->where('cu_information_target.customer_branch_id', $user->customer_branch_id)
                        ->where('cu_information_target.status', DaoConstants::STATUS_ACTIVE);
                })
                ->orWhere(function ($cQuery) use ($user) {
                    return $cQuery->where('cu_information.data_type', CodeDefinition::DATA_TYPE_CUSTOMER_USER_ID)
                        ->where('cu_information_target.customer_id', $user->customer_id)
                        ->where('cu_information_target.customer_branch_id', $user->customer_branch_id)
                        ->where('cu_information_target.customer_user_id', $user->customer_user_id)
                        ->where('cu_information_target.status', DaoConstants::STATUS_ACTIVE);
                });
        });
    }
}
