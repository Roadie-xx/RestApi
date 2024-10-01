<?php

declare(strict_types=1);

namespace RoadieXX;

use PDO;
use PDOException;
use RuntimeException;

// Get configuration from env
defined('DATABASE_DSN') or define('DATABASE_DSN', getenv('DATABASE_DSN')); // mysql:host=localhost;dbname=database
defined('DATABASE_USER') or define('DATABASE_USER', getenv('DATABASE_USER'));
defined('DATABASE_PASS') or define('DATABASE_PASS', getenv('DATABASE_PASS'));

class Database implements DatabaseInterface
{
    private ?PDO $pdo = null;

    /**
     * @param array<string, string> $options
     */
    public function setPdo(?PDO $pdo = null, array $options = []): void
    {
        if ($pdo === null) {
            $defaultOptions = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ];

            $options = array_replace($defaultOptions, $options);

            if (DATABASE_DSN === false || DATABASE_USER === false || DATABASE_PASS === false) {
                throw new RuntimeException('Missing database credentials');
            }

            $pdo = new PDO(DATABASE_DSN, DATABASE_USER, DATABASE_PASS, $options);
        }

        $this->pdo = $pdo;
    }

    /**
     * @return false|array<int, array<string, string>>|array<string, string>
     */
    public function findAll(string $sql)
    {
        try {
            $this->checkConnected();

            // Cannot call method query() on PDO|null: Caught by $this->checkConnected();
            // @phpstan-ignore method.nonObject
            $statement = $this->pdo->query($sql);

            if ($statement === false) {
                throw new PDOException('Could not query');
            }

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            return $this->returnError($error);
        }
    }

    /**
     * @param array<string, string> $params
     * @return array<string, string>
     */
    public function find(string $sql, array $params): array
    {
        try {
            $this->checkConnected();

            // Cannot call method prepare() on PDO|null: Caught by $this->checkConnected();
            // @phpstan-ignore method.nonObject
            $statement = $this->pdo->prepare($sql);

            $statement->execute($params);

            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if (! is_array($result)) {
                throw new PDOException('Could not fetch');
            }

            return $result;
        } catch (PDOException $error) {
            return $this->returnError($error);
        }
    }

    /**
     * @param array<string|int, string> $params
     * @return int|array<string, string>
     */
    public function insert(string $sql, array $params)
    {
        try {
            $this->checkConnected();

            // Cannot call method prepare() on PDO|null: Caught by $this->checkConnected();
            // @phpstan-ignore method.nonObject
            $statement = $this->pdo->prepare($sql);

            $statement->execute($params);

            return $statement->rowCount();
        } catch (PDOException $error) {
            return $this->returnError($error);
        }
    }

    /**
     * @param array<string|int, string> $params
     * @return int|array<string, string>
     */
    public function update(string $sql, array $params)
    {
        try {
            $this->checkConnected();

            // Cannot call method prepare() on PDO|null: Caught by $this->checkConnected();
            // @phpstan-ignore method.nonObject
            $statement = $this->pdo->prepare($sql);

            $statement->execute($params);

            return $statement->rowCount();
        } catch (PDOException $error) {
            return $this->returnError($error);
        }
    }

    /**
     * @param array<string, string> $params
     * @return false|int|array<int, array<string, string>>|array<string, string>
     */
    public function run(string $sql, ?array $params = [])
    {
        try {
            $this->checkConnected();

            // Cannot call method prepare() on PDO|null: Caught by $this->checkConnected();
            // @phpstan-ignore method.nonObject
            $statement = $this->pdo->prepare($sql);

            $statement->execute($params);

            return $statement->fetchAll();
        } catch (PDOException $error) {
            return $this->returnError($error);
        }
    }

    /**
     * @return array<string, string>
     */
    private function returnError(PDOException $error): array
    {
        http_response_code(500);

        return [
            'status' => 'error',
            'message' => $error->getMessage(),
        ];
    }

    private function checkConnected(): void
    {
        if ($this->pdo === null) {
            throw new PDOException('Not connected to a PDO source');
        }
    }
}
