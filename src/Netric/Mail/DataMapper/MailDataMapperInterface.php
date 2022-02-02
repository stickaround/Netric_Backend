<?php

declare(strict_types=1);

namespace Netric\Mail\DataMapper;

interface MailDataMapperInterface
{
    /**
     * Add a domain with address wildcards for incoming message delivery
     *
     * @param string $accountId
     * @param string $domain
     * @return bool
     */
    public function addIncomingDomain(string $accountId, string $domain): bool;

    /**
     * Get all domains for an account
     *
     * @param string $accountId The account id that we are currently working on
     * @param bool $activeOnly Flag that will determine if we are going to get the active domains
     * @return string[]
     */
    public function getDomains(string $accountId, bool $activeOnly = false): array;

    /**
     * Delete a domain, wildcard alias, and incoming mailbox
     *
     * @param string $accountId
     * @param string $domain
     * @return bool
     */
    public function removeIncomingDomain(string $accountId, string $domain): bool;
}
