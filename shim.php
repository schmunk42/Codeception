<?php
// @codingStandardsIgnoreStart
namespace Symfony\Component\CssSelector {
if (!class_exists('Symfony\Component\CssSelector\CssSelectorConverter')) {
        class CssSelectorConverter {
            function toXPath($cssExpr, $prefix = 'descendant-or-self::') {
                return CssSelector::toXPath($cssExpr, $prefix);
            }
        }
    }
}

// prefering old names
namespace Codeception\TestCase {
if (!class_exists('Codeception\TestCase\Unit')) {
        class Test extends \Codeception\Test\Unit
        {
        }
}
}

namespace Codeception\Module {
if (!class_exists('Codeception\Module\Symfony2')) {
    class Symfony2 extends Symfony
    {
    }

    class Phalcon1 extends Phalcon
    {
    }

    class Phalcon2 extends Phalcon
    {
    }
}
}

namespace Codeception\Platform {
if (!class_exists('Codeception\Platform\Group')) {
    abstract class Group extends \Codeception\GroupObject
    {
    }

    abstract class Extension extends \Codeception\Extension
    {
    }
}
}
namespace {
    class_alias('Codeception\TestInterface', 'Codeception\TestCase');
}

// @codingStandardsIgnoreEnd
