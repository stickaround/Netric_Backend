<?php

declare(strict_types=1);

namespace Netric\PubSub;

interface PubSubInterface
{
    public function publish(string $topic, array $data): void;
}
