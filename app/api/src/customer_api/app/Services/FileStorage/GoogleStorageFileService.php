<?php 

namespace App\Services\FileStorage;

use App\Common\CodeDefinition;
use Google\Cloud\Storage\StorageClient;
use App\Services\Interfaces\GoogleStorageFileServiceInterface;
use Carbon\Carbon;

class GoogleStorageFileService implements GoogleStorageFileServiceInterface
{

    protected $storage;
    protected $storageClient;

    public function __construct()
    {
        $this->storageClient = new StorageClient([
            'keyFile' => json_decode(file_get_contents(config('filesystems.google_account_key')), true)
        ]);

        $this->storage = $this->storageClient->bucket(config('filesystems.google_storage_file_bucket'));
    }

    public function putFile($file, $path, $isPublic = false) 
    {
        $config = [
            'name' => $path
        ];

        // if ($isPublic) {
        //     $config['predefinedAcl'] = 'publicRead';
        // }
        
        return $this->storage->upload(fopen($file, 'r'), $config);
    }


    public function delete($path)
    {
        $object = $this->storage->object($path);
        return $object->delete();
    }

    public function getFullUrl($path)
    {
        return config('filesystems.google_storage_url'). config('filesystems.google_storage_file_bucket') . '/' . $path;
    }

    public function listObjects($prefix)
    {
        return $this->storage->objects([
            'prefix' => $prefix,
            'maxResults' => CodeDefinition::MAX_FILE_UPLOAD_ON_DAY + 2
        ]);
    }

    public function countFileInFolder($prefix)
    {
        $listObject = $this->listObjects($prefix);

        $listFileName = [];
        
        foreach ($listObject as $object) {
            array_push($listFileName, $object->name());
        }
        
        if (!count($listFileName)) {
            return 0;
        }
        
        $lastFileName = end($listFileName);
        $filePath = explode("/", $lastFileName);

        $name = end($filePath);
        $fileName = explode(".", $name);

        $count = intval(substr($fileName[0], strlen(CodeDefinition::FILE_NAME_PREFIX)));

        return $count + 1;
    }

    public function getPathForUpload($file, $user) {
        $now = Carbon::now()->format('Y/m/d');
        $folderDir = $user->customer_id . "/" . $user->customer_branch_id . "/" . $user->customer_user_id . "/" . $now . "/";

        $countFile = $this->countFileInFolder($folderDir);

        if ($countFile < CodeDefinition::MAX_FILE_UPLOAD_ON_DAY) {
            
            $extension = $file->getClientOriginalExtension();

            $fileName = CodeDefinition::FILE_NAME_PREFIX. str_pad($countFile, 3, "0", STR_PAD_LEFT) . "." . $extension;

            return $folderDir . $fileName;
        }

        return null;
    }

    public function getObject($path) {
        return  $this->storage->object($path);
    }

    public function downloadFile($fileObject) {
        $file = $fileObject->downloadAsStream();
        
        return $file->getContents();
    }

    public function createPathForUpload($fileName, $user) {
        $now = Carbon::now()->format('Y/m/d');
        $folderDir = $user->customer_id . "/" . $user->customer_branch_id . "/" . $user->customer_user_id . "/" . $now . "/";
        return $folderDir . $fileName;
    }

    public function uploadFile($contentFile, $path){
        $config = [
            'name' => $path
        ];
        
        return $this->storage->upload($contentFile, $config);
    }
}