<?php

namespace Yolo\Di\Annotations;

use Attribute;

/**
 * Mark a method as an initialization method.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Initializer
{
}
