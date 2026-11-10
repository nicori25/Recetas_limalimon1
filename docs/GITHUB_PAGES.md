# Publicar LimaLimón en GitHub Pages

Dirección prevista del repositorio: **https://nicori25.github.io/Recetas_limalimon1/**.
Esta dirección solo queda activa cuando GitHub Pages termina un despliegue exitoso.

## Configuración incluida

- `npm run build:pages` compila con la base `/Recetas_limalimon1/`.
- Los archivos públicos, las ilustraciones de recetas (incluidas las rutas `/images/` guardadas en la base) y el favicon respetan esa carpeta.
- Vue Router mantiene sus rutas con hash; no se necesitan reglas de reescritura.
- Los enlaces de retorno de Auth apuntan a la carpeta de la aplicación.
- `.github/workflows/pages.yml` verifica y publica únicamente `dist/`, al recibir cambios de aplicación en `feat/vue-supabase`.
- El workflow usa la URL y la clave **pública** incluidas en el código. No necesita claves privadas de Supabase ni exportaciones de datos.
- El build se comprueba en un navegador bajo la ruta del repositorio con `npm run test:pages`; estas pruebas simulan el servicio remoto y no envían correos reales.

## Activación inicial en GitHub

1. Abrir **Settings → Pages** del repositorio:
   https://github.com/nicori25/Recetas_limalimon1/settings/pages
2. En **Build and deployment → Source**, elegir **GitHub Actions**.
3. En **Actions**, abrir **Publicar LimaLimón en GitHub Pages**. Si la primera ejecución falló porque Pages estaba deshabilitado, usar **Re-run all jobs** después de completar el paso 2.
4. Si GitHub solicita autorización para Actions, habilitarla para el repositorio. Si el entorno `github-pages` tiene restricciones de ramas, permitir `feat/vue-supabase` en sus reglas de despliegue.
5. Esperar a que los trabajos `build` y `deploy` finalicen correctamente y abrir la URL que informa `deploy`.

La activación inicial es una configuración del repositorio: no basta con subir un archivo HTML. La acción oficial `configure-pages` no puede activar automáticamente Pages usando el `GITHUB_TOKEN` predeterminado. Si la API no es accesible desde el entorno de trabajo, el propietario debe seleccionar la fuente en Settings. No es necesario compartir tokens ni contraseñas en el chat.

El workflow admite `workflow_dispatch`; GitHub muestra su botón de ejecución manual cuando el archivo también está disponible en la rama predeterminada. La publicación inicial en la rama de Vue se inicia por `push`; una ejecución existente se puede reintentar desde Actions.

## Redirecciones de Supabase Auth

En **Supabase → Authentication → URL Configuration**, configurar:

**Site URL**

```text
https://nicori25.github.io/Recetas_limalimon1/
```

**Redirect URLs**

```text
https://nicori25.github.io/Recetas_limalimon1/
https://nicori25.github.io/Recetas_limalimon1/?flow=recovery
```

Conservar además los orígenes locales si se sigue desarrollando en ellos. Revisar las plantillas de correo si fueron personalizadas: deben respetar el destino permitido por Auth. La clave pública de la aplicación no permite administrar esta configuración del proyecto.

La publicación del frontend no crea tablas, no importa recetas y no configura SMTP. Esos elementos siguen perteneciendo a Supabase.

## Comprobación local del build

```bash
npm ci
npm run build:pages
npm run test:pages
npm run preview:pages
```

El servidor de preview debe abrirse usando `/Recetas_limalimon1/`, con el puerto que informe Vite. Para el desarrollo normal se conserva `npm run dev` en la raíz. `npm run build` sigue disponible para alojamientos que sirven el sitio desde `/`.

## Diagnóstico

- **404 en imágenes:** comprobar que se desplegó el build de Pages y no una compilación antigua con rutas absolutas a `/images/`.
- **404 o “Get Pages site failed” en configure-pages:** seleccionar GitHub Actions en Settings → Pages y reintentar la ejecución.
- **Despliegue rechazado por el entorno:** revisar las reglas de ramas y revisores del entorno `github-pages`.
- **Se publica la versión PHP:** usar GitHub Actions y el artefacto `dist/`, no servir la rama `main` original desde su raíz.
- **El correo vuelve a localhost:** corregir Site URL y Redirect URLs en Supabase y solicitar un enlace nuevo.
- **El frontend abre, pero falla la carga de datos:** comprobar el proyecto Supabase y sus políticas; Pages solo sirve archivos estáticos.
