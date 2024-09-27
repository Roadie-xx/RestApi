<?php
namespace Test\RoadieXX;

use InvalidArgumentException;
use RuntimeException;
use RoadieXX\Database;
use RoadieXX\RestApi;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \RoadieXX\RestApi
 */
class RestApiTest extends TestCase
{
    public function testSomething() {
        $database = $this->getMockBuilder('Database')->disableOriginalConstructor()->getMock();

        $api = new RestApi('table', $database);

        $this->assertInstanceOf(RestApi::class, $api);
    }

}
