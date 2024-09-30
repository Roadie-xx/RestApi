<?php

declare(strict_types=1);

namespace RoadieXX;

use RoadieXX\DatabaseInterface;

class RestApi
{
    private DatabaseInterface $database;
    private string $table;

    public function __construct(string $table, DatabaseInterface $database)
    {
        $this->table = $table;
        $this->database = $database;
    }

    public function execute(?string $id = null): void
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

    /**
     * @return array<int|string, array<string, string>|string>|false
     */
    private function findAll()
    {
        $sql = sprintf('SELECT * FROM %s', $this->table);

        return $this->database->findAll($sql);
    }

    /**
     * @return array<string, string>
     */
    private function find(string $id): array
    {
        $sql = sprintf('SELECT * FROM %s WHERE id = :id', $this->table);

        return $this->database->find($sql, ['id' => $id]);
    }

    /**
     * @return array<string, string>|int
     */
    private function patch(?string $id)
    {
        $data = $this->parseInput();

        $parts = array_map(fn ($key): string => "$key = :$key", array_keys($data));

        $sql = sprintf('UPDATE %s SET %s WHERE id = %d', $this->table, implode(', ', $parts), $id);

        return $this->database->update($sql, $data);
    }

    /**
     * @return array<string, string>|int
     */
    private function post()
    {
        $data = $this->parseInput();

        $parts = array_map(fn ($key): string => "$key = :$key", array_keys($data));

        $sql = sprintf('INSERT INTO %s SET %s', $this->table, implode(', ', $parts));

        return $this->database->insert($sql, $data);
    }

    /**
     * @return array<int|string, array|string>
     */
    private function parseInput(): array
    {
        $content = file_get_contents("php://input") ?: '';

        parse_str($content, $data);

        return $data;
    }
}
