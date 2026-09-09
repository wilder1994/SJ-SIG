# SJ-SIG — Informe de definición

**Producto:** SJ-SIG  
**Empresa:** SJ  
**Estado:** infraestructura v1 en local (Laravel 13)  
**Última actualización:** 2026-09-08  
**Objeto:** plataforma de gestión, supervisión, trazabilidad y análisis del servicio de vigilancia y seguridad privada, para cumplimiento del factor 4.2.4 (herramienta tecnológica, 2,0 puntos) y uso operativo multi-cliente.

Este documento es la bitácora de producto. Cada decisión de alcance, tenancy, módulo o anexo se registra aquí antes de construir.

---

## 1. Nombre

| Campo | Valor |
|--------|--------|
| Marca | **SJ-SIG** |
| Lectura | “ese jota sig” |
| Expansión de trabajo | Sistema Integral de Gestión (SJ) |
| Carpeta / repo | `SJ-SIG` |
| Nombre en Anexo 7.4 | Plataforma **SJ-SIG** |

Nombre provisional acordado. No se usa “Gestión” ni “Atalaya” / “SIGESTA” como marca.

---

## 2. Contexto de licitación

**Factor:** 4.2.4 Ecosistema tecnológico integral (máx. 9,0), asignado de manera proporcional.  
**Subfactor documentado aquí:** herramienta tecnológica para la gestión, supervisión, trazabilidad y análisis del servicio contratado (**2,0 puntos**). El resto de subfactores (hasta 9,0) no forma parte de este alcance hasta que se cruce el texto.

**Calificación:** el proponente suscribe y presenta el **Anexo 7.4** totalmente diligenciado y firmado por el representante legal, comprometiendo la puesta a disposición de la herramienta al **Departamento Administrativo de Contratación Pública** para el seguimiento del contrato de vigilancia y seguridad privada.

El puntaje de este subfactor es binario a efectos prácticos: los **seis parámetros técnicos** deben poder demostrarse. Un módulo omitido invalida el compromiso. La tabla del pliego no reparte décimas por ítem.

**Párrafo de propósito (pliego):** la entidad requiere mayor control y supervisión de la ejecución, y que la herramienta **facilite la gestión de novedades** de la ejecución contractual. Ese módulo no aparece numerado en la tabla de puntaje; se incluye en el alcance.

Pendiente: texto exacto del Anexo 7.4 (casillas, plazos, URL, evidencias) y resto del 4.2.4.

---

## 3. Parámetros técnicos (Anexo 7.4)

| # | Requisito del pliego | Decisión de producto |
|---|----------------------|----------------------|
| 1 | Acceso por navegador web; control de roles; repositorio/gestor documental (HV, certificados, cursos, etc.) del personal de vigilancia **asociado al servicio u operación con sus clientes** | Obligatorio. Web HTTPS. Roles y policies. Documentos por persona y por contrato/cliente, no un archivo corporativo suelto. |
| 2 | Buscador por carpetas o archivos; visualizar los archivos de cada carpeta del personal | Obligatorio. Catálogo de carpetas por persona (HV, certificados, cursos, afiliaciones, otros). Búsqueda + visor in-app. |
| 3 | Visualizar cursos realizados por la empresa al personal: **título y fecha** | Obligatorio. Entidad mínima: título + fecha. Evidencia PDF opcional de refuerzo. |
| 4 | Visualizar EPS, caja de compensación y pensión; visualizar documentos **parafiscales de la empresa** | Obligatorio. Dos capas: ficha de persona (importable) vs. parafiscales de SJ por periodo (no van en la ficha de empleado). |
| 5 | **Llevar registro** y visualizar mantenimientos técnicos de infraestructura de seguridad electrónica | Obligatorio **en la plataforma** (alta + consulta + evidencias). El proceso operativo sigue en el área técnica de SJ. No es un CMMS interno. Origen v1: carga manual del técnico. |
| 6 | **Control** y visualizar la cantidad de servicios prestados a cada **puesto de la Alcaldía** | Obligatorio. Contador por puesto y periodo (semana / mes / vigencia). “Alcaldía” es este cliente; el modelo es `puesto` del contrato. |

---

## 4. Principios de diseño

