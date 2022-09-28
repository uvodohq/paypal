<?php

namespace Uvodo\Paypal;

use Modules\Shared\Domain\ValueObjects\Enum;

/**
 * @method static SandboxMode ACTIVE()
 * @method static SandboxMode INACTIVE()
 * @package Uvodo\Paypal
 */
class SandboxMode extends Enum
{
    public const ACTIVE = 1;

    public const INACTIVE = 2;

    /**
     * @var string
     */
    protected string $value;
}
