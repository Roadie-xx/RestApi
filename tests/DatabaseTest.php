<?php
namespace Test\RoadieXX;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use RoadieXX\Database;
use RoadieXX\RestApi;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Helpers/PrivatePropertyTrait.php';

class PDOMock extends \PDO {
	public function __construct() {}
}

/**
 * @coversDefaultClass \RoadieXX\RestApi
 */
class DatabaseTest extends TestCase
{
    use PrivatePropertyTrait;

    function tearDown(): void
    {
        if (file_exists('example.db')) {
            @unlink('example.db');
        }
    }

    public function testConstructor() {
        $database = new Database();

        $this->assertInstanceOf(Database::class, $database);

        $this->assertSame($this->getPrivateProperty($database, 'pdo'), null);
    }

    public function testSetPdoWithPDO() {
        $pdoMock = new PDOMock();
        $database = new Database();
        $database->setPdo($pdoMock);

        $this->assertInstanceOf(Database::class, $database);

        $this->assertSame($this->getPrivateProperty($database, 'pdo'), $pdoMock);
    }

    public function testSetPdoNoCredentials() {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing database credentials');

        $database = new Database();
        $database->setPdo();
    }

    /**
    * @runInSeparateProcess
    */
    public function testSetPdo() {
        define('DATABASE_DSN', 'sqlite:example.db');
        define('DATABASE_USER', '');
        define('DATABASE_PASS', '');

        $database = new Database();
        $database->setPdo();

        $this->assertInstanceOf(Database::class, $database);

        $this->assertInstanceOf(PDO::class, $this->getPrivateProperty($database, 'pdo'));
    }

    public function testFindAll() {
        $query = 'SELECT * FROM table_a';
        $expected = [
            ['id' => 1, 'name' => 'First Item'],
            ['id' => 2, 'name' => 'Second Item'],
        ];

        $pdoStatementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $pdoStatementMock
            ->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expected);

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($pdoStatementMock);

        $database = new Database();
        $database->setPdo($pdoMock);

        $actual = $database->findAll($query);

        $this->assertSame($expected, $actual);
    }

    public function testFindAllFailed() {
        $query = 'SELECT * FROM table_a';
        $expected = [
            'status' => 'error',
            'message' => 'Could not query',
        ];

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(false);

        $database = new Database();
        $database->setPdo($pdoMock);

        $this->expectOutputString("http_response_code = 500\n");

        $actual = $database->findAll($query);

        $this->assertSame($expected, $actual);
    }

    public function testFind() {
        $query = 'SELECT * FROM table_a WHERE id = 34';
        $expected = ['id' => 34, 'name' => 'Second Item'];

        $pdoStatementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with(['id' => '34'])
            ->willReturn($expected);
        $pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expected);

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($pdoStatementMock);

        $database = new Database();
        $database->setPdo($pdoMock);

        $actual = $database->find($query, ['id' => '34']);

        $this->assertSame($expected, $actual);
    }

    public function testFindFailed() {
        $query = 'SELECT * FROM table_a WHERE id = 45';
        $expected = [
            'status' => 'error',
            'message' => 'Could not execute',
        ];

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willThrowException(new PDOException('Could not execute'));

        $database = new Database();
        $database->setPdo($pdoMock);

        $this->expectOutputString("http_response_code = 500\n");

        $actual = $database->find($query,  ['id' => '45']);

        $this->assertSame($expected, $actual);
    }

    public function testFindNoArrayResult() {
        $query = 'SELECT * FROM table_a WHERE id = 45';
        $expected = [
            'status' => 'error',
            'message' => 'Could not fetch',
        ];

        $pdoStatementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with(['id' => '56'])
            ->willReturn($expected);
        $pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($pdoStatementMock);

        $database = new Database();
        $database->setPdo($pdoMock);

        $this->expectOutputString("http_response_code = 500\n");

        $actual = $database->find($query,  ['id' => '56']);

        $this->assertSame($expected, $actual);
    }

    public function testInsert() {
        $query = 'INSERT INTO table_a SET id = :id, name = :name';
        $params = ['id' => 34, 'name' => 'Second Item'];
        $expected = 1;

        $pdoStatementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with($params)
            ->willReturn($expected);
        $pdoStatementMock
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn($expected);

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($pdoStatementMock);

        $database = new Database();
        $database->setPdo($pdoMock);

        $actual = $database->insert($query, $params);

        $this->assertSame($expected, $actual);
    }

    public function testInsertFailed() {
        $query = 'INSERT INTO table_a SET id = :id, name = :name';
        $params = ['id' => 34, 'name' => 'Second Item'];
        $expected = [
            'status' => 'error',
            'message' => 'Could not insert',
        ];

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willThrowException(new PDOException('Could not insert'));

        $database = new Database();
        $database->setPdo($pdoMock);

        $this->expectOutputString("http_response_code = 500\n");

        $actual = $database->insert($query,  $params);

        $this->assertSame($expected, $actual);
    }

    public function testUpdate() {
        $query = 'UPDATE table_a SET name = :name WHERE id = :id';
        $params = ['id' => 69, 'name' => 'Second Item'];
        $expected = 1;

        $pdoStatementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with($params)
            ->willReturn($expected);
        $pdoStatementMock
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn($expected);

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($pdoStatementMock);

        $database = new Database();
        $database->setPdo($pdoMock);

        $actual = $database->update($query, $params);

        $this->assertSame($expected, $actual);
    }

    public function testUpdateFailed() {
        $query = 'UPDATE table_a SET name = :name WHERE id = :id';
        $params = ['id' => 34, 'name' => 'Second Item'];
        $expected = [
            'status' => 'error',
            'message' => 'Could not update',
        ];

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willThrowException(new PDOException('Could not update'));

        $database = new Database();
        $database->setPdo($pdoMock);

        $this->expectOutputString("http_response_code = 500\n");

        $actual = $database->update($query,  $params);

        $this->assertSame($expected, $actual);
    }

    public function testRun() {
        $query = 'SHOW TABLES';
        $params = [];
        $expected = [['Test table']];

        $pdoStatementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with($params);
        $pdoStatementMock
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expected);

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($pdoStatementMock);

        $database = new Database();
        $database->setPdo($pdoMock);

        $actual = $database->run($query, $params);

        $this->assertSame($expected, $actual);
    }

    public function testRunFailed() {
        $query = 'SHOW TABLES';
        $params = [];
        $expected = [
            'status' => 'error',
            'message' => 'Could not run',
        ];

        $pdoMock = $this->getMockBuilder(PDOMock::class)->getMock();
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willThrowException(new PDOException('Could not run'));

        $database = new Database();
        $database->setPdo($pdoMock);

        $this->expectOutputString("http_response_code = 500\n");

        $actual = $database->run($query,  $params);

        $this->assertSame($expected, $actual);
    }

    public function testCheckConnected() {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not connected to a PDO source');

        $database = new Database();

        $this->invokePrivateMethod($database, 'checkConnected');
    }
}
