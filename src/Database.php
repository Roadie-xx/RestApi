<?php

declare(strict_types=1);

namespace RoadieXX;

use PDO;
use PDOException;

// Get configuration from env
defined('DATABASE_DSN') or define('DATABASE_DSN', getenv('DATABASE_DSN')); // mysql:host=localhost;dbname=database
defined('DATABASE_USER') or define('DATABASE_USER', getenv('DATABASE_USER'));
defined('DATABASE_PASS') or define('DATABASE_PASS', getenv('DATABASE_PASS'));

class Database extends PDO
{
    public function __construct($options = [])
    {
        $defaultOptions = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];

        $options = array_replace($defaultOptions, $options);
        
        parent::__construct(DATABASE_DSN, DATABASE_USER, DATABASE_PASS, $options);
    }

    public function findAll($sql): array 
    {
        try {
            $statement = $this->query($sql);

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            return $this->returnError($error);
        }   
    }

    public function find($sql, $params): array 
    {
        try {
            $statement = $this->prepare($sql);
            
            $statement->execute($params);

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            return $this->returnError($error);
        }   
    }

    public function insert($sql, $params = []): int
    {
        try {
            $statement = $this->prepare($sql);

            $statement->execute($params);

            return $statement->rowCount();
        } catch (PDOException $error) {
            return $this->returnError($error);
        }   
    }

    public function update($sql, $params = []): int
    {
        try {
            $statement = $this->prepare($sql);

            $statement->execute($params);

            return $statement->rowCount();
        } catch (PDOException $error) {
            return $this->returnError($error);
        }   
    }

    public function run($sql, $args = []): mixed
    {
        try {
            $stmt = $this->prepare($sql);

            $stmt->execute($args);

            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return $this->error($e->getMessage());
        }
    }

    private function returnError(PDOException $error): array
    {
        http_response_code(500); 

        return [
            'status' => 'error',
            'message' => $error->getMessage(),
        ];
    }
}
