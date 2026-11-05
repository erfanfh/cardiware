<?php

namespace App\Application\Loyalty\Earning\Enums;

enum EarningRuleSkipReasonEnum: string
{
    case NO_ACTIVE_RULE = 'no_active_rule';
    case NOT_APPLICABLE = 'not_applicable';
    case NOT_APPROVED = 'not_approved';
    case DAILY_LIMIT_REACHED = 'daily_limit_reached';
    case BELOW_MINIMUM = 'below_minimum';
    case ZERO_POINTS = 'zero_points';
    case REFERENCE_INVALID = 'reference_invalid';
    case NOT_ELIGIBLE = 'not_eligible';
}
