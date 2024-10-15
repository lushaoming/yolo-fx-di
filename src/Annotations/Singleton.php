<?php

namespace Yolo\Di\Annotations;

use Attribute;

/**
 * Mark a class as a singleton
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Singleton
{

}
