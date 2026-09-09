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

**IPs actuales del servidor** (cambian si el router asigna otra): consultar con `ipconfig` o el script de firewall. Wi‑Fi actual (`sjsp.net`): `http://172.16.23.47:8086/ingreso`.

**Firewall (obligatorio para otros PCs):** PowerShell **como Administrador**:

```powershell
cd C:\laragon\www\SJ-SIG
.\docs\apache\abrir-firewall-8086.ps1
```

Vhost: `docs/apache/00-aae-sj-sig.conf` → `C:\laragon\etc\apache2\sites-enabled\` y **Reload Apache** en Laragon.

Clave demo: `Sig2026!`

| Correo | Rol | Cliente |
|--------|-----|---------|
| `admin@sj-sig.test` | Administración | Todos |
| `interno@sj-sig.test` | Usuario interno | Todos |
| `ops.a@sj-sig.test` | Operaciones | Alcaldía A |
| `ops.b@sj-sig.test` | Operaciones | Entidad B |
| `tecnico@sj-sig.test` | Técnico | Alcaldía A |
| `supervisor.a@sj-sig.test` | Supervisor de cliente | Alcaldía A |
| `supervisor.b@sj-sig.test` | Supervisor de cliente | Entidad B |

El usuario **no edita su perfil**. Cambios: solo Administración. Primer ingreso de un alta nueva: cambio obligatorio de clave. Ojito para ver la clave en login y formularios.

## Stack

Laravel 13, PHP 8.3, MySQL (`sj_sig`), Vite 8, Tailwind 4. Capas: Controller → Service → Repository → Model + policies y scope de tenant.

UI: mismo layout gerencial; paleta institucional del logo SJ (navy, azure, cian, plata/acero). Tokens en `resources/css/app.css`.

## Arranque (Laragon)

1. Copiar `.env.example` → `.env` y `php artisan key:generate`.
2. Crear base `sj_sig` (utf8mb4). Credenciales locales típicas: `root` / vacío.
3. `composer install` y `npm install && npm run build`.
4. `php artisan migrate:fresh --seed` (o solo `php artisan migrate` si ya hay datos: `sites`, `document_batches`, `pages` JSON). Tras pull hace falta `npm install && npm run build` (PDF.js).
5. Copiar [`docs/apache/00-aae-sj-sig.conf`](docs/apache/00-aae-sj-sig.conf) a `C:\laragon\etc\apache2\sites-enabled\` (no reemplaza otros vhosts) y recargar Apache.
6. Alternativa: `php artisan serve --host=0.0.0.0 --port=8086`.

Firewall: permitir TCP **8086** en red privada si otros PCs no entran. En Laragon, si `php` no está en el PATH: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate`.

## Personal vs Documentos

| Módulo | Qué es | Quién carga |
|--------|--------|-------------|
| **Personal** | Quién es: buscador, Excel, alta unitaria, ficha | Interno / admin |
| **Documentos** | Carpeta por vigilante. HV (26), Contratación (9), Certificados (3), Cursos y capacitación (25+otro) y Afiliaciones (8) indexadas | Interno/admin: tarjetas + indexar; entidad consulta Listado/ojo |
| **Parafiscales** | PILA de empresa por periodo | Interno / admin |
| **Clientes / Usuarios** | Universos y cuentas de plataforma | Solo administración |
| **Instalaciones** | Plantas/bodegas → puestos (modalidad + unidades) | Interno / admin crean; entidad consulta |
| **Equipo SJ** | Operaciones asignadas al cliente | Visible para la entidad |

Flujo actual: Personal → Nuevo empleado (o Excel) → Documentos → Ver carpeta → **Cargar documentos**.

**Historia Laboral** (26; EPS/AFP/cesantías del empleado), **Contratación** (9; todos obligatorios), **Certificados** (3), **Cursos y capacitación** (catálogo Super + otro; fecha y entidad) y **Afiliaciones** (8; las hace la empresa): mismo indexador. Interno/admin: **Cargar documentos** → tarjeta PDF → **Indexar lote**. Consulta: **Listado** + ojo. Tope de archivo: **50 MB**. **Escanear** pendiente. Tras pull: `php artisan migrate` y `npm run build`. Test: `LaborHistoryIndexingTest`.

Plantilla `ficha_empleados SJ-SIG.xlsx`: fila 1 encabezado, fila 2 ayuda, fila 3+ trabajadores. Upsert por cédula dentro del contrato actual.

## Repositorio

https://github.com/wilder1994/SJ-SIG
