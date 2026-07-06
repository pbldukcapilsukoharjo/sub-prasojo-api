<?php
$content = file_get_contents('docs/prasojo.sql');
preg_match_all('/INSERT INTO `ajuan`.*?VALUES\s*\((.+?)\);/s', $content, $matches);
$statuses = [];
foreach ($matches[0] as $insert_block) {
    preg_match_all('/\((?:[^)]+)\)/', trim($insert_block), $rows);
    foreach ($rows[0] as $row) {
        $fields = str_getcsv(trim($row, '()'), ',', "'");
        if (isset($fields[11])) {
            $status = trim($fields[11], "'");
            if (!isset($statuses[$status])) $statuses[$status] = 0;
            $statuses[$status]++;
        }
    }
}
print_r($statuses);
