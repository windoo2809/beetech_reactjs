<?php

use App\Common\CodeDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api', 'middleware' => 'api'], function () {
    Route::group(['middleware' => 'common_log_begin'], function () {

        /*
        |--------------------------------------------------------------------------
        | Public (Before Log In)
        |--------------------------------------------------------------------------
        */
        // Auth Routes
        // --------------------------------------------------------------------------
        Route::post('/login', 'AuthController@authenticate')->name('login.default'); //API_AUTH_0100

        // User Lock Routes
        // --------------------------------------------------------------------------
        Route::put('/user/lock', 'AuthController@updateLock')->name('login.lock'); //API_AUTH_0330

        // Check Token Routes
        // --------------------------------------------------------------------------
        Route::post('/token/check', 'CuTokenController@checkToken')->name('cu_token.check'); //API-AUTH-0340
        
        /*
        |--------------------------------------------------------------------------
        | Private (After Logged In)
        |--------------------------------------------------------------------------
        */
        Route::group(['middleware' => 'user_is_valid'], function () {


            // Logout Routes
            // --------------------------------------------------------------------------
            Route::put('/logout', 'AuthController@logout')->name('auth.logout');

            /*
            |--------------------------------------------------------------------------
            | Group Role 0,1,2,3,4 Routes
            |--------------------------------------------------------------------------
            */
            Route::group(['middleware' => 'role:' . CodeDefinition::ROLE_SUPER_USER ."|". CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR ."|"
                . CodeDefinition::ROLE_APPROVER . "|" . CodeDefinition::ROLE_ACCOUNTANT . "|" . CodeDefinition::ROLE_PERSON_IN_CHARGE], function () {

            });

            /*
            |--------------------------------------------------------------------------
            | Role 0 Routes
            |--------------------------------------------------------------------------
            */
            Route::group(['middleware' => 'role:'.CodeDefinition::ROLE_SUPER_USER], function () {
                
            });

            /*
            |--------------------------------------------------------------------------
            | Role 0,1 Routes
            |--------------------------------------------------------------------------
            */
            Route::group(['middleware' => 'role:'. CodeDefinition::ROLE_SUPER_USER . "|" . CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR], function () {

            });

            /*
            |--------------------------------------------------------------------------
            | Role 0,1,2,3 Routes
            |--------------------------------------------------------------------------
            */
            Route::group(['middleware' => 'role:' . CodeDefinition::ROLE_SUPER_USER ."|". CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR ."|". CodeDefinition::ROLE_APPROVER ."|". CodeDefinition::ROLE_ACCOUNTANT], function () {


            });

            /*
            |--------------------------------------------------------------------------
            | Role 0,1,2 Routes
            |--------------------------------------------------------------------------
            */
            Route::group(['middleware' => 'role:' . CodeDefinition::ROLE_SUPER_USER ."|". CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR ."|". CodeDefinition::ROLE_APPROVER ], function () {


            });

            /*
            |--------------------------------------------------------------------------
            | Role 0,1,2,4 Routes
            |--------------------------------------------------------------------------
            */
            Route::group(['middleware' => 'role:' . CodeDefinition::ROLE_SUPER_USER ."|". CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR ."|". CodeDefinition::ROLE_APPROVER ."|". CodeDefinition::ROLE_PERSON_IN_CHARGE], function () {

                
            });

        });

    });
});
