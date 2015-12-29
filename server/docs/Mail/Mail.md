# Mail

Use the Mail module send email from netric.

## Theory of Operation

The Mail module utilizes the follow components to compose and send messages.

### Transport
The transport is the actual transportation of the message from the local server to the destination.

We use a factory to setup the factory based on the settings for the installation and for each account.

#### Regular Mail Transport
This transport is used for sending notices to users of netric.

    $serviceManager = $application->getAccount()->getServiceManager();
    $transport = $serviceManager->get("Netric/Mail/Transport/Transport");
    // Send any Netric\Mail\Message with $transport->send();
    
#### Bulk Mail Transport
This is the transport used for bulk mailers.

    $serviceManager = $application->getAccount()->getServiceManager();
    $transport = $serviceManager->get("Netric/Mail/Transport/BulkTransport");
    // Send any Netric\Mail\Message with $transport->send();
    
In both cases the transports can be configured per account. Account administrators can setup alternate
SMTP servers to use for either.

In the future we may create an API based transport as well for clients to want to expose a mailer database
of some kind to queue message from their network.

### Message
The Message class represents an indivial message to send via a transport.

    $message = new \Netric\Mail\Message();
    $message->addFrom("noreply@netric.com");
    $message->addTo($emailAddress);
    $message->setBody($body);
    $message->setEncoding('UTF-8');
    $message->setSubject($subject);

## Example Usage

### Sending a Plain-Text Message

    // Create a new message
    $message = new \Netric\Mail\Message();
    $message->addFrom("noreply@netric.com");
    $message->addTo("test@netric.com");
    $message->setBody("Body Content Here");
    $message->setEncoding('UTF-8');
    $message->setSubject("Test Message");
    
    $serviceManager = $application->getAccount()->getServiceManager();
    $transport = $serviceManager->get("Netric/Mail/Transport/Transport");
    $transport->send($message);
    
### Unit Testing
To make unit testing easier, there is an in memory transport that never actually sends the message.

    $transport = new \Netric\Mail\Transport\InMemory.php
    $transport->send($message); // This will not send anything
    $transport->getSentMessages(); // Returns an array of \Netric\Mail\Message
    