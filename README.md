# SJ-SIG

Sistema Integral de Gestión de vigilancia y seguridad privada (SJ). Bitácora de producto: [`docs/informe-definicion.md`](docs/informe-definicion.md).

Factor 4.2.4 del pliego (herramienta tecnológica, 2,0 puntos): tablero de supervisión, gestor documental, cursos, afiliaciones, parafiscales, electrónica, servicios por puesto y novedades. Multi-cliente con aislamiento por tenant/contrato.

## Acceso local / LAN

Puerto **8086** en **todas las interfaces** (`0.0.0.0`). Cualquier equipo de la misma red entra con la IP del servidor Laragon:

`http://<IP-del-servidor>:8086/ingreso`

| Desde | URL típica |
|-------|------------|
| Este PC (hosts) | http://sj-sig.test (Apache 80/443) |
| Misma red Wi‑Fi / Ethernet | `http://IP:8086/ingreso` (ver IP abajo) |

**IPs actuales del servidor** (cambian si el router asigna otra): consultar con `ipconfig` o el script de firewall.

| Adaptador | URL |
|-----------|-----|
| Ethernet (cable, red actual) | `http://172.16.16.70:8086/ingreso` |
| Wi‑Fi `sjsp.net` (si está activo) | `http://172.16.23.47:8086/ingreso` |

**Firewall (obligatorio para otros PCs):** PowerShell **como Administrador**:

```powershell
cd C:\laragon\www\SJ-SIG
.\docs\apache\abrir-firewall-8086.ps1
```

Vhost: `docs/apache/00-aae-sj-sig.conf` → `C:\laragon\etc\apache2\sites-enabled\` y **Reload Apache** en Laragon.

Tras `php artisan migrate:fresh --seed` queda **solo el administrador**. Correo y clave salen de `.env` (`ADMIN_EMAIL`, `ADMIN_PASSWORD`; nombre opcional `ADMIN_NAME`). Por defecto: `admin@sj-sig.test` / `Sig2026!`.

Los clientes, contratos, puestos, personal y usuarios de la entidad se crean en **Clientes** y **Usuarios**. El administrador no está atado a un tenant (ve todos).

El usuario **no edita su perfil**. Cambios: solo Administración. Primer ingreso de un alta nueva: cambio obligatorio de clave. Ojito para ver la clave en login y formularios.

## Stack

Laravel 13, PHP 8.3, MySQL (`sj_sig`), Vite 8, Tailwind 4. Capas: Controller → Service → Repository → Model + policies y scope de tenant.

UI: mismo layout gerencial; paleta institucional del logo SJ (navy, azure, cian, plata/acero). Tokens en `resources/css/app.css`.

## Arranque (Laragon)

1. Copiar `.env.example` → `.env` y `php artisan key:generate`.
2. Crear base `sj_sig` (utf8mb4). Credenciales locales típicas: `root` / vacío.
3. `composer install` y `npm install && npm run build`.
4. En `.env` poner `GOOGLE_MAPS_API_KEY` (Maps JavaScript + Places + Geocoding; restringir a `http://IP:8086/*` y `http://sj-sig.test/*`). Sin esa clave el mapa muestra aviso.
5. `php artisan migrate:fresh --seed` (esquema unificado + admin). Si la base ya está en este esquema: `php artisan migrate` y, si falta el admin, `php artisan db:seed`. Tras pull: `npm install && npm run build` (PDF.js + mapas).
6. Copiar [`docs/apache/00-aae-sj-sig.conf`](docs/apache/00-aae-sj-sig.conf) a `C:\laragon\etc\apache2\sites-enabled\` (no reemplaza otros vhosts) y recargar Apache.
7. Alternativa: `php artisan serve --host=0.0.0.0 --port=8086`.

Firewall: permitir TCP **8086** en red privada si otros PCs no entran. En Laragon, si `php` no está en el PATH: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate`.

## Personal vs Documentos

| Módulo | Qué es | Quién carga |
|--------|--------|-------------|
| **Personal** | Quién es: buscador, Excel, alta unitaria, ficha y foto circular | Interno / admin |
| **Documentos** | Listado por vigilante (cédula, carpetas con PDF, documentos reales). En la carpeta: foto, tarjetas y modal. HV (26), Contratación (9), Certificados (3), Cursos y capacitación (25+otro), Afiliaciones (8) y Otros (hasta 20, tipo libre) | Interno/admin: foto + modal PDF + indexar; entidad consulta tarjetas/modal |
| **Parafiscales** | PILA de empresa por periodo | Interno / admin |
| **Clientes / Usuarios** | Ficha del universo (identidad, contacto, representante) + georreferencia Google | Solo administración |
| **Instalaciones** | Plantas/bodegas con dirección/mapa → puestos (modalidad + unidades) | Interno / admin crean; entidad consulta |
| **Equipo SJ** | Operaciones asignadas al cliente | Visible para la entidad |

Flujo actual: Clientes (ficha + pin) → Usuarios / Instalaciones (dirección + pin) → Tablero (mapa: navy = cliente, cian = sedes) → Personal → Nuevo empleado (foto circular) → Documentos → Ver carpeta (tarjetas) → **Cargar documentos**.

Sin cliente o sin datos, cada módulo muestra un aviso centrado con el siguiente paso (no un 404).

**Historia Laboral** (26; EPS/AFP/cesantías del empleado), **Contratación** (9; todos obligatorios), **Certificados** (3), **Cursos y capacitación** (catálogo Super + otro; fecha y entidad), **Afiliaciones** (8; las hace la empresa) y **Otros** (tipo libre, máx. 20; el nombre no puede cruzar con las otras listas): **un solo PDF** (`POST /documentos/carpeta/{person}/lote`). Interno/admin: **Cargar documentos** (modal) → Indexar lote → por cada grupo de páginas elige **carpeta** y **tipo**. Las páginas ya agregadas a la lista salen del preview (Quitar las devuelve). En la carpeta: foto circular, tarjetas (`N de total`), **Volver**, modal con buscador (Ver/Descargar; Eliminar 12 h). N/A en pendientes. Tope: **50 MB**. **Escanear** pendiente. Tras pull: `php artisan migrate:fresh --seed` (o `migrate` si el esquema ya está unificado) y `npm run build`. Tests: `php artisan test` (usan `TestingSeeder`, no el seed de producción).

Plantilla `ficha_empleados SJ-SIG.xlsx`: fila 1 encabezado, fila 2 ayuda, fila 3+ trabajadores. Upsert por cédula dentro del contrato actual.

## Repositorio

https://github.com/wilder1994/SJ-SIG
