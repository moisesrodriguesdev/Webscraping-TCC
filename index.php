<?php
require __DIR__ . '/vendor/autoload.php';

use TccUnifametro\Entities\{Expandir, Extensao};

$cursosDeExtensao = (new Extensao())->run();
$cursosDoExpandir = (new Expandir())->run();

dump($cursosDeExtensao, $cursosDoExpandir);
