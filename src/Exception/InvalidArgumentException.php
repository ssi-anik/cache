<?php

declare(strict_types=1);

namespace Anik\Cache\Exception;

use Psr\Cache\InvalidArgumentException as Psr6Exception;
use Psr\SimpleCache\InvalidArgumentException as Psr16Exception;

class InvalidArgumentException extends \InvalidArgumentException implements Psr6Exception, Psr16Exception
{
}
