<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Account\InitData\InitDataInterface;
use Netric\FileSystem\FileSystem;

/**
 * Initializer to make sure the account root folder exists
 */
class RootFolderInitData implements InitDataInterface
{
    /**
     * File system to create folders
     */
    private FileSystem $fileSystem;

    /**
     * Constructor
     *
     * @param FileSystem $fileSystem
     */
    public function __construct(FileSystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Insert or update initial data for account
     *
     * @param Account $account
     * @return bool
     */
    public function setInitialData(Account $account): bool
    {
        $this->fileSystem->setRootFolder($account->getSystemUser());
        return true;
    }
}
