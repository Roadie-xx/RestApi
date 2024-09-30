<?php

declare(strict_types=1);

namespace RoadieXX;

interface DatabaseInterface
{
    /**
     * @return false|array<int, array<string, string>>|array<string, string>
     */
    public function findAll(string $sql);

    /**
     * @param array<string, string> $params
     * @return array<string, string>
     */
    public function find(string $sql, array $params): array;

    /**
     * @param array<int|string, array<int, mixed>|string> $params
     * @return int|array<string, string>
     */
    public function insert(string $sql, array $params);

    /**
     * @param array<int|string, array<int, mixed>|string> $params
     * @return int|array<string, string>
     */
    public function update(string $sql, array $params);

    /**
     * @param array<string, string> $params
     * @return int|array<string, string>
     */
    public function run(string $sql, ?array $params = []);
}
