<?php

declare(strict_types=1);

namespace RoadieXX;

use PDO;

// Get configuration from env
defined('DATABASE_DSN') or define('DATABASE_DSN', getenv('DATABASE_DSN')); // mysql:host=localhost;dbname=database
defined('DATABASE_USER') or define('DATABASE_USER', getenv('DATABASE_USER'));
defined('DATABASE_PASS') or define('DATABASE_PASS', getenv('DATABASE_PASS'));

class Database extends PDO
{
    public $pdo;

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

    public function run($sql, $args = [])
    {
        try {
            $stmt = $this->prepare($sql);

            $stmt->execute($args);

            return $stmt->fetch();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return $this->error($e->getMessage());
        }
    }
}
