<?php

namespace Netric\Mail\Transport;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Settings\SettingsFactory;
use Netric\Config\ConfigFactory;
use Netric\Log\LogFactory;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Account\AccountContainerInterface;
use Netric\Authentication\AuthenticationService;

/**
 * Create a new SMTP Transport service based on account settings
 */
class SmtpFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Service used to get the current user/account
     */
    private AuthenticationService $authService;

    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return TransportInterface
     * @throws Exception\InvalidArgumentException if a transport could not be created
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Get the required method
        $config = $serviceLocator->get(ConfigFactory::class);

        // Initialize new Smtp transport
        $transport = new Smtp();

        /*
         * Set the default application level email settings from the system config
         */
        $options = [
            'host' => $config->email["server"],
        ];

        // Add username and password if needed for sending messages
        if (isset($config->email['username']) && isset($config->email['password'])) {
            $options['connection_class'] = 'login';
            $options['connection_config'] = [
                'username' => $config->email['username'],
                'password' => $config->email['password'],
            ];
        }

        // Setup the port if set in the system config
        if (isset($config->email['port'])) {
            $options['port'] = $config->email['port'];
        }

        $this->accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $this->authService = $serviceLocator->get(AuthenticationServiceFactory::class);

        $currentAccount = $this->getAuthenticatedAccount();

        /*
         * Check for account overrides in settings. This allows specific
         * accounts to utilize another email server to send messages from.
         */
        if ($currentAccount) {
            $settings = $serviceLocator->get(SettingsFactory::class);
            $host = $settings->get("email/smtp_host", $currentAccount->getAccountId());
            $username = $settings->get("email/smtp_user", $currentAccount->getAccountId());
            $password = $settings->get("email/smtp_password", $currentAccount->getAccountId());
            $port = $settings->get("email/smtp_port", $currentAccount->getAccountId());
            if ($host) {
                $options['host'] = $host;

                // Check for login information
                if ($username && $password) {
                    $options['connection_class'] = 'login';
                    $options['connection_config'] = [
                        'username' => $username,
                        'password' => $password,
                    ];
                } else {
                    unset($options['connection_class']);
                    unset($options['connection_config']);
                }

                if ($port) {
                    $options['port'] = $port;
                }
            }
        }


        // Apply set options to the transport
        $transport->setOptions(new SmtpOptions($options));

        // Log the Smtp settings
        $log = $serviceLocator->get(LogFactory::class);
        $log->info("SmtpFactory:: Email Options - " . json_encode($options));

        return $transport;
    }

    /**
     * Get the currently authenticated account
     *
     * @return Account
     */
    private function getAuthenticatedAccount()
    {
        $authIdentity = $this->authService->getIdentity();
        if (!$authIdentity) {
            return null;
        }

        return $this->accountContainer->loadById($authIdentity->getAccountId());
    }
}
