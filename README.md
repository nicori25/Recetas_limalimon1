# LimaLimón · Cociná a tu manera

La misma comunidad de recetas, renovada con **Vue 3, TypeScript y Supabase**. La versión PHP/MySQL original se conserva en esta rama como referencia y origen de la migración. La nueva aplicación se ejecuta desde `src/` y se publica desde `dist/`.

## Empezar con Supabase

Requiere Node.js **22.12 o superior** (recomendado: 24 LTS).

```bash
npm ci
npm run dev
```

La aplicación ya incluye la URL y la clave pública del proyecto `syojuxibmkarhtweuqfv` en `src/lib/supabase.public.ts`. Al ejecutarla o compilarla sin variables adicionales, usa ese servidor de Supabase. No depende de `.env.local` ni requiere volver a cargar estos datos en el alojamiento.

Para usar la **demo local**, copiá `.env.example` a `.env.local` y dejá ambas variables vacías; reiniciá Vite o volvé a compilar. Se abre un catálogo con seis recetas ilustrativas. En «Mi cuenta» → «Entrar a la demo» se habilitan las interacciones y el panel de administración de prueba. No crea cuentas reales ni llama a Supabase; guarda los cambios en el navegador. La lista de compras también es local. «Reiniciar demo» pide confirmación antes de borrar las interacciones. El catálogo de demostración no se carga automáticamente en una base real.

## Funciones

- Catálogo con búsqueda sin distinción de tildes, filtros alimentarios combinables, temporada, tiempo y orden.
- Recetas con ingredientes, pasos, porciones, imágenes, edición y eliminación.
- Favoritos personales, una valoración por persona y receta, y comentarios moderables.
- Ruleta de temporada para el hemisferio sur, calculada en el huso de Buenos Aires.
- Lista de compras con casillas, copia y persistencia local; modo cocina paso a paso e impresión.
- Registro, confirmación de correo, inicio/cierre de sesión y recuperación de contraseña con Supabase Auth.
- Perfil y preferencia alimentaria. Administración de recetas, comentarios, valoraciones y cuentas (roles, desactivación y eliminación).
- Diseño adaptable, navegación con teclado, diálogos nativos con foco y respeto por movimiento reducido.

Las etiquetas alimentarias son declaraciones de la comunidad: no certifican ausencia de alérgenos ni contaminación cruzada.

## Configurar otro proyecto de Supabase

1. Creá un proyecto en Supabase.
2. Ejecutá **una vez** [`supabase/migrations/202611090001_limalimon.sql`](supabase/migrations/202611090001_limalimon.sql) en SQL Editor de un proyecto vacío, o aplicala con la CLI. Crea tablas, permisos RLS, perfiles automáticos, funciones de administración y el bucket `recipe-images`.
3. Copiá `.env.example` a `.env.local` y completá la URL y la clave **publishable** (o `anon`):

   ```dotenv
   VITE_SUPABASE_URL=https://TU-PROYECTO.supabase.co
   VITE_SUPABASE_PUBLISHABLE_KEY=TU_CLAVE_PUBLICA
   ```

4. En Authentication → URL Configuration configurá Site URL y las Redirect URLs del sitio donde se ejecutará la aplicación (en desarrollo, el origen que imprime Vite). Agregá también la URL de recuperación `http://localhost:5173/?flow=recovery`, ajustando dominio/puerto en producción. Conservá la confirmación de correo habilitada; configurá SMTP para uso real.
5. Reiniciá Vite. La aplicación usará la base real; no recurrirá a la demo si falla la conexión. Una base nueva comienza sin recetas.
6. Registrá la cuenta administradora y confirmá el correo. En SQL Editor, asigná el primer rol mediante una consulta explícita, sustituyendo el UUID de Authentication → Users:

   ```sql
   update public.profiles set role = 'admin' where id = 'UUID-DEL-ADMIN';
   ```

La clave pública está diseñada para el navegador; la protección está en RLS y en los permisos SQL. **Nunca pongas `service_role`, una clave `sb_secret_` ni contraseñas en variables `VITE_`**. Los correos y credenciales quedan en Supabase Auth, no en las tablas públicas de perfiles. El nombre, la preferencia alimentaria y el rol son públicos; favoritos solo puede leerlos su propietario.

Para desarrollo con Supabase local: instalá la CLI oficial, ejecutá `supabase start`, aplicá `supabase db reset` y usá la URL/clave pública de esa instancia. El proyecto incluye `supabase/config.toml`. El conjunto completo de servicios locales no fue arrancado durante esta migración: las pruebas SQL usan PostgreSQL con contratos mínimos de Auth/Storage.

## Migrar los datos de MySQL

Ver [la guía de migración](docs/MIGRACION.md). Incluye exportación privada, validación sin escritura, importación repetible, imágenes, roles y verificación de cuentas. No publiques el JSON con correos ni lo agregues a Git. Las contraseñas antiguas no se exportan; se establece una nueva mediante recuperación por correo.

## Verificar

```bash
npm run build       # TypeScript + compilación de producción
npm test            # Búsqueda, restricciones alimentarias y temporadas
npm run test:e2e    # Recorridos reales de navegador en modo demo
npm run test:db     # PostgreSQL en Docker: esquema, RLS y funciones de administración
```

Para E2E se usa `/usr/bin/chromium` si existe. En otras máquinas ejecutá `npx playwright install chromium` o definí `PLAYWRIGHT_CHROMIUM_EXECUTABLE` con la ruta del navegador. `test:db` requiere Docker local y elimina su contenedor al terminar. No conecta a una base de producción.

## GitHub Pages

La rama incluye el workflow de publicación de Pages y el build bajo `/Recetas_limalimon1/`. Consultá [la guía de GitHub Pages](docs/GITHUB_PAGES.md) para la activación inicial, las URLs de Supabase y la verificación del despliegue.

```bash
npm run build:pages
npm run test:pages
```

Dirección prevista una vez desplegado: https://nicori25.github.io/Recetas_limalimon1/

## Publicar en otros alojamientos

```bash
npm run build
```

Publicá **solo `dist/`** en un alojamiento de sitios estáticos. Las rutas usan hash (`#/receta/...`), por lo que no necesitan reglas de reescritura. El proyecto incluido se usa por defecto. Si definís variables públicas en el alojamiento, completá la URL y la clave del mismo proyecto; para usar los valores incluidos, quitá esos overrides. Agregá el dominio publicado a las Redirect URLs de Supabase. No subas la raíz completa con PHP, SQL, exportaciones ni scripts privados. `netlify.toml` incluye la configuración de compilación de ejemplo.

Se configuró el proyecto remoto indicado por el propietario. No se ejecutó una importación de los datos originales en Supabase. El build, la demo, las políticas SQL en PostgreSQL y la validación del exportador se verificaron localmente. La autenticación por correo y Storage reales deben comprobarse al conectar el proyecto.
