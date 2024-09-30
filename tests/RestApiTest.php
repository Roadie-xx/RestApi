<?php
namespace Test\RoadieXX;

use InvalidArgumentException;
use RuntimeException;
use RoadieXX\Database;
use RoadieXX\RestApi;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Helpers/MockFunctions.php';
require_once __DIR__ . '/Helpers/PrivatePropertyTrait.php';

/**
 * @coversDefaultClass \RoadieXX\RestApi
 */
class RestApiTest extends TestCase
{
    use PrivatePropertyTrait;

    public function testConstruction() {
        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();

        $api = new RestApi('table_name', $database);

        $this->assertInstanceOf(RestApi::class, $api);

        $this->assertSame($this->getPrivateProperty($api, 'table'), 'table_name');
        $this->assertSame($this->getPrivateProperty($api, 'database'), $database);
    }

    public function testExecuteWithUnknownRequestMethod() {
        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();

        $api = new RestApi('table_name', $database);

        $_SERVER['REQUEST_METHOD'] = '';

        $this->expectOutputString("http_response_code = 400\nheader set with \"Content-Type: application/json\"\n{\"error\":\"Unknown REQUEST_METHOD\"}");

        $api->execute();
    }

    public function testExecuteWithGetNoId() {
        $expectedResult = [
            ['id' => 1, 'name' => 'First Item'],
            ['id' => 2, 'name' => 'Second Item'],
        ];

        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();

        $database
            ->expects($this->once())
            ->method('findAll')
            ->with('SELECT * FROM table_name')
            ->willReturn($expectedResult);

        $api = new RestApi('table_name', $database);

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->expectOutputString("header set with \"Content-Type: application/json\"\n" . json_encode($expectedResult));

        $api->execute();
    }

    public function testExecuteWithGetAndId() {
        $expectedResult = ['id' => 2, 'name' => 'Second Item'];

        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();

        $database
            ->expects($this->once())
            ->method('find')
            ->with('SELECT * FROM table_name WHERE id = :id', ['id' => '2'])
            ->willReturn($expectedResult);

        $api = new RestApi('table_name', $database);

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->expectOutputString("header set with \"Content-Type: application/json\"\n" . json_encode($expectedResult));

        $api->execute('2');
    }

    public function testExecuteWithPatch() {
        $expectedResult = 1;

        $_ENV['PHPUNIT_RESPONSE_FILE_GET_CONTENTS'] = 'name=New Name&company=Unknown';

        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();
        $database
            ->expects($this->once())
            ->method('update')
            ->with(
                'UPDATE table_name SET name = :name, company = :company WHERE id = 23', 
                [
                    'name' => 'New Name',
                    'company' => 'Unknown',
                ]
            )
            ->willReturn($expectedResult);

        $api = new RestApi('table_name', $database);

        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        $this->expectOutputString("header set with \"Content-Type: application/json\"\n" . json_encode($expectedResult));

        $api->execute('23');
    }

    public function testExecuteWithPost() {
        $expectedResult = 1;

        $_ENV['PHPUNIT_RESPONSE_FILE_GET_CONTENTS'] = 'name=New Name&company=New Company';

        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();
        $database
            ->expects($this->once())
            ->method('insert')
            ->with(
                'INSERT INTO table_name SET name = :name, company = :company', 
                [
                    'name' => 'New Name',
                    'company' => 'New Company',
                ]
            )
            ->willReturn($expectedResult);

        $api = new RestApi('table_name', $database);

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->expectOutputString("header set with \"Content-Type: application/json\"\n" . json_encode($expectedResult));

        $api->execute();
    }

    public function testFindAll() {
        $expectedResult = [
            ['id' => 1, 'name' => 'First Item'],
            ['id' => 2, 'name' => 'Second Item'],
        ];

        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();
        $database
            ->expects($this->once())
            ->method('findAll')
            ->with('SELECT * FROM table_name')
            ->willReturn($expectedResult);

        $api = new RestApi('table_name', $database);

        $this->assertSame($this->invokePrivateMethod($api, 'findAll'), $expectedResult);
    }

    public function testFind() {
        $expectedResult = ['id' => 1, 'name' => 'First Item'];

        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();
        $database
            ->expects($this->once())
            ->method('find')
            ->with('SELECT * FROM table_name WHERE id = :id', ['id' => '1'])
            ->willReturn($expectedResult);

        $api = new RestApi('table_name', $database);

        $this->assertSame($this->invokePrivateMethod($api, 'find', ['1']), $expectedResult);
    }

    public function testPatch() {
        $expectedResult = 1;

        $_ENV['PHPUNIT_RESPONSE_FILE_GET_CONTENTS'] = 'name=Test Name&company=Unknown';

        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();
        $database
            ->expects($this->once())
            ->method('update')
            ->with(
                'UPDATE table_name SET name = :name, company = :company WHERE id = 23', 
                [
                    'name' => 'Test Name',
                    'company' => 'Unknown',
                ]
            )
            ->willReturn($expectedResult);

        $api = new RestApi('table_name', $database);

        $this->assertSame($this->invokePrivateMethod($api, 'patch', ['23']), $expectedResult);
    }

    public function testInsert() {
        $expectedResult = 1;

        $_ENV['PHPUNIT_RESPONSE_FILE_GET_CONTENTS'] = 'name=Test Name&company=Known Company';

        $database = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();
        $database
            ->expects($this->once())
            ->method('insert')
            ->with(
                'INSERT INTO table_name SET name = :name, company = :company', 
                [
                    'name' => 'Test Name',
                    'company' => 'Known Company',
                ]
            )
            ->willReturn($expectedResult);

        $api = new RestApi('table_name', $database);

        $this->assertSame($this->invokePrivateMethod($api, 'post'), $expectedResult);
    }
}
