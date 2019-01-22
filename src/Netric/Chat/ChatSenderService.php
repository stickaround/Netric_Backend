<?php
namespace Netric\Chat;

/**
 * Handle sending chat messages to the right recipients
 *
 * For now this will just pass a chat message entity to the receiver service,
 * but in the future we will probably implement a message queue like Kafka
 * in order to help with scale.
 *
 * @package Netric\Chat
 */
class ChatSenderService
{

}