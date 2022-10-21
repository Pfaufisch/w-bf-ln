<?php

define('TARGET_CSV_FILENAME', 'republishes.csv');

function getTextFiles(): array {
    $files = scandir(__DIR__);
    $files = array_filter($files, function (string $file): bool {
        return str_contains($file, '.txt');
    });

    return $files;
}

function fixJson(string $filename): ?array {
    $text = '{' . file_get_contents($filename);

    try {
        $data = json_decode($text, true, JSON_THROW_ON_ERROR);

        return $data;
    } catch (Exception $e) {
        return null;
    }
}

function getMetadata(array $data): array {
    $data = [
        'modified' => $data['dateModified'] ?? null,
        'headline' => $data['headline'] ?? null,
        'alternativeHeadline' => $data['alternativeHeadline'] ?? null,
        'url' => $data['mainEntityOfPage']['url'] ?? null,
    ];

    if ($data['modified'] !== null) {
        $data['modified'] = (new DateTime($data['modified']))->format('d.m.Y H:i');
    }

    return $data;
}

function writeCsv(array $head, array $rows, string $filename): void {
    $fp = fopen($filename, 'w');
    fputcsv($fp, $head);

    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }

    fclose($fp);
}

function run() {
    $files = getTextFiles();

    $datas = [];
    foreach ($files as $file) {
        $data = fixJson($file);
        if ($data !== null) {
            $datas[] = $data;
        }
    }

    $metadatas = [];
    foreach ($datas as $data) {
        $metadatas[] = getMetadata($data);
    }

    writeCsv(
        [
            'datum',
            'headline',
            'dachzeile',
            'url',
        ],
        $metadatas,
        TARGET_CSV_FILENAME,
    );
}

run();
