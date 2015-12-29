<?php
/**
 * Netric Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Netric Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Netric\Mail\Header;

use Zend\Loader\PluginClassLoader;

/**
 * Plugin Class Loader implementation for HTTP headers
 */
class HeaderLoader extends PluginClassLoader
{
    /**
     * @var array Pre-aliased Header plugins
     */
    protected $plugins = [
        'bcc'                       => 'Netric\Mail\Header\Bcc',
        'cc'                        => 'Netric\Mail\Header\Cc',
        'contenttype'               => 'Netric\Mail\Header\ContentType',
        'content_type'              => 'Netric\Mail\Header\ContentType',
        'content-type'              => 'Netric\Mail\Header\ContentType',
        'contenttransferencoding'   => 'Netric\Mail\Header\ContentTransferEncoding',
        'content_transfer_encoding' => 'Netric\Mail\Header\ContentTransferEncoding',
        'content-transfer-encoding' => 'Netric\Mail\Header\ContentTransferEncoding',
        'date'                      => 'Netric\Mail\Header\Date',
        'from'                      => 'Netric\Mail\Header\From',
        'message-id'                => 'Netric\Mail\Header\MessageId',
        'mimeversion'               => 'Netric\Mail\Header\MimeVersion',
        'mime_version'              => 'Netric\Mail\Header\MimeVersion',
        'mime-version'              => 'Netric\Mail\Header\MimeVersion',
        'received'                  => 'Netric\Mail\Header\Received',
        'replyto'                   => 'Netric\Mail\Header\ReplyTo',
        'reply_to'                  => 'Netric\Mail\Header\ReplyTo',
        'reply-to'                  => 'Netric\Mail\Header\ReplyTo',
        'sender'                    => 'Netric\Mail\Header\Sender',
        'subject'                   => 'Netric\Mail\Header\Subject',
        'to'                        => 'Netric\Mail\Header\To',
    ];
}
