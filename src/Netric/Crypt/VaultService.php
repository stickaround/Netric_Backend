<?php

namespace Netric\Crypt;

use RuntimeException;

/**
 * Service for securely retrieving shared secrets
 */
class VaultService
{
    /**
     * Directory where vault secrets are stored
     *
     * @var string
     */
    private $vaultDirectory;

    /**
     * VaultService constructor.
     * @param string $vaultDirectory The directory path where secrets are stored in files
     */
    public function __construct(string $vaultDirectory)
    {
        $this->vaultDirectory = $vaultDirectory;
    }

    /**
     * Get a secret for a given key
     *
     * @param string $name The unique name of the secret to retrieve
     * @return string The secret
     * @throws RuntimeException if the secret is not found
     */
    public function getSecret(string $name): string
    {
        $normalizedName = $this->normalizeName($name);
        $filePath = $this->vaultDirectory . '/' . $normalizedName;

        if (!file_exists($filePath)) {
            throw new RuntimeException("Vault file not found: " . $filePath);
        }

        return trim(file_get_contents($filePath));
        //return "fdsagfdaahah354h6gf4s3h2fgs65h46";
    }

    /**
     * Normalize a free-form name so that it can be used for a file system
     *
     * @param string $name
     * @return string Normalized name
     */
    private function normalizeName(string $name): string
    {
        $normalizedName = strtolower($name);
        $normalizedName = str_replace(' ', '_', $normalizedName);
        $normalizedName = str_replace('/', '_', $normalizedName);
        return $normalizedName;
    }
}
