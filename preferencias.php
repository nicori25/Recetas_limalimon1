<?php
function preferencias_base() {
    return [
        "vegano" => "Vegano",
        "vegetariano" => "Vegetariano",
        "sin gluten" => "Sin gluten",
        "sin lactosa" => "Sin lactosa"
    ];
}

function obtener_preferencias($conn) {
    $preferencias = preferencias_base();
    $vistas = [];

    foreach(array_keys($preferencias) as $valor_base) {
        $vistas[$valor_base] = true;
    }

    $sql = "
        SELECT preferencia_tipo AS tipo
        FROM usuarios
        WHERE preferencia_tipo IS NOT NULL
        AND preferencia_tipo <> ''
        UNION
        SELECT tipo
        FROM recetas
        WHERE tipo IS NOT NULL
        AND tipo <> ''
        ORDER BY tipo
    ";

    $result = $conn->query($sql);

    if($result) {
        while($row = $result->fetch_assoc()) {
            $valor = trim($row["tipo"] ?? "");

            if($valor === "" || $valor === "otro") {
                continue;
            }

            $clave = function_exists("mb_strtolower")
                ? mb_strtolower($valor, "UTF-8")
                : strtolower($valor);

            if(!isset($vistas[$clave])) {
                $preferencias[$valor] = $valor;
                $vistas[$clave] = true;
            }
        }
    }

    return $preferencias;
}
?>
