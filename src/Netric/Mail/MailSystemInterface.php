<?php

declare(strict_types=1);

namespace Netric\Mail;

interface MailSystemInterface
{
    /**
     * Returns the default domain for an account
     * 
     * @return string The domain that should be used by default for an account
     */
    public function getDefaultDomain(string $accountId): string;

    /**
     * Add a domain for an account
     * 
     * @return bool true on success, false on failure
     */
    public function addDomain(string $accountId, string $domain): bool;

    /**
     * Get all domains for an account
     * 
     * @return string[]
     */
    public function getDomains(string $accountId): array;

    /**
     * This looks for the account ID associated with a domain
     * 
     * @return string UUID of the account that owns this domain
     */
    public function getAccountIdFromDomain(string $domain): string;

}