<?php

declare(strict_types=1);

namespace App\Data\Email;

class OutgoingEmailData
{
    /**
     * @param  array<int, string>  $to
     * @param  array<int, string>  $cc
     * @param  array<int, string>  $bcc
     * @param  array<int, string>  $attachmentPaths
     */
    public function __construct(
        public readonly array $to,
        public readonly string $subject,
        public readonly ?string $bodyText = null,
        public readonly ?string $bodyHtml = null,
        public readonly array $cc = [],
        public readonly array $bcc = [],
        public readonly array $attachmentPaths = [],
    ) {}
}
