<?php

namespace Netric\Mail;

use Netric\Account\Account;
use Netric\Account\AccountContainer;
use Netric\Account\AccountContainerInterface;
use Netric\EntityQuery\EntityQuery;
use Netric\Error\AbstractHasErrors;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\FileSystem\FileSystem;
use Netric\Log\Log;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Mail\Exception\AddressNotFoundException;
use Netric\Entity\EntityLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Mail\Maildrop\MaildropContainer;

/**
 * Service responsible for delivering messages into netric
 */
class DeliveryService extends AbstractHasErrors
{
    /**
     * Entity loader
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Index for querying entities
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * Mailsystem service used to interact with email
     */
    private MailSystemInterface $mailSystem;

    /**
     * Account loader
     *
     * @var AccountContainer
     */
    private AccountContainerInterface $accountContainer;

    /**
     * The container used to get maildrop drivers for delivering mail
     *
     * @var MaildropContainer
     */
    private MaildropContainer $maildropContainer;

    /**
     * Construct the transport service
     *
     * @param Log $log
     * @param EntityLoader $entityLoader Loader to get and save messages
     * @param GroupingLoader $groupingLoader For loading mailbox groupings
     * @param IndexInterface $entityIndex The index for querying entities,
     * @param FileSystem $fileSystem For saving attachments
     */
    public function __construct(
        MailSystemInterface $mailSystem,
        MaildropContainer $maildropContainer,
        EntityLoader $entityLoader,
        IndexInterface $entityIndex,
        AccountContainerInterface $accountContainer
    ) {
        $this->entityLoader = $entityLoader;
        $this->entityIndex = $entityIndex;
        $this->mailSystem = $mailSystem;
        $this->accountContainer = $accountContainer;
        $this->maildropContainer = $maildropContainer;

        if (!function_exists('mailparse_msg_parse')) {
            throw new \RuntimeException("'pecl/mailparse' is a required extension.");
        }
    }

    /**
     * Import a message from a remote server into a netric entity
     *
     * @param string $emailAddress The address to deliver to
     * @param string $filePath Path to the file containing the message to import
     * @return int The imported message id, 0 on failure, and -1 if already imported
     */
    public function deliverMessageFromFile(
        string $emailAddress,
        string $filePath
    ): string {

        // First thing we do is try to get the account if there is one associated with the domain
        $account = $this->getNetricAccountFromAddress($emailAddress);

        // First get the email account from the address
        $emailAccount = $this->getEmailAccountFromAddress($emailAddress, $account->getAccountId());
        if (!$emailAccount) {
            throw new AddressNotFoundException("$emailAddress not found");
        }

        // Get the maildrop driver
        $maildrop = $this->maildropContainer->getMaildropForEmailAccount($emailAccount);
        return $maildrop->createEntityFromMessage($filePath, $emailAccount);
    }

    /**
     * Get an email account from an address if it exists
     *
     * @param string $emailAddress
     * @param string $accountId
     * @return EmailAccountEntity|null
     */
    private function getEmailAccountFromAddress(string $emailAddress, string $accountId): ?EmailAccountEntity
    {
        // Check for comment-reply dropbox which is comment.[UUID]@anyaccountdomain
        // The reason we use any domain, is because they could change the default
        // but we'd still want sent emails to be able to be replied to
        preg_match('!comment.([a-z0-9\-]*)@!i', $emailAddress, $matches);
        if (isset($matches[0]) && isset($matches[1])) {
            // Create a dynamic email account (we never save it though)
            $emailAccount = $this->entityLoader->create(ObjectTypes::EMAIL_ACCOUNT, $accountId);
            $emailAccount->setValue('type', 'dropbox');
            $emailAccount->setvalue('address', $emailAddress);
            $emailAccount->setValue('dropbox_create_type', 'comment');
            $emailAccount->setValue('dropbox_obj_reference', $matches[1]);
            return $emailAccount;
        }

        // Query email accounts for unique email address
        $query = new EntityQuery(ObjectTypes::EMAIL_ACCOUNT, $accountId);
        $query->where("address")->equals($emailAddress);
        $result = $this->entityIndex->executeQuery($query);
        $num = $result->getNum();
        if (!$num) {
            return null;
        }

        return $result->getEntity(0);
    }

    /**
     * Get the netric account for an email address
     */
    private function getNetricAccountFromAddress(string $emailAddress): ?Account
    {
        // Split out email address to get %user% and %domain%
        $addressParts = explode('@', $emailAddress);
        if (!isset($addressParts[1])) {
            return null;
        }

        // Ge tthe account ID form the domain
        $accountId = $this->mailSystem->getAccountIdFromDomain($addressParts[1]);
        if (!$accountId) {
            return null;
        }

        // Return the account
        return $this->accountContainer->loadById($accountId);
    }
}
