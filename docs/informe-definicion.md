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
  └── Cliente / Entidad contratante     ← límite de confidencialidad (tenant)
        └── Contrato
              └── Puestos, personal, documentos, cursos,
                  afiliaciones, parafiscales, activos electrónicos,
                  mantenimientos, servicios, novedades
```

### Roles (v1)

| Rol | Actor | Alcance |
|-----|--------|---------|
| `admin_empresa` | SJ | Configuración, usuarios, todos los clientes |
| `operador` | SJ | Clientes asignados: **carga masiva de personal**, carga documental, cursos, servicios, novedades |
| `tecnico_electronica` | Área de infraestructura SJ | Solo activos y mantenimientos de clientes asignados |
| `supervisor_entidad` | Cliente (p. ej. Alcaldía / Contratación Pública) | Solo **su contrato**: consulta, tablero, novedades de seguimiento |
| `consulta_entidad` | Auditoría / apoyo del cliente | Igual que supervisor, sin registrar novedades |

Reglas de aislamiento a implementar (cuando haya código):

- `tenant_id` en tablas de negocio; `contract_id` en datos contractuales.
- Global scope por tenant; policies sobre cada recurso y cada descarga.
- Storage: `tenants/{tenant_id}/contracts/{contract_id}/...`
- Prueba de aceptación: dos logins de supervisor, dos mundos; 403/404 al cruzar IDs.

---

## 6. Carga masiva de personal

Contratos de este tamaño (100–600 personas) no se digitán uno a uno. La alta de personal v1 es **importación Excel** ejecutada por `operador` / `admin_empresa` **dentro de un contrato ya seleccionado**. El archivo no elige cliente ni contrato.

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

---

## 7. Módulos previstos

Orden de construcción acordado (aún no iniciado):

1. Tenancy, roles, tests de aislamiento  
2. Clientes, contratos, puestos  
3. Personal + **importación plantilla SJ-SIG** + gestor documental + búsqueda  
4. Asignación persona ↔ puesto del contrato  
5. Cursos  
6. EPS / caja / pensión (ficha) + parafiscales de empresa  
7. Activos electrónicos + mantenimientos (registro, consulta y evidencias)  
8. Servicios por puesto  
9. Novedades de ejecución  
10. Dashboard y reportes (semana, mes, vigencia)  
11. Usuarios demo (supervisor A vs supervisor B) para demostración de isolation  

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

Capas: `Controllers` → `Services` → `Repositories` → `Models`. Policies por recurso. Scope global de tenant. Importación Excel en `ImportPersonnelWorkbookService`.

Local aislado:

- LAN por IP (puerto **8086**, no usa `:80` de Armory): `http://172.16.16.70:8086` o `http://192.168.18.14:8086`
- Vhost Apache: `00-aae-sj-sig.conf` (`Listen 8086` + `sj-sig.test` en 80/443). Recargar Apache en Laragon.
- Base de datos propia: `sj_sig`
- Demo: clave `Sig2026!` — `supervisor.a@sj-sig.test` vs `supervisor.b@sj-sig.test`

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
