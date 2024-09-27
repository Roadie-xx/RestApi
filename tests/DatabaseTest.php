<?php
namespace Test\RoadieXX;

use InvalidArgumentException;
use RuntimeException;
use RoadieXX\Database;
use RoadieXX\RestApi;
use PHPUnit\Framework\TestCase;

class PDOMock extends \PDO { 
	public function __construct() {} 
} 

/**
 * @coversDefaultClass \RoadieXX\RestApi
 */
class DatabaseTest extends TestCase
{
    public function testSomething() {
        $pdo = $this->getMockBuilder('PDOMock') ->getMock(); 
        $database = $this->getMockBuilder('Database')->disableOriginalConstructor()->getMock();

        
    }

}
