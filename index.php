<?php
require __DIR__ . '/vendor/autoload.php';

use TccUnifametro\Entities\{Expandir, Extensao};

$cursosDeExtensao = (new Extensao())->run();
$cursosDoExpandir = (new Expandir())->run();

$data = collect(['extensao' => $cursosDeExtensao, 'expandir' => $cursosDoExpandir])->toArray();

$arquivo = 'data.json';
$json    = json_encode($data);
$file    = fopen(__DIR__ . '/' . $arquivo, 'w');
fwrite($file, $json);
fclose($file);
