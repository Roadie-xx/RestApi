<?php

declare(strict_types=1);

namespace RoadieXX;

use RoadieXX\Database;

class RestApi {
    public function __construct(string $table, Database $database)
    {
        $this->table = $table;
        $this->database = $database;
    }

    public function execute()
    {

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $data = isset($_GET['id']) ? $this->fetchById($_GET['id']) : $this->fetchAll();
                break;

            case 'POST': // Used for new records
                $data = $this->post();
                break;

            // case 'PUT': // Replace record
            //     $data = $this->put();
            //     break;

            case 'PATCH': // Update some fields in a record
                $data = $this->patch($_GET['id']);
                break;

            default:
                http_response_code(400); //Bad Request
                $data = [
                    
                    'error' => 'Unknown REQUEST_METHOD',
                ];                
            }

        header('Content-Type: application/json');
        echo json_encode($data);
    }



    private function fetchAll(): array
    {
        $sql = sprintf('SELECT * FROM %s', $this->table);

        return $this->database->run($sql);
    }

    private function fetchById(string $id): array
    {
        $sql = sprintf('SELECT * FROM %s WHERE id = :id', $this->table);

        return $this->database->run($sql, ['id' => $id]);
    }

    private function patch(int $id): array 
    {
        $data = $this->parseInput($data);
        
        $parts = array_map(fn(string $key): string => "$key = :$key", array_keys($data));

        $sql = sprintf('UPDATE %s SET %s WHERE id = %d', $this->table, implode(', ', $parts), $id);

        return $this->database->run($sql, $data);

    }

    private function post(): array
    {
        $data = $this->parseInput($data);
        
        $parts = array_map(fn(string $key): string => "$key = :$key", array_keys($data));

        $sql = sprintf('UPDATE %s SET %s', $this->table, implode(', ', $parts));

        return $this->database->run($sql, $data);

    }

    private function parseInput($data): array
    {
        parse_str(file_get_contents("php://input"), $data);
        
        return $data;
    }
}