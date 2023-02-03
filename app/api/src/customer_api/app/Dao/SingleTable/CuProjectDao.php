<?php

namespace App\Dao\SingleTable;

use App\Models\CuProject;
use App\Dao\DaoConstants;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Collection;
use App\Dao\CuBaseDao;
use App\Common\CodeDefinition;
use Illuminate\Support\Facades\Auth;

class CuProjectDao extends CuBaseDao
{
    /**
     * get construction details
     * @param int $projectId
     *
     * @return collection
     */
    public function getOne($projectId)
    {

        return CuProject::select(
                                    'project_id', 'core_id', 'customer_id', 'customer_branch_id', 'customer_user_id', 'branch_id', 'construction_number', 'site_name',
                                    'site_name_kana', 'zip_code', 'site_prefecture', 'address_cd', 'city_cd', 'site_city', 'site_address', 'latitude', 'longitude',
                                    'project_start_date', 'project_finish_date', 'create_date', 'create_user_id', 'create_system_type',
                                    'update_date', 'update_user_id', 'update_system_type'
                                )
                        ->where('project_id', $projectId)
                        ->where('status', true)
                        ->get();
    }

    /**
     * register a new construction information.
     * @param array $projectData
     *
     * @return int $projectId
     */

    public function create($projectData, $userId)
    {

        $paramInsert = [];

        $paramInsert['customer_id']             = Arr::get($projectData, 'customer_id');
        $paramInsert['customer_branch_id']      = Arr::get($projectData, 'customer_branch_id');
        $paramInsert['customer_user_id']        = Arr::get($projectData, 'customer_user_id');
        $paramInsert['branch_id']               = Arr::get($projectData, 'branch_id');
        $paramInsert['construction_number']     = Arr::get($projectData, 'construction_number');
        $paramInsert['site_name']               = Arr::get($projectData, 'site_name');
        $paramInsert['site_name_kana']          = Arr::get($projectData, 'site_name_kana');
        $paramInsert['zip_code']                = Arr::get($projectData, 'zip_code');
        $paramInsert['site_prefecture']         = Arr::get($projectData, 'site_prefecture');
        $paramInsert['address_cd']              = Arr::get($projectData, 'address_cd');
        $paramInsert['city_cd']                 = Arr::get($projectData, 'city_cd');
        $paramInsert['site_city']               = Arr::get($projectData, 'site_city');
        $paramInsert['site_address']            = Arr::get($projectData, 'site_address');
        $paramInsert['latitude']                = Arr::get($projectData, 'latitude');
        $paramInsert['longitude']               = Arr::get($projectData, 'longitude');
        $paramInsert['project_start_date']      = Arr::get($projectData, 'project_start_date');
        $paramInsert['project_finish_date']     = Arr::get($projectData, 'project_finish_date');
        $paramInsert['create_user_id']          = $userId;
        $paramInsert['create_system_type']      = DaoConstants::CUSTOMER_SYSTEM_TYPE;
        $paramInsert['status']                  = DaoConstants::STATUS_ACTIVE;

        $id = CuProject::create($paramInsert)->project_id;

        return $id;
    }

    /**
     * Update construction information.
     * @param array $projectData
     *
     * @return int $projectId
     */

    public function update($projectData)
    {
        $user = Auth::user();
        $paramUpdate = $this->getProjectData($projectData);

        $paramUpdate['cu_project.update_user_id']          = $user->user_id;
        $paramUpdate['cu_project.update_system_type']      = DaoConstants::CUSTOMER_SYSTEM_TYPE;

        $paramUpdate['cu_project.update_date']             = DB::raw("CURRENT_TIMESTAMP()");

        $query = CuProject::where([
            'project_id' => $projectData['project_id'],
            'status' => DaoConstants::STATUS_ACTIVE
        ]);

        return $this->authorizationHelper->addDataScopeOfProject($query)->update($paramUpdate);
    }

    /**
     * get construction information from parameters.
     * 
     * @param $projectData
     */

    public function getProjectData($projectData)
    {
        $paramUpdate = [];

        $paramUpdate['branch_id']               = Arr::get($projectData, 'branch_id');
        $paramUpdate['construction_number']     = Arr::get($projectData, 'construction_number');
        $paramUpdate['site_name']               = Arr::get($projectData, 'site_name');
        $paramUpdate['site_name_kana']          = Arr::get($projectData, 'site_name_kana');
        $paramUpdate['zip_code']                = Arr::get($projectData, 'zip_code');
        $paramUpdate['site_prefecture']         = Arr::get($projectData, 'site_prefecture');
        $paramUpdate['address_cd']              = Arr::get($projectData, 'address_cd');
        $paramUpdate['city_cd']                 = Arr::get($projectData, 'city_cd');
        $paramUpdate['site_city']               = Arr::get($projectData, 'site_city');
        $paramUpdate['site_address']            = Arr::get($projectData, 'site_address');
        $paramUpdate['latitude']                = Arr::get($projectData, 'latitude');
        $paramUpdate['longitude']               = Arr::get($projectData, 'longitude');
        $paramUpdate['project_start_date']      = Arr::get($projectData, 'project_start_date');
        $paramUpdate['project_finish_date']     = Arr::get($projectData, 'project_finish_date');

        return $paramUpdate;
    }

   
    /**
     * Update construction information.
     */
    public function updateForProjectRequest($projectData, $projectId, $user)
    {

        $listUpdateField = [
            'branch_id',
            'construction_number',
            'site_name',
            'site_name_kana',
            'zip_code',
            'site_prefecture',
            'address_cd',
            'city_cd',
            'site_city',
            'site_address',
            'latitude',
            'longitude',
            'project_start_date',
            'project_finish_date',
        ];

        $paramUpdate = $this->getDataForStatement($listUpdateField, $projectData);

        $paramUpdate['cu_project.update_date'] = DB::raw("CURRENT_TIMESTAMP()");
        $paramUpdate['cu_project.update_user_id'] = $user->user_id;
        $paramUpdate['cu_project.update_system_type'] = DaoConstants::CUSTOMER_SYSTEM_TYPE;

        $query = CuProject::where([
            'project_id' => $projectId,
            'cu_project.status' => DaoConstants::STATUS_ACTIVE
        ]);
       
        return $this->authorizationHelper->addDataScopeOfProject($query)->update($paramUpdate);
    }

    /**
     * Check the access range of the logged-in user to the construction information.
     * @param int $projectId
     * @param $customerId
     * @return int
     */
    public function checkPermissionUserAndProject($projectId, $customerId)
    {
        return CuProject::where([
                ['project_id', $projectId],
                ['customer_id', $customerId]
            ])
            ->count();
    }


    /**
     * Get construction information with project_id.
     */
    public function getProjectInfoByProjectId($projectId) {
        $query = CuProject::where('status', DaoConstants::STATUS_ACTIVE)
                        ->where('project_id', $projectId);

        return $this->authorizationHelper->addDataScopeOfProject($query)->first();
    }

    /**
     * getProgressStatus query
     * 
     * @param requestData
     */

    public function getProgressStatus($requestData)
    {
        $estiamteId = empty($requestData['estimate_id']) ? "NULL": $requestData['estimate_id'];

        return DB::select(DB::raw("select getProgressStatus(". $requestData['request_id'] .",". $estiamteId.") as progress_status;"));
    }
}
