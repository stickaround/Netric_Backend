<?php
/**
 * Copy any local files up to a file server - mogilefs
 *
 * We moved everything to point to mogilefs in production. In the future we
 * may want to make a general purpose tool for moving from one store to another, but for
 * now this is a one-time shot and all installations must stay on the store they started
 * or they will lose data.
 */
$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get("Netric/Db/Db");
$config = $serviceManager->get("Netric/Config/Config");
$entityLoader = $serviceManager->get("Netric/EntityLoader");
$entityIndex = $serviceManager->get("Netric/EntityQuery/Index/Index");
$localStore = $serviceManager->get("Netric/FileSystem/FileStore/LocalFileStore");
$remoteStore = $serviceManager->get("Netric/FileSystem/FileStore/FileStore");

/*
 * If the store is not local then we need to upload any local files
 */
if ($localStore != $remoteStore) {
    // Undeleted
    $query = new \Netric\EntityQuery("file");
    $query->where("dat_ans_key")->equals("");
    $query->andWhere('dat_local_path')->doesNotEqual("");

    // Move undeleted files
    $result = $entityIndex->executeQuery($query);
    $num = $result->getTotalNum();
    for ($i = 0; $i < $num; $i++) {
        $file = $result->getEntity($i);

        updates_uploadLoadFileToRemoteStore(
            $config,
            $account,
            $localStore,
            $remoteStore,
            $file
        );
    }

    // Now move deleted
    $query->andWhere("f_deleted")->equals(true);
    $result = $entityIndex->executeQuery($query);
    $num = $result->getTotalNum();
    for ($i = 0; $i < $num; $i++) {
        $file = $result->getEntity($i);

        updates_uploadLoadFileToRemoteStore(
            $config,
            $account,
            $localStore,
            $remoteStore,
            $file
        );
    }
}

/**
 * Function to upload a file from the local file system to the remote store.
 *
 * @param $config
 * @param $account
 * @param $localStore
 * @param $remoteStore
 * @param $file
 */
function updates_uploadLoadFileToRemoteStore($config, $account, $localStore, $remoteStore, $file) {
    $fileName = $config->data_path . DIRECTORY_SEPARATOR . "tmp"  . DIRECTORY_SEPARATOR;
    $fileName .= "file-" . $account->getId() . "-" . $file->getId() . "-" . $file->getValue('revision');

    // Copy to the temp file
    file_put_contents($fileName, $localStore->readFile($file));

    if (filesize($fileName) <= 0) {
        throw new RuntimeException(
            "Failed to copy file to local temp file: " .
            $file->getId() . ":" .
            $file->getName() . " - " . $file->getValue("dat_local_path")
        );
    }

    // Save the file to the remote store
    if ($remoteStore->uploadFile($file, $fileName)) {
        // Cleanup
        $localStore->deleteFile($file);
        unlink($fileName);
    } else {
        throw new RuntimeException("Could not upload: " . $file->getId() . ":" . $file->getName());
    }
}
