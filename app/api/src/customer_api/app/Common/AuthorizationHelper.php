<?php

namespace App\Common;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class AuthorizationHelper
{

    /**
     * Create a query for check the data access range of construction information.
     */
    public function addDataScopeOfProject($query) {

        $user = Auth::user();

        $dataScope = $this->getDataScope();

        if ($dataScope === CodeDefinition::DATA_SCOPE_ALL) {
            $query->where('cu_project.customer_id', $user->customer_id);
        } else if ($dataScope === CodeDefinition::DATA_SCOPE_BRANCH_UNIT) {
            $query->where('cu_project.customer_id', $user->customer_id);
            $query->where('cu_project.customer_branch_id', $user->customer_branch_id);
        } else if ($dataScope ===  CodeDefinition::DATA_SCOPE_PERSON_IN_CHARGE) {
            $query->where('cu_project.customer_id', $user->customer_id);
            $query->where('cu_project.customer_branch_id', $user->customer_branch_id);
            $query->where('cu_project.customer_user_id', $user->customer_user_id);
        } else {
            $query->whereRaw('FALSE');
        }

        return $query;
    }

    /**
     * Create a query for check the data access range of construction information.
     */
    public function checkDataScopeOfProject($query) {
        $user = Auth::user();

        $dataScope = $this->getDataScope();

        if ($dataScope === CodeDefinition::DATA_SCOPE_ALL) {
            $query->where('v_project_list.customer_id', $user->customer_id);
        } else if ($dataScope === CodeDefinition::DATA_SCOPE_BRANCH_UNIT) {
            $query->where('v_project_list.customer_id', $user->customer_id);
            $query->where('v_project_list.customer_branch_id', $user->customer_branch_id);
        } else if ($dataScope ===  CodeDefinition::DATA_SCOPE_PERSON_IN_CHARGE) {
            $query->where('v_project_list.customer_id', $user->customer_id);
            $query->where('v_project_list.customer_branch_id', $user->customer_branch_id);
            $query->where('v_project_list.customer_user_id', $user->customer_user_id);
        } else {
            $query->whereRaw('FALSE');
        }

        return $query;
    }

    /**
     *  Create a query for check the data access range of cu_information.
     */
    public function addDataScopeOfInformation($query) {
        $user = Auth::user();

        $dataScope = $this->getDataScope();

        if ($dataScope === CodeDefinition::DATA_SCOPE_ALL) {
            $query->where('cu_information_target.customer_id', $user->customer_id);
        } else if ($dataScope === CodeDefinition::DATA_SCOPE_BRANCH_UNIT) {
            $query->where('cu_information_target.customer_id', $user->customer_id);
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('cu_information_target.customer_branch_id', $user->customer_branch_id)
                    ->orWhereNull('cu_information_target.customer_branch_id');
            });
        } else if ($dataScope ===  CodeDefinition::DATA_SCOPE_PERSON_IN_CHARGE) {
            $query->where('cu_information_target.customer_id', $user->customer_id);
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('cu_information_target.customer_branch_id', $user->customer_branch_id)
                    ->orWhereNull('cu_information_target.customer_branch_id');
            });
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('cu_information_target.customer_user_id', $user->customer_user_id)
                    ->orWhereNull('cu_information_target.customer_user_id');
            });
        } else {
            $query->whereRaw('FALSE');
        }
        return $query;
    }

    /**
     * Create a query for check the data access range of user information.
     */
    public function addDataScopeOfUserInfo($query)
    {
        $user = Auth::user();
        $query->where('cu_user.customer_id', $user->customer_id);

        $query->where(function ($subQuery) use ($user) {
            $subQuery->whereIn(DB::raw("(SELECT cu.role FROM cu_user cu WHERE cu.user_id = " . $user->user_id .")"),[CodeDefinition::ROLE_SUPER_USER, CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR])
                ->orWhere("cu_user.user_id", $user->user_id);
        });

        return $query;
    }

    /**
     * get data scope of user
     */
    public function getDataScope() {
        $user = Auth::user();
        $dataScope = DB::select(DB::raw("SELECT getDataScope(". $user->user_id .") AS data_scope"));

        return !empty($dataScope[0]) ? $dataScope[0]->data_scope : null;
    }
}
