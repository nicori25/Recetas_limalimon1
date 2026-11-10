# Migración de LimaLimón: PHP/MySQL → Vue/Supabase

## Alcance y correspondencias

| Original                                                                                                 | Nueva aplicación                                                           |
| -------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------- |
| `index.php`, preferencias dinámicas                                                                      | Catálogo con búsqueda, etiquetas personalizadas y filtros combinables      |
| `login.php`, `register.php`, `logout.php`                                                                | Supabase Auth: acceso, registro, correo verificado y recuperación          |
| `perfil.php`, `actualizar.php`                                                                           | Perfil y preferencia alimentaria                                           |
| `recetas.php`, `guardar_receta.php`, `editar_receta.php`, `actualizar_receta.php`, `eliminar_receta.php` | Crear, editar y eliminar recetas; subir imágenes a Storage                 |
| `mis_recetas.php`, `mis_favoritos.php`, `favorito.php`                                                   | Recetario personal y favoritos privados                                    |
| `guardar_opinion.php`, `valorar.php`                                                                     | Comentarios y valoraciones de 1 a 5, una por persona/receta                |
| `ruleta.php`                                                                                             | Ruleta por estación del hemisferio sur y preferencia                       |
| `admin*.php`, `cambiar_rol.php`, `eliminar_usuario.php`, `eliminar_valoracion.php`                       | Panel de administración, moderación, roles, desactivación y eliminación    |
| Nuevas funciones                                                                                         | Lista de compras local, modo cocina, impresión, filtros por tiempo y orden |

Los archivos PHP permanecen disponibles para comparar o exportar; no participan del build de Vue. La documentación original permanece en `documentacion/`.

## Antes de importar

- Hacé una copia de MySQL y `uploads/`; trabajá con una copia de la base para el ensayo.
- Aplicá la migración SQL en un proyecto de Supabase **vacío**. No es una actualización genérica de un proyecto que ya tenga tablas con esos nombres.
- La rama usa por defecto el proyecto de Supabase configurado en `src/lib/supabase.public.ts`; la importación de MySQL es un paso separado. Las recetas ilustrativas de la demo son independientes del contenido original.
- Los UUID de Supabase reemplazan los enteros de MySQL. `legacy_id` vincula cada usuario, receta y comentario original para repetir la importación sin duplicarlos.
- Los tiempos y porciones no existían: se importan como **30 minutos y 2 porciones**, para revisión posterior. Las etiquetas se conservan literalmente; no se infieren restricciones por los ingredientes.

## Exportación privada

Con PHP, `mysqli`, MySQL y `config.php` del proyecto original funcionando:

```bash
umask 077
php scripts/export-legacy.php > /ruta/privada/legacy-export.json
node scripts/import-legacy.mjs /ruta/privada/legacy-export.json ./uploads --dry-run
```

El exportador solo se ejecuta por CLI, exporta las cinco tablas funcionales y **excluye las contraseñas**. El JSON contiene correos y debe mantenerse fuera de Git, `public/` y del directorio publicado. En el entorno preparado anteriormente, el PHP configurado está en `/workspace/setup-limalimon/bin/php`.

La simulación comprueba formato, autores y referencias. No se conecta a Supabase ni sube imágenes. No sustituye la comprobación de límites SQL, archivos y correo en el ensayo real.

## Importación

En un equipo/servidor privado, configurá `SUPABASE_URL` y `SUPABASE_SERVICE_ROLE_KEY` mediante variables seguras del proceso. No usar `.env` del frontend ni variables `VITE_`; el script no carga automáticamente archivos `.env`.

```bash
npm run migrate:legacy -- /ruta/privada/legacy-export.json ./uploads
```

Para conservar los roles administradores de un exportado revisado y confiable:

```bash
npm run migrate:legacy -- /ruta/privada/legacy-export.json ./uploads --include-admin-roles
```

Por defecto todas las cuentas importadas son de usuario. El primer administrador puede asignarse desde SQL Editor. Nunca se confía en `role` enviado en el registro del navegador.

El importador crea las cuentas de Auth y enlaza los perfiles, sube imágenes, importa recetas y reconstruye favoritos, valoraciones y comentarios. Las rutas de imágenes se limitan al directorio indicado y a JPG/PNG/WebP de hasta 5 MB. No se envían invitaciones ni correos masivos automáticamente. Una cuenta preexistente con el mismo correo pero sin vínculo de migración bloquea el proceso para evitar apropiársela.

Las escrituras entre Auth, Storage y Postgres no forman una única transacción. Si falla, revisá el error y repetí el comando: se reutilizan `legacy_id` y el marcador de la cuenta, se actualizan las mismas recetas e interacciones y se sobrescribe únicamente el archivo de imagen determinista de esa receta antigua. No ejecutar simultáneamente dos importaciones ni importar de nuevo después de que la comunidad empiece a editar, porque el exportado vuelve a ser la fuente de esos datos.

## Recuperar las cuentas

No se trasladan hashes PHP ni contraseñas conocidas. Cada persona usa «Olvidé mi contraseña» con su correo original y verifica su acceso mediante el enlace. Para ello tienen que estar configurados SMTP y Redirect URLs en Supabase. Probá este recorrido con una cuenta controlada antes de avisar a usuarios reales. No se puede comprobar la entrega de correo sin un proyecto configurado.

## Verificaciones al conectar Supabase

1. Comparar conteos por tabla y la correspondencia de `legacy_id`.
2. Registrar, confirmar correo, ingresar, salir y recuperar contraseña desde un enlace real.
3. Publicar una receta con imagen, editarla y verla desde otra sesión.
4. Con dos usuarios, comprobar que favoritos sean privados y que uno no pueda editar las recetas del otro.
5. Valorar dos veces y comprobar que se actualice el voto; publicar y eliminar un comentario propio.
6. Probar moderación con un administrador; verificar que una cuenta desactivada no pueda escribir ni subir imágenes, incluso con una sesión antigua.
7. Ensayar eliminación de una receta y de una cuenta en datos de prueba. Se eliminan sus relaciones por cascada. Las imágenes públicas que queden sin referencias necesitan limpieza desde Storage; no borrar directamente filas de `storage.objects` por SQL. Antes de eliminar definitivamente una cuenta con archivos en un proyecto real, revisar y retirar sus objetos desde Storage.
8. Verificar las páginas en celular y el origen publicado en las Redirect URLs.

## Pruebas ejecutables y límites

- `npm test`: búsqueda, combinación de preferencias, temporada argentina y preparación de líneas.
- `npm run test:e2e`: Chromium con la demo, CRUD completo, favoritos, comentarios, valoraciones, compras, cocina, perfil, ruleta, administración, control de visitante y ancho móvil.
- `npm run test:db`: aplica **la migración real** a PostgreSQL 17 con tablas mínimas que simulan los contratos de `auth` y `storage`. Comprueba los permisos SQL y las políticas, incluida la imposibilidad de elevar el rol desde el cliente. No comprueba Auth HTTP, entrega de correo, Storage HTTP ni la configuración interna completa de Supabase.
- La exportación actual se validó sin escritura: 3 usuarios, 1 receta, 1 favorito, 0 comentarios y 1 valoración. No se aplicó a un servicio remoto.

La demo usa una cuenta administradora ficticia y almacenamiento local. No ofrece las garantías de Supabase ni debe usarse para guardar datos sensibles. Con variables reales la aplicación usa exclusivamente Supabase y muestra los errores, sin conmutar silenciosamente a datos de ejemplo.
