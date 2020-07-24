<?php
namespace Netric\Mail\Transport;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\Settings\SettingsFactory;
use Netric\Config\ConfigFactory;
use Netric\Log\LogFactory;

/**
 * Create a new SMTP Transport service based on account settings
 */
class SmtpFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceManager ServiceLocator for injecting dependencies
     * @return TransportInterface
     * @throws Exception\InvalidArgumentException if a transport could not be created
     */
    public function createService(AccountServiceManagerInterface $serviceManager)
    {
        // Get the required method
        $config = $serviceManager->get(ConfigFactory::class);

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

        /*
         * Check for account overrides in settings. This allows specific
         * accounts to utilize another email server to send messages from.
         */
        $settings = $serviceManager->get(SettingsFactory::class);
        $host = $settings->get("email/smtp_host");
        $username = $settings->get("email/smtp_user");
        $password = $settings->get("email/smtp_password");
        $port = $settings->get("email/smtp_port");
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

        // Log the Smtp settings
        $log = $serviceManager->get(LogFactory::class);
        $log->info("SmtpFactory:: Email Options - " . json_encode($options));

        return $transport;
    }
}