1. **Aislamiento por cliente.** Los datos de un cliente/contrato no son visibles para otro. Un supervisor de la entidad A no existe en el universo de la entidad B (URL, búsqueda, archivos, reportes).
2. **El supervisor es del cliente.** Usuario externo, atado a **un contrato** (el pliego habla del seguimiento de “el contrato”). Sin selector de otros contratos ni de la operación interna de SJ. Multi-contrato del mismo cliente queda como extensión posterior.
3. **Seguridad electrónica = evidencia contractual**, no CMMS del área técnica.
4. **Tablero primero.** La entidad entra a un informe gerencial del contrato, no a un CRUD.
5. **Una plataforma, muchos clientes.** Se construye para esta licitación y se reutiliza en otros contratos de SJ.
6. **Nómina no es supervisión.** Salario, banco, cuenta y forma de pago no se muestran a roles de entidad ni salen en reportes de seguimiento.

---

## 5. Modelo de tenancy y roles

```
Empresa SJ (dueña de SJ-SIG)
  └── Cliente (tenant)     ← un universo; el supervisor se asigna aquí
        └── Contrato 1:1 (interno, no se elige en pantalla)
              └── Instalaciones / puestos (siguiente fase)
              └── Personal, documentos, parafiscales, electrónica, servicios, novedades
```

El usuario de **Operaciones** (jefe, coordinador, analista, patrulla) también va amarrado a **un cliente** y aparece en **Equipo SJ** para que la entidad vea quién de SJ está asignado. Gestión humana (`interno`) no sale en esa lista.

### Roles (v1)

| Rol | Actor | Alcance |
|-----|--------|---------|
| `admin_empresa` | SJ | Usuarios, clientes, toda la operación |
| `interno` | Empleado SJ (GH / corporativo) | **Todos** los clientes. Carga Excel/PDF. No administra usuarios |
| `operaciones` | Jefe, coordinador, analista, supervisor de patrulla | **Un cliente**. Visible en Equipo SJ. Consulta documental |
| `tecnico_electronica` | Área de infraestructura SJ | Solo mantenimientos del cliente asignado |
| `supervisor_entidad` | Usuario del cliente | Solo **su cliente**. Tablero, consulta, novedades |
| `consulta_entidad` | Auditoría / apoyo del cliente | Igual que supervisor, sin novedades |

Reglas de aislamiento:

- `tenant_id` en tablas de negocio; contrato 1:1 por cliente (oculto en UI).
- `interno` y `admin_empresa` ven todos los clientes (selector en el header).
- Entidad, operaciones y técnico: un cliente; 404 al cruzar IDs.
- Perfil de plataforma: solo lectura. Alta/edición de usuarios: solo Administración.
- Storage: `tenants/{tenant_id}/contracts/{contract_id}/...` y `avatars/` (no se versionan).
- Pruebas: `TenantIsolationTest` + `PlatformAccessTest` (usuarios/clientes/equipo).

---

## 6. Carga masiva de personal

La alta de personal v1 es **importación Excel** ejecutada por `interno` / `admin_empresa` **dentro del cliente activo**. El archivo no elige cliente.

### Plantilla oficial v1

| Campo | Valor |
|--------|--------|
| Archivo de referencia | `ficha_empleados SJ-SIG.xlsx` |
| Hoja | `WM` (el nombre de hoja no es contrato de parser) |
| Fila 1 | Encabezado técnico (clave de columnas). Es lo que lee el importador. |
| Fila 2 | Texto de ayuda para quien llena. **No se importa.** |
| Fila 3 en adelante | Un trabajador por fila. |

**Upsert:** misma `cedula` en el mismo tenant = actualizar ficha, no duplicar.  
**Validación:** filas válidas se cargan; las inválidas van a un log (fila, columna, motivo). Fila sin `cedula` = error.  
**Idempotencia y aislamiento:** el archivo no puede crear personas en otro tenant.

### Columnas de la plantilla (A–AC)

**Identidad y ficha:** `cedula` (obligatorio), `nombre`, `fecha_nac`, `tipo_documento` (C, CE, N, TI, PT), `lugar_exp_cedula`, `fecha_expedicion`, `lugar_residencia`, `direccion`, `telefono`, `tipo_sangre`, `sexo`, `escolaridad`, `estado_civil`, `numero_hijos`, `email`.

**Vinculación laboral con SJ:** `tipo_vinculacion`, `tipo_cotizante`, `fecha_ingreso`, `fecha_vencimiento_contrato` (contrato laboral SJ, **no** el contrato de la entidad), `fecha_retiro` (vacío = activo), `cargo` (código; requiere catálogo código → nombre en SJ-SIG), `tipo_contrato`.

**Ítem 4 (persona):** `codigo_eps`, `nombre_eps`, `codigo_afp`, `nombre_afp`, `nombre_caja_compensacion`.

**Extra (no está en el Anexo 7.4):** `nombre_arp`, `nivel_riesgo_arp` (ARL). Se guarda; no es requisito de puntaje.

Esta plantilla **no incluye** salario, banco, cuenta, forma de pago, centros de costo ni organigrama WM. Esa recorte es deliberado.

### Qué crea y qué no crea este Excel

| Crea / actualiza | No cubre (otros flujos) |
|------------------|-------------------------|
| Persona en el tenant | Asignación a **puesto del contrato** (ítem 6) |
| Ficha + EPS / AFP / caja | Carpetas y archivos (HV, certificados) |
| Estado activo/retiro | Cursos (título + fecha) |
| Asignación al contrato donde se importa | Parafiscales de **empresa** por periodo |

**Puesto:** la ficha no trae puesto de la Alcaldía. Decisión: **no mapear** centros de costo WM a puestos. Tras el import, asignación en un segundo paso (Excel corto `cedula` + código/nombre de puesto, o pantalla). Columna extra `puesto` en la ficha queda como extensión si más adelante se unifica en un solo archivo.

### Personal vs Documentos (IA de producto)

**Personal** es quién es el vigilante: buscador, tabla, alta unitaria, carga masiva. **Ver ficha** muestra identidad, EPS/pensión/caja/ARL en texto y el conteo de archivos, con enlace a la carpeta. No hay visor ni subidas ni cursos en esa pantalla.

**Documentos** es una fila por empleado (no por archivo). Filtro por nombre/cédula. **Ver carpeta** es el expediente: HV, certificados, cursos (título + fecha y actas PDF), afiliaciones PDF, otros. Supervisor y operaciones: Ver/Descargar. Interno/admin: **Cargar documentos** (`?cargar=1`).

La entidad no valida con el texto de la tabla: ve **PDF reales**, previsualizados in-app (`/documentos/archivo/{id}/ver`) y con descarga opcional.

| Carpeta | Contenido | Carga |
|---------|-----------|--------|
| Hoja de vida | HV | Documentos → carpeta |
| Certificados | Aptitud, armas, escolta, policía, etc. | Documentos → carpeta |
| Cursos | Título y fecha + acta/diploma PDF | Documentos → carpeta |
| Afiliaciones | Certificado PDF de EPS, pensión y caja | Documentos → carpeta (nombres siguen en la ficha) |
| Otros | Cédula, foto, RUT | Documentos → carpeta |

Alta unitaria: Personal → `Nuevo empleado`. Parafiscales: PDF de **empresa** por periodo (PILA), no de la persona.

---

## 7. Módulos previstos

| # | Módulo | Estado v1 local |
|---|--------|-----------------|
| 1 | Tenancy, roles, test de aislamiento | Hecho (`TenantIsolationTest` + `PlatformAccessTest`) |
| 2 | Clientes + usuarios de plataforma | Hecho (alta de universo y cuentas). Instalaciones/puestos pendientes |
| 3 | Personal + import Excel + gestor documental | Hecho: ficha vs carpeta (ver §6) |
| 4 | Asignación persona ↔ puesto | Pendiente |
| 5 | Cursos (título + fecha + acta) | Hecho, en Documentos → carpeta |
| 6 | EPS / caja / pensión (ficha) + parafiscales empresa | Hecho (nombres en ficha; PDF en carpeta / PILA en Parafiscales) |
| 7 | Activos electrónicos + mantenimientos | Alta de mantenimiento; evidencias PDF por activo pendientes de pulir |
| 8 | Servicios por puesto | Consulta seed |
| 9 | Novedades de ejecución | Alta + listado |
| 10 | Dashboard y reportes (semana, mes, vigencia) | Tablero KPI; exportación PDF/Excel pendiente |
| 11 | Usuarios demo A vs B + interno/ops | Hecho |

### Rutas de evidencia (v1)

| Recurso | Listado | Carpeta / alta | Visor | Descarga |
|---------|---------|----------------|-------|----------|
| Clientes | `GET /clientes` | `GET/POST /clientes`, `PUT /clientes/{id}` | — | — |
| Usuarios | `GET /usuarios` | `GET/POST /usuarios`, `PUT /usuarios/{id}` | foto `/usuarios/foto/{id}` | — |
| Equipo SJ | `GET /equipo` | — | — | — |
| Personal | `GET /personal` | `GET /personal/nuevo`, `POST /personal`, `POST /personal/importar` | — | — |
| Documentos | `GET /documentos` | `GET/POST /documentos/carpeta/{person}` (+ `/cursos`) | `.../archivo/{id}/ver` | `.../archivo/{id}/descarga` |
| Parafiscales | `GET /parafiscales` | `POST /parafiscales` | `.../{id}/ver` | `.../{id}/descarga` |

Carga de PDF y cursos: `admin_empresa` e `interno` (`canUploadEvidence`). Entidad y operaciones: Ver/Descargar.  

