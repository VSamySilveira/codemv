<?php
declare(strict_types=1);

namespace Tests\Traits;

trait TestProd
{
    protected function skipTestIfNotProd($message = '')
    {
        if(!$this->isTestingProd()) {
            $this->markTestSkipped($message);
        }
    }

    protected function isTestingProd()
    {
        return (env('TESTING_PROD') == true); //If exists and is set to true returns true, otherwise returns false to avoid prod tests due miss configurations;
    }
}