<?php

namespace Incenteev\TranslationCheckerBundle\Tests;

use Prophecy\Prophet;

class ProphecyTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Prophet
     */
    protected $prophet;

    protected function setup()
    {
        $this->prophet = new Prophet();
    }

    protected function teardown()
    {
        $this->prophet->checkPredictions();
    }
}
