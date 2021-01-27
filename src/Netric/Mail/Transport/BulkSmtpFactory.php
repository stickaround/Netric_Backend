<?php
namespace Netric\Mail\Transport;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;
use Netric\Settings\SettingsFactory;

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
class BulkSmtpFactory implements ApplicationServiceFactoryInterface
{
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

        /*
         * Check for account overrides in settings. This allows specific
         * accounts to utilize another email server to send messages from.
         */
        $settings = $serviceLocator->get(SettingsFactory::class);
        $host = $settings->get("email/smtp_bulk_host");
        $username = $settings->get("email/smtp_bulk_user");
        $password = $settings->get("email/smtp_bulk_password");
        $port = $settings->get("email/smtp_bulk_port");
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
}
