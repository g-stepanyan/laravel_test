<?php

namespace Tests\Unit;

use App\Http\Controllers\TeHelper;
use Tests\TestCase;

class TeHelperTest extends TestCase
{
    /**
     *  TeHelper test example.
     *
     * @return void
     */

    public function testHelper()
    {
        $controller = new TeHelper();
        $response = $controller->willExpireAt('11/05/2020', '11/09/2020');
        $this->assertIsString($response);
    }
}
