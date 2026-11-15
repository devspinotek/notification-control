<?php

namespace Devspinotek\NotificationControl\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class NotificationMeta
{
    public function __construct(
        public string $title = '',
        public string $category = 'Umum',
        public string $trigger = '',
        public string $calledIn = '',
        public array $channels = ['mail'],
    ) {}
}

