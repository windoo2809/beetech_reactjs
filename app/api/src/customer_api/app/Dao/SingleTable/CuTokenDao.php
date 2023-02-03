<?php

namespace App\Dao\SingleTable;

use App\Dao\DaoConstants;
use App\Models\CuToken;
use Carbon\Carbon;
use App\Dao\CuBaseDao;

class CuTokenDao extends CuBaseDao
{
    /**
     * create new one-time token
     * @param $userID
     * @param $token
     * @return mixed
     */
    public function createNewToken($userID, $token)
    {
        $cuToken = new CuToken();
        $cuToken->user_id = $userID;
        $cuToken->token = $token;
        $cuToken->expire = Carbon::now()->addHour(DaoConstants::CU_TOKEN_DEFAULT_TIME_EXTEND);
        $cuToken->save();
        return $cuToken;
    }
    /**
     * update one-time token
     * @param $userID
     * @param $token
     * @return mixed
     */
    public function updateNewToken($userID, $token)
    {
        $cuToken = CuToken::where('user_id', $userID)
            ->latest('token_id')
            ->first();
        $cuToken->token = $token;
        $cuToken->expire = Carbon::now()->addHour(DaoConstants::CU_TOKEN_DEFAULT_TIME_EXTEND);
        $cuToken->save();
        return $cuToken;
    }

    /**
     * delete token
     * @param $token
     * @return mixed
     */
    public function deleteToken( $token )
    {
        $res =  CuToken::where('token', $token)->first();
        $res->delete();
        return $res;
    }

    /** get token details
     * @param $token
     * @return mixed
     */
    public function getInfoByToken( $token )
    {
        return  CuToken::select('user_id')->where('token', $token)
            ->whereDate('expire', '>=', Carbon::now())
            ->latest('token_id')
            ->first();
    }

    /** get token by user id
     * @param $userId
     * @return mixed
     */
    public function getTokenByUserId( $userId )
    {
        return  CuToken::where('user_id', $userId)->get();
    }

    /** delete token
     * @param $userId
     * @return mixed
     */
    public function deleteTokenByUserId( $userId )
    {
        return  CuToken::where('user_id', $userId)->delete();
    }
}
