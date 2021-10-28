<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Account\InitData\InitDataInterface;
use Netric\Mail\MailSystemInterface;

/**
 * Initializer to make sure default email domains are setup
 */
class EmailDomainInitData implements InitDataInterface
{
    /**
     * Mailsystem for getting domain defaults
     *
     * @var MailSystemInterface
     */
    private MailSystemInterface $mailSystem;

    /**
     * Constructor
     */
    public function __construct(
        MailSystemInterface $mailSytem
    ) {
        $this->mailSystem = $mailSytem;
    }

    /**
     * Insert or update initial data for account
     *
     * @param Account $account
     * @return bool
     */
    public function setInitialData(Account $account): bool
    {
        // Get the system dynamic domain for this account - something like [accountname].netric.com
        $accountSysDomain = $this->mailSystem->getAccountDynamicSystemDomain($account->getAccountId());

        // Add the domain if it does not already exist
        $this->mailSystem->addDomain($account->getAccountId(), $accountSysDomain);
        return true;
    }
}
