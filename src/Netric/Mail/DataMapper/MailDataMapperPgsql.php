<?php

declare(strict_types=1);

namespace Netric\Mail\DataMapper;

use Netric\Db\Relational\RelationalDbContainerInterface;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Mail\Exception\DomainOwnedByAnotherAccountException;

class MailDataMapperPgsql implements MailDataMapperInterface
{
    private RelationalDbContainerInterface $rdbContainer;

    /**
     * Constructor
     *
     * @param RelationalDbContainerInterface $rdbContainer
     */
    public function __construct(RelationalDbContainerInterface $rdbContainer)
    {
        $this->rdbContainer = $rdbContainer;
    }

    /**
     * Add a domain with address wildcards for incoming message delivery
     *
     * @param string $accountId
     * @param string $domain
     * @return bool
     */
    public function addIncomingDomain(string $accountId, string $domain): bool
    {
        $database = $this->rdbContainer->getDbHandleForAccountId($accountId);

        // First check to see if this domain exists
        $result = $database->query(
            "SELECT domain, account_id FROM email_domain WHERE domain=:domain",
            ['domain' => $domain]
        );
        $existDomainOwner = "";
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $existDomainOwner = $row['account_id'];
        }

        // Throw an excepiton if this domain belongs to another account
        if ($existDomainOwner != "" && $existDomainOwner != $accountId) {
            throw new DomainOwnedByAnotherAccountException(
                "Another account ($existDomainOwner) already owns the domain $domain"
            );
        }

        // Insert the domain if it does not exist
        if ($existDomainOwner === "") {
            $database->insert("email_domain", [
                'domain' => $domain,
                'account_id' => $accountId
            ]);

            // Insert the wildcard alias
            $database->insert("email_alias", [
                'email_address' => '@' . $domain, // wildcard
                'goto' => 'incoming@' . $domain,
                'account_id' => $accountId
            ]);

            // Create incoming mailbox where all messages will be routed
            // so netric can decide what to do with them.
            $database->insert("email_mailbox", [
                'email_address' => 'incoming@' . $domain,
                'account_id' => $accountId
            ]);
        }


        return true;
    }

    /**
     * Delete a domain, wildcard alias, and incoming mailbox
     *
     * @param string $accountId
     * @param string $domain
     * @return bool
     */
    public function removeIncomingDomain(string $accountId, string $domain): bool
    {
        $database = $this->rdbContainer->getDbHandleForAccountId($accountId);

        // Check if the domain already exists and if the account is the same
        $existDomainOwner = $this->getAccountForDomain($domain, $database);

        // Only the owning account may remove a domain
        // Since they should never be able to add it, this should never happen
        // but better to be safe than sorry.
        if ($existDomainOwner != '' && $existDomainOwner != $accountId) {
            throw new DomainOwnedByAnotherAccountException(
                "Another account ($existDomainOwner) owns the domain $domain " .
                    "and the requested account ($accountId) cannot remove it"
            );
        }

        // Remove the domain
        $database->query(
            "DELETE FROM email_domain WHERE domain=:domain AND account_id=:account_id",
            ['domain' => $domain, 'account_id' => $accountId]
        );

        // Remove the wildcard alias "@domain.com"
        $database->query(
            "DELETE FROM email_alias WHERE email_address=:wildcard AND account_id=:account_id",
            ['wildcard' => '@' . $domain, 'account_id' => $accountId]
        );

        // Now remove the incoming mailbox "incoming@domain.com"
        $database->query(
            "DELETE FROM email_mailbox WHERE email_address=:incoming AND account_id=:account_id",
            ['incoming' => 'incoming@' . $domain, 'account_id' => $accountId]
        );

        // TODO: We should probably check how many records got deleted
        // and return true if it was deleted and false if it was not found
        return true;
    }

    /**
     * Get all domains for an account
     *
     * @param string $accountId The account id that we are currently working on
     * @return string[]
     */
    public function getDomains(string $accountId): array
    {
        $database = $this->rdbContainer->getDbHandleForAccountId($accountId);

        $result = $database->query(
            "SELECT domain FROM email_domain WHERE account_id=:account_id",
            ['account_id' => $accountId]
        );
        if ($result->rowCount() == 0) {
            return [];
        }

        // Convert row of associative rows to just an array of strings
        $ret = [];
        $domains = $result->fetchAll();
        foreach ($domains as $domainRow) {
            $ret[] = $domainRow['domain'];
        }

        return $ret;
    }

    /**
     * Check if a domain exists by getting the account id that owns it
     *
     * @param string $domain
     * @param RelationalDbInterface $database
     * @return string UUID of the account that owns it, otherwise an empty string
     */
    private function getAccountForDomain(string $domain, RelationalDbInterface $database): string
    {
        // First check to see if this domain exists
        $result = $database->query(
            "SELECT account_id FROM email_domain WHERE domain=:domain",
            ['domain' => $domain]
        );
        $existDomainOwner = "";
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            return $row['account_id'];
        }

        // Not found
        return '';
    }
}
