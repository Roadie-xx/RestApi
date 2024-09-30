<?php

namespace RoadieXX;

function file_get_contents(string $data) {
    if (isset($_ENV['PHPUNIT_RESPONSE_FILE_GET_CONTENTS'])) {
        return $_ENV['PHPUNIT_RESPONSE_FILE_GET_CONTENTS'];
    } 

    return false;
}

function http_response_code(int $code) {
    echo "http_response_code = $code\n";
}

function header(string $header) {
    echo "header set with \"$header\"\n";
}
