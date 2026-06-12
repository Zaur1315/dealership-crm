<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadPipelineStage: string
{
    case NEW = 'new';
    case IN_COMMUNICATION = 'in_communication';
    case DID_NOT_ANSWER = 'did_not_answer';
    case IN_NEGOTIATION = 'in_negotiation';
    case CONTRACT = 'contract';
    case INVOICE = 'invoice';
    case WON = 'won';
    case LOST = 'lost';
    case NOT_INTERESTED = 'not_interested';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::IN_COMMUNICATION => 'In Communication',
            self::DID_NOT_ANSWER => 'Did Not Answer',
            self::IN_NEGOTIATION => 'In Negotiation',
            self::CONTRACT => 'Contract',
            self::INVOICE => 'Invoice',
            self::WON => 'Won',
            self::LOST => 'Lost',
            self::NOT_INTERESTED => 'Not Interested',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $stage): array => [$stage->value => $stage->label()])
            ->all();
    }
}
