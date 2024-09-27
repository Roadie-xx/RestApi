<?php

declare(strict_types=1);

namespace RoadieXX;

use RoadieXX\Database;

class RestApi
{
    private Database $database;
    private string $table;

    public function __construct(string $table, Database $database)
    {
        $this->table = $table;
        $this->database = $database;
    }

    public function execute(?string $id = null)
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET': // Show record(s)
                $data = $id === null ? $this->findAll() : $this->find($id);
                break;

            case 'POST': // Used for new records
                $data = $this->post();
                break;

                // case 'PUT': // Replace record
                //     $data = $this->put();
                //     break;

            case 'PATCH': // Update some fields in a record
                $data = $this->patch($id);
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

    private function findAll(): array
    {
        $sql = sprintf('SELECT * FROM %s', $this->table);

        return $this->database->findAll($sql);
    }

    private function find(string $id): array
    {
        $sql = sprintf('SELECT * FROM %s WHERE id = :id', $this->table);

        return $this->database->find($sql, ['id' => $id]);
    }

    private function patch(int $id): array
    {
        $data = $this->parseInput();

        $parts = array_map(fn (string $key): string => "$key = :$key", array_keys($data));

        $sql = sprintf('UPDATE %s SET %s WHERE id = %d', $this->table, implode(', ', $parts), $id);

        return $this->database->update($sql, $data);
    }

    private function post(): array
    {
        $data = $this->parseInput();

        $parts = array_map(fn (string $key): string => "$key = :$key", array_keys($data));

        $sql = sprintf('INSERT INTO %s SET %s', $this->table, implode(', ', $parts));

        return $this->database->insert($sql, $data);
    }

    private function parseInput(): array
    {
        parse_str(file_get_contents("php://input"), $data);

        return $data;
    }
}
