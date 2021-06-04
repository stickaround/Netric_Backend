<?php
namespace Netric\Mail\Transport;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;
use Netric\Settings\SettingsFactory;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Account\AccountContainerInterface;
use Netric\Authentication\AuthenticationService;

/**
 * Create a new Bulk SMTP Transport service based on account settings
 *
 * This is basically used any time we are sending emails to any recipients that are not
 * verified netric users to protect the sending reputation of our main mail servers.
 * It also gives users the ability to define their own SMTP servers to assume any additional
 * risk on their side of getting blacklisted which will relax our bulk mail requirements since
 * if they mess up their reputation, it's their fault.
 *
 * This factory is basically just gathering configuration options from either the system
 * settings or user-defined account settings.
 */
class BulkSmtpFactory implements FactoryInterface
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
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return TransportInterface
     * @throws Exception\InvalidArgumentException if a transport could not be created
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        // Get the required method
        $config = $serviceLocator->get(ConfigFactory::class);

        // Initialize new Smtp transport
        $transport = new Smtp();

        /*
         * Set the default application level email settings from the system config
         */
        $options = [
            'host' => $config->email["bulk_server"],
        ];

        if ($config->email["bulk_port"]) {
            $options['port'] = $config->email["bulk_port"];
        }

        // Add username and password if needed for sending messages
        if (isset($config->email['bulk_user']) && isset($config->email['bulk_password'])) {
            $options['connection_class'] = 'login';
            $options['connection_config'] = [
                'username' => $config->email['bulk_user'],
                'password' => $config->email['bulk_password'],
            ];
        }

        $this->accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $this->authService = $serviceLocator->get(AuthenticationServiceFactory::class);

        $currentAccount = $this->getAuthenticatedAccount();

        /*
         * Check for account overrides in settings. This allows specific
         * accounts to utilize another email server to send messages from.
         */
        $settings = $serviceLocator->get(SettingsFactory::class);
        $host = $settings->get("email/smtp_bulk_host", $currentAccount->getAccountId());
        $username = $settings->get("email/smtp_bulk_user", $currentAccount->getAccountId());
        $password = $settings->get("email/smtp_bulk_password", $currentAccount->getAccountId());
        $port = $settings->get("email/smtp_bulk_port", $currentAccount->getAccountId());
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


        // Apply set options to the transport
        $transport->setOptions(new SmtpOptions($options));

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