### Tablero (visión)

- Semáforo documental (HV, certificados, cursos, seguridad social, parafiscal del mes).  
- Servicios por puesto (semana / mes / acumulado).  
- Mantenimientos: últimos, vencidos, sin evidencia.  
- Novedades abiertas vs. cerradas.  
- Personal activo y documentos por vencer (30 / 15 / 7 días).  
- Exportación PDF/Excel para acta de supervisión.

---

## 8. Arquitectura técnica

Entorno: Laragon, PHP 8.3, Laravel 13, Vite 8, Tailwind 4.

Capas: `Controllers` → `Services` → `Repositories` → `Models`. Scope de contrato en middleware `contract.bound`. Clientes: `CreateClientService`. Usuarios: `PersistPlatformUserService`. Importación Excel: `ImportPersonnelWorkbookService`. Alta unitaria: `CreatePersonService`. Expediente: `StorePersonDocumentService`, `StoreCourseService`, `StoreParafiscalService`. Visor: `StoredFileResponder`. Storage: `storage/app/tenants/...` y `storage/app/avatars/` (no se versionan).

UI: layout compacto gerencial (rail, tarjetas, KPIs). Paleta del logo SJ Seguridad Privada Ltda.: navy `#0b3d91`, azure `#1c7ae6`, cian `#58c4ff`, papel plata `#e8eef6`, tinta `#0b1220`. No se usa beige/oro.

Local aislado:

- LAN por IP (puerto **8086**, no usa `:80` de Armory): `http://172.16.16.70:8086` o `http://192.168.18.14:8086`
- Vhost Apache: `00-aae-sj-sig.conf` (`Listen 8086` + `sj-sig.test` en 80/443). Recargar Apache en Laragon.
- Base de datos propia: `sj_sig`
- Demo: clave `Sig2026!` — `admin@sj-sig.test`, `interno@sj-sig.test`, `ops.a@sj-sig.test`, `supervisor.a@sj-sig.test` vs `supervisor.b@sj-sig.test`. Tras este corte: `php artisan migrate:fresh --seed`.

---

## 9. Entregables de oferta vs. ejecución

| Momento | Qué debe existir |
|---------|------------------|
| Oferta | Anexo 7.4 completo, firmado, nombrando **SJ-SIG** y los seis parámetros |
| Adjudicación / supervisión | Ambiente web, usuario de la entidad, datos del contrato, tablero, aislamiento demostrable, personal cargable por plantilla |

---

## 10. Decisiones abiertas

- Texto literal del Anexo 7.4 y del resto del 4.2.4 (puntos que completan los 9,0).  
- Expansión formal definitiva de “SIG” (Gestión vs. Gestión y Seguimiento).  
- Segundo archivo vs. pantalla para asignar persona ↔ puesto.  
- Hosting (Laragon/demo local vs. URL HTTPS de presentación).

---

## 11. Historial

| Fecha | Cambio |
|--------|--------|
| 2026-09-08 | Creación del informe. Marca **SJ-SIG**. Alcance de los 6 parámetros. Tenancy multi-cliente. Seguridad electrónica como consulta/evidencia. Carpeta de proyecto renombrada desde `Gestion`. |
| 2026-09-08 | Cruce con texto del pliego 4.2.4 (subfactor 2,0). Supervisor atado a un contrato. Novedades en alcance. Carga masiva: plantilla `ficha_empleados SJ-SIG.xlsx` (fila 1 encabezado, fila 2 ayuda, fila 3+ datos). Nómina oculta a la entidad. Puesto del contrato fuera de esa ficha. |
| 2026-09-08 | Scaffold Laravel 13. Tenancy, módulos 1–9, tablero gerencial, importación Excel, seed A/B, vhost Apache `sj-sig.test`, DB `sj_sig`. |
| 2026-09-08 | Acceso LAN por IP en puerto 8086. Documentación de arranque y vhost versionado en `docs/apache`. |
| 2026-09-08 | Paleta UI alineada al logo institucional (navy/azure/cian/plata), sin cambiar el diseño. |
| 2026-09-08 | Alta unitaria, carga PDF (HV, certificados, afiliaciones, cursos, parafiscales) y visor in-app. |
| 2026-09-08 | Separación Personal (ficha HR) vs Documentos (carpeta por vigilante). Cursos y PDF viven en Documentos. |
| 2026-09-08 | Módulos Clientes y Usuarios. Rol `interno` (todos los clientes) y `operaciones` (un cliente, listado Equipo SJ). Perfil de solo lectura. Clave con ojito y cambio en primer ingreso. |
