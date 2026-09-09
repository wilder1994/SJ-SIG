# SJ-SIG

Sistema Integral de Gestión de vigilancia y seguridad privada (SJ). Bitácora de producto: [`docs/informe-definicion.md`](docs/informe-definicion.md).

Factor 4.2.4 del pliego (herramienta tecnológica, 2,0 puntos): tablero de supervisión, gestor documental, cursos, afiliaciones, parafiscales, electrónica, servicios por puesto y novedades. Multi-cliente con aislamiento por tenant/contrato.

## Acceso local

Puerto **8086** (no usa el `:80`; Armory permanece en `172.16.16.70`).

| Red | URL |
|-----|-----|
| Ethernet | http://172.16.16.70:8086/ingreso |
| Wi-Fi | http://192.168.18.14:8086/ingreso |
| Hosts local | http://sj-sig.test (tras recargar Apache) |

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
4. `php artisan migrate:fresh --seed` (o solo `php artisan migrate` si ya hay datos y solo faltan tablas nuevas como `sites`).
5. Copiar [`docs/apache/00-aae-sj-sig.conf`](docs/apache/00-aae-sj-sig.conf) a `C:\laragon\etc\apache2\sites-enabled\` (no reemplaza otros vhosts) y recargar Apache.
6. Alternativa: `php artisan serve --host=0.0.0.0 --port=8086`.

Firewall: permitir TCP **8086** en red privada si otros PCs no entran. En Laragon, si `php` no está en el PATH: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate`.

## Personal vs Documentos

| Módulo | Qué es | Quién carga |
|--------|--------|-------------|
| **Personal** | Quién es: buscador, Excel, alta unitaria, ficha | Interno / admin |
| **Documentos** | Carpeta por vigilante. Visor in-app | Interno / admin cargan; entidad y operaciones consultan |
| **Parafiscales** | PILA de empresa por periodo | Interno / admin |
| **Clientes / Usuarios** | Universos y cuentas de plataforma | Solo administración |
| **Instalaciones** | Plantas/bodegas → puestos (modalidad + unidades) | Interno / admin crean; entidad consulta |
| **Equipo SJ** | Operaciones asignadas al cliente | Visible para la entidad |

Flujo: Personal → Nuevo empleado (o Excel) → Documentos → Ver carpeta → **Cargar documentos**.

Plantilla `ficha_empleados SJ-SIG.xlsx`: fila 1 encabezado, fila 2 ayuda, fila 3+ trabajadores. Upsert por cédula dentro del contrato actual.

## Repositorio

https://github.com/wilder1994/SJ-SIG
