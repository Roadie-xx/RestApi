<?php

namespace RoadieXX;

function file_get_contents(string $data) {
    return $_ENV['PHPUNIT_RESPONSE_FILE_GET_CONTENTS'] ?? false;
}

function http_response_code(int $code): void {
    echo "http_response_code = $code\n";
}

function header(string $header): void {
    echo "header set with \"$header\"\n";
}
