<?php
// Run with the existing PHP/MySQL setup. Redirect output to a PRIVATE JSON file.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require dirname(__DIR__) . '/config.php';
$tables = [
 'usuarios' => 'SELECT id,nombre,email,preferencia_tipo,rol FROM usuarios',
 'recetas' => 'SELECT * FROM recetas',
 'favoritos' => 'SELECT usuario_id,receta_id FROM favoritos',
 'opiniones' => 'SELECT id,usuario_id,receta_id,comentario FROM opiniones',
 'valoraciones' => 'SELECT usuario_id,receta_id,puntuacion FROM valoraciones'
];
$export = ['format' => 'limalimon-legacy-v1'];
foreach ($tables as $name => $sql) {
 $result = $conn->query($sql);
 if (!$result) { fwrite(STDERR, "No se pudo exportar $name\n"); exit(1); }
 $export[$name] = $result->fetch_all(MYSQLI_ASSOC);
}
echo json_encode($export, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
