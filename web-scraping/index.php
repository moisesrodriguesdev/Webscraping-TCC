<?php
require __DIR__ . '/vendor/autoload.php';

use TccUnifametro\Entities\{Expandir, Extensao};

$cursosDeExtensao = (new Extensao())->run();
$cursosDoExpandir = (new Expandir())->run();

$data = collect(['extensao' => $cursosDeExtensao, 'expandir' => $cursosDoExpandir])->toArray();

$fp = fopen('cursos.json', 'a');//opens file in append mode
fwrite($fp, json_encode($data));
fclose($fp);
