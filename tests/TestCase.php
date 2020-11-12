<?php

namespace LaraCaptcha\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use LaraCaptcha\LaraCaptchaServiceProvider;

class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaraCaptchaServiceProvider::class,
        ];
    }
}
