<?php

use Incenteev\TranslationCheckerBundle\Tests\FixtureApp\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

return new Application(new TestKernel('test', true));
