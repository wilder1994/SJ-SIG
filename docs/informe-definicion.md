# SJ-SIG — Informe de definición

**Producto:** SJ-SIG  
**Empresa:** SJ  
**Estado:** infraestructura v1 en local (Laravel 13)  
**Última actualización:** 2026-09-09  
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
| 2 | Buscador por carpetas o archivos; visualizar los archivos de cada carpeta del personal | Obligatorio. Catálogo de carpetas por persona (**Historia Laboral**, **Contratación**, **Certificados**, **Cursos y capacitación**, **Afiliaciones** y **Otros** indexadas). Búsqueda + visor in-app. |
| 3 | Visualizar cursos realizados por la empresa al personal: **título y fecha** | Obligatorio. Título + fecha + entidad que dicta + acta PDF indexada (catálogo Super + otro). |
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
              └── Instalación (planta, bodega, sede)
                    └── Puesto (portería, ronda…)
                          · modalidad: 8 / 12 / 24 h
                          · unidades: N vigilantes (cupo, no el listado de personas)
              └── Personal, documentos, parafiscales, electrónica, servicios, novedades
```

**Instalaciones y puestos** definen la capacidad contratada. El módulo **Personal** sigue siendo quién es cada vigilante; la asignación persona ↔ puesto queda para un paso posterior.

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
- Pruebas: `TenantIsolationTest` + `PlatformAccessTest` + `LaborHistoryIndexingTest`.

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

**Documentos** es una fila por empleado (no por archivo). Filtro por nombre/cédula. **Ver carpeta** es el expediente indexado. Supervisor y operaciones: consulta + preview. Interno/admin: **Cargar documentos** (`?cargar=1`) con una tarjeta PDF (arrastrar, pegar o seleccionar) y luego carpeta + tipo en el indexador.

| Carpeta | Contenido | Carga |
|---------|-----------|--------|
| **Historia Laboral** (antes “Hoja de vida”) | Checklist indexado de documentos de ingreso/selección (ver §6.1; 26 tipos). EPS/AFP/cesantías = certificados que **trae el empleado** | Tarjeta PDF → Indexar lote. Escáner pendiente |
| **Contratación** | Vinculación laboral (ver §6.5; 9 tipos, todos obligatorios) | Tarjeta PDF → Indexar lote |
| **Certificados** | Exámenes ocupacionales (ver §6.3; 3 tipos) | Tarjeta PDF → Indexar lote |
| **Cursos y capacitación** | Catálogo Superintendencia (25) + otro (ver §6.4). Fecha y entidad obligatorias | Tarjeta PDF → Indexar lote |
| Afiliaciones | Afiliaciones que **hace la empresa** al contratar (8 tipos) | Tarjeta PDF → Indexar lote. Nombres EPS/AFP/caja/ARL siguen en la ficha |
| **Otros** | Soportes sueltos (ver §6.6; tipo libre, máx. 20) | Tarjeta PDF → Indexar lote |

Alta unitaria: Personal → `Nuevo empleado`. Parafiscales: PDF de **empresa** por periodo (PILA), no de la persona.

### 6.1 Historia Laboral — gestión documental (hecho; escáner pendiente)

Objetivo: dejar de tratar la carpeta como “un PDF suelto” y pasar a **gestión documental indexada** por tipo de documento.

#### Alcance v1 (acordado)

- Renombrar etiqueta de carpeta `hv` → **Historia Laboral**.
- Catálogo fijo de tipos de documento (lista operativa SJ).
- En modo carga: **una** tarjeta PDF → **Indexar**. En el lote se elige carpeta y tipo por grupo de páginas (un PDF puede alimentar varias carpetas).
- Pantalla **Indexar lote**: dos columnas. Izquierda: carpeta + tipo/nombre/lista (scroll si crece). Derecha: miniaturas del PDF. Seleccionar páginas sueltas o un tramo con Shift+clic → carpeta → tipo → **Guardar** genera **un archivo por corte**.
- En consulta: botón **Listado** (reemplaza el “Ver” a nivel carpeta) → lista de tipos con estado (cargado / falta / N/A) e **icono ojo** para previsualizar cada archivo en el modal actual (`/documentos/archivo/{id}/ver`).
- Campos de trazabilidad en documento: `document_type`, `display_name`, opcionalidad / “si aplica”.

#### Fuera de v1 (pendiente de decisión)

- Botón **Escanear** con escáner TWAIN/WIA o agente local Windows. El navegador no habla al escáner nativo; se decidirá después (agente local vs. solo digitalización externa + Subir).
- Semáforo de completitud en el tablero (% Historia Laboral) puede venir justo después del indexador.

#### Catálogo de tipos (Historia Laboral)

| # | Documento | Notas |
|---|-----------|--------|
| 1 | Formato de requisición de personal | |
| 2 | Registro conocimiento del empleado | |
| 3 | Hoja de vida | |
| 4 | Fotocopia de la cédula de ciudadanía | |
| 5 | Certificado etnia – factor de vulnerabilidad | Si aplica |
| 6 | Foto 3×4 fondo blanco o azul | |
| 7 | Certificados de estudio formal y no formal | |
| 8 | Resolución de retiro de entidades de fuerza pública | Si aplica |
| 9 | Certificaciones laborales con verificación de referencias | |
| 10 | Libreta militar | Opcional |
| 11 | Verificación de antecedentes | |
| 12 | Licencia de conducción (categoría A2 / B1 según aplique) | |
| 13 | Tarjeta de propiedad (licencia de tránsito) | |
| 14 | SOAT (Seguro Obligatorio de Accidentes de Tránsito) | |
| 15 | Revisión tecno mecánica | |
| 16 | Evaluación de conocimiento | Si aplica |
| 17 | Entrevista selección | |
| 18 | Prueba psicotécnica | |
| 19 | Entrevista técnica de líder de área | |
| 20 | Estudio de confiabilidad | Si aplica |
| 21 | Prueba de poligrafía | Si aplica |
| 22 | Certificado de EPS | Del empleado, para contratar. Distinto de Afiliaciones |
| 23 | Certificado de AFP | Del empleado, para contratar. Distinto de Afiliaciones |
| 24 | Certificado de cesantías | Del empleado, para contratar. Distinto de Afiliaciones |
| 25 | Documentos de beneficiarios | |
| 26 | Certificación bancaria | |

#### Capas técnicas

- Enum `LaborHistoryDocumentType` (código, etiqueta, obligatorio / opcional / si aplica).
- Tabla `document_batches` + columnas en `person_documents` (`document_type`, `display_name`, `pages` JSON, `page_from`/`page_to` min/max, `not_applicable`).
- `StoreLaborHistoryBatchService` + `IndexLaborHistoryPdfService` (partir PDF por lista de páginas con FPDI).
- UI: `documents/folder` (consulta Listado + ojo; una dropzone PDF) + `documents/index-batch` (select de carpeta, tipos del catálogo activo, miniaturas PDF.js, páginas sueltas).
- Rutas de lote: `POST/GET /documentos/carpeta/{person}/lote`, `GET .../lote/{batch}/ver`, `POST .../lote/{batch}`. El PDF vive en `.../lote/batches/{uuid}.pdf`. `document_batches.folder` queda null; la carpeta va en cada corte (`slices.*.folder`).
- Sin cambiar el aislamiento por cliente/contrato.

### 6.2 Afiliaciones — gestión documental (hecho)

Mismo flujo que Historia Laboral. Catálogo de **8 tipos**, todos obligatorios: afiliaciones que **hace la empresa** al contratar. Los certificados homónimos de HV los **trae el empleado**; no se cruzan. Los nombres de EPS/AFP/caja/ARL siguen en la ficha.

| # | Documento | Código |
|---|-----------|--------|
| 1 | Certificado estado de afiliaciones | `certificado_estado_afiliaciones` |
| 2 | Afiliación a EPS | `certificado_eps` |
| 3 | Afiliación a cesantías | `certificado_cesantias` |
| 4 | Afiliación a fondo de pensiones | `certificado_afp` |
| 5 | Afiliación a ARL | `afiliacion_arl` |
| 6 | Afiliación a caja de compensación familiar | `certificado_caja` |
| 7 | Afiliación seguro de vida | `afiliacion_seguro_vida` |
| 8 | Afiliación o carta de desistimiento de seguro exequial | `afiliacion_exequial` |

Mismo lote único: en el indexador se elige carpeta Afiliaciones y el tipo. Consulta: **Listado** + ojo. N/A disponible en modo carga. Enum `AffiliationDocumentType`. Demo: 3/8 (EPS, pensiones y caja).

### 6.3 Certificados — gestión documental (hecho)

Los exámenes ocupacionales salieron de Historia Laboral. Catálogo de **3 tipos**:

| # | Documento | Código | Notas |
|---|-----------|--------|-------|
| 1 | Examen médico ocupacional de ingreso | `examen_medico_ingreso` | Obligatorio |
| 2 | Examen psicofísico | `examen_psicofisico` | Si aplica |
| 3 | Examen psicosensométrico | `examen_psicosensometrico` | Si aplica |

Mismo indexador. Enum `CertificateDocumentType`. Demo: 1/3 (el PDF de aptitud se mapea al examen de ingreso). Documentos ya indexados en HV con esos códigos se reubican a `certificados` en la migración.

### 6.4 Cursos y capacitación — gestión documental (hecho)

Salieron de Historia Laboral. Carpeta indexada con catálogo Superintendencia (**25** tipos, *si aplica*) más **Otro** (repetible, nombre libre). Cada acta exige **fecha** y **entidad** que dicta el curso. Enum `CourseDocumentType`. Demo: 1/25 (especialización en manejo defensivo). Pliego ítem 3 (título + fecha) queda cubierto; la entidad es extra operativa.

| # | Curso Superintendencia |
|---|------------------------|
| 1 | Curso de fundamentación en vigilancia |
| 2 | Curso de reentrenamiento en vigilancia |
| 3 | Especialización en seguridad comercial |
| 4 | Especialización en entidades oficiales |
| 5 | Especialización en grandes superficies |
| 6 | Especialización en seguridad residencial |
| 7 | Especialización en seguridad aeroportuaria |
| 8 | Especialización en sector financiero |
| 9 | Especialización en sector petrolero |
| 10 | Especialización en sector industrial |
| 11 | Especialización en sector hospitalario |
| 12 | Curso de fundamentación en escolta |
| 13 | Curso de reentrenamiento en escolta |
| 14 | Especialización en escolta a personas |
| 15 | Especialización en manejo defensivo |
| 16 | Curso de fundamentación para supervisor |
| 17 | Curso de reentrenamiento para supervisor |
| 18 | Curso de fundamentación en operador de medios tecnológicos |
| 19 | Curso de reentrenamiento en operador de medios tecnológicos |
| 20 | Especialización para coordinador |
| 21 | Especialización para instalador |
| 22 | Seminario para directivos de seguridad privada |
| 23 | Seminario para coordinadores de seguridad |
| 24 | Seminario para jefes de recursos humanos en seguridad privada |
| 25 | Curso de manejo y uso de armas de fuego |
| — | Otro curso o capacitación (nombre libre; se pueden agregar varios) |

### 6.5 Contratación — gestión documental (hecho)

Mismo flujo que Afiliaciones (sin fecha/entidad extra). Catálogo de **9 tipos**, todos obligatorios: documentos de vinculación. No se cruza con Historia Laboral.

| # | Documento | Código |
|---|-----------|--------|
| 1 | Contrato de trabajo | `contrato_trabajo` |
| 2 | Cláusula de confidencialidad | `clausula_confidencialidad` |
| 3 | Conocimiento código de ética y conducta | `conocimiento_codigo_etica` |
| 4 | Acuerdo de responsabilidad laboral | `acuerdo_responsabilidad_laboral` |
| 5 | Perfil del cargo | `perfil_del_cargo` |
| 6 | Certificado de inducción o reinducción corporativa | `induccion_reinduccion_corporativa` |
| 7 | Registro fotográfico y de huellas dactilares | `registro_fotografico_huellas` |
| 8 | Entrega de carné | `entrega_carne` |
| 9 | Carta de presentación del empleado | `carta_presentacion_empleado` |

Orden en UI: Historia Laboral → **Contratación** → Certificados → Cursos y capacitación → Afiliaciones → Otros. Enum `ContractingDocumentType`. Demo: 0/9 (no se siembra).

### 6.6 Otros — gestión documental (hecho)

Mismo indexador (PDF, páginas sueltas). No hay catálogo fijo: el usuario **digita el tipo**. El nombre del archivo se arma con el slug del tipo + cédula + nombre (editable). Tope **20** soportes por trabajador. Consulta: **Listado** `N/20 soportes` + ojo. Sin N/A. Demo: 0/20 (no se siembra).

Si el tipo o el nombre coincide con un documento de Historia Laboral, Contratación, Certificados, Cursos o Afiliaciones, no se guarda: debe cambiar el tipo o cargarlo en esa carpeta. Se indexa en el lote único (`/lote`) eligiendo carpeta Otros. Enum `OtherDocumentType` (`otro_soporte`, repetible). Helper `OtherSupportNamer`.

---

## 7. Módulos previstos

| # | Módulo | Estado v1 local |
|---|--------|-----------------|
| 1 | Tenancy, roles, test de aislamiento | Hecho (`TenantIsolationTest` + `PlatformAccessTest`) |
| 2 | Clientes + usuarios + instalaciones/puestos | Hecho. Capacidad: modalidad + unidades por puesto. Asignación persona↔puesto pendiente |
| 3 | Personal + import Excel + gestor documental | Hecho. Un PDF + indexador (HV 26 + Contratación 9 + Certificados 3 + Cursos 25+otro + Afiliaciones 8 + Otros 20 tipo libre). Escáner pendiente |
| 4 | Asignación persona ↔ puesto | Pendiente |
| 5 | Cursos (título + fecha + acta) | Hecho. Cursos y capacitación indexados (catálogo Super + otro; fecha y entidad) |
| 6 | EPS / caja / pensión (ficha) + parafiscales empresa | Hecho. Afiliaciones indexadas (8 tipos). PILA en Parafiscales |
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
| Instalaciones | `GET /instalaciones` | `POST /instalaciones`, `POST .../{site}/puestos` | — | — |
| Personal | `GET /personal` | `GET /personal/nuevo`, `POST /personal`, `POST /personal/importar` | — | — |
| Documentos | `GET /documentos` | `GET /documentos/carpeta/{person}`; lote `POST/GET .../lote` (+ `/ver`, indexar); N/A por carpeta | `.../archivo/{id}/ver` | `.../archivo/{id}/descarga` |
| Parafiscales | `GET /parafiscales` | `POST /parafiscales` | `.../{id}/ver` | `.../{id}/descarga` |

Carga de PDF y cursos: `admin_empresa` e `interno` (`canUploadEvidence`). Entidad y operaciones: Ver/Descargar.  

### Tablero (visión)

- Semáforo documental (Historia Laboral, Contratación, Certificados, Cursos y capacitación, Afiliaciones y Otros indexadas, parafiscal del mes).  
- Servicios por puesto (semana / mes / acumulado).  
- Mantenimientos: últimos, vencidos, sin evidencia.  
- Novedades abiertas vs. cerradas.  
- Personal activo y documentos por vencer (30 / 15 / 7 días).  
- Exportación PDF/Excel para acta de supervisión.

---

## 8. Arquitectura técnica

Entorno: Laragon, PHP 8.3, Laravel 13, Vite 8, Tailwind 4.

Capas: `Controllers` → `Services` → `Repositories` → `Models`. Scope de contrato en middleware `contract.bound`. Clientes: `CreateClientService`. Usuarios: `PersistPlatformUserService`. Estructura: `PersistSiteService`, `PersistPostService`. Importación Excel: `ImportPersonnelWorkbookService`. Alta unitaria: `CreatePersonService`. Expediente: `StorePersonDocumentService`, `StoreParafiscalService`. Un lote PDF (`/lote`; `document_batches.folder` nullable) se parte por cortes con carpeta + tipo: `StoreLaborHistoryBatchService`, `IndexLaborHistoryPdfService`, `MarkLaborHistoryNotApplicableService` (FPDI; cursos: `taken_on` + `provider`; Otros: tipo libre, tope 20, `OtherSupportNamer`). Miniaturas del lote: PDF.js. Visor: `StoredFileResponder`. Storage: `storage/app/tenants/...` y `storage/app/avatars/` (no se versionan).

UI: layout compacto gerencial (rail, tarjetas, KPIs). Paleta del logo SJ Seguridad Privada Ltda.: navy `#0b3d91`, azure `#1c7ae6`, cian `#58c4ff`, papel plata `#e8eef6`, tinta `#0b1220`. No se usa beige/oro.

Local aislado:

- LAN: puerto **8086** en `0.0.0.0` (cualquier IP del servidor). URL: `http://<IP>:8086/ingreso`. Ejemplo Wi‑Fi `sjsp.net`: `http://172.16.23.47:8086/ingreso`. No usa `:80` si Armory u otro vhost lo ocupa.
- Tope de carga: **50 MB** por archivo (PDF del indexador; PDF/JPG/PNG en parafiscales). PHP Laragon ya admite más.
- Firewall Windows: script `docs/apache/abrir-firewall-8086.ps1` (Administrador) — regla *SJ-SIG LAN 8086*.
- Vhost: `docs/apache/00-aae-sj-sig.conf` → `sites-enabled` + Reload Apache. URLs generadas según el Host (`ForceRequestRootUrl`).
- Base de datos propia: `sj_sig`
- Tras pull: `composer install`, `npm install && npm run build`, `php artisan migrate` (p. ej. `document_batches.folder` nullable para el lote único). Demo: `php artisan migrate:fresh --seed`.
- Si `php` no está en PATH: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan …`

### Usuarios y claves de prueba (demo local)

Clave común: **`Sig2026!`** (variable `SEED_PASSWORD`).

| Correo | Rol | Cliente | Para qué |
|--------|-----|---------|----------|
| `admin@sj-sig.test` | Administración | Todos | Usuarios y clientes |
| `interno@sj-sig.test` | Usuario interno | Todos | Carga Excel/PDF, instalaciones |
| `ops.a@sj-sig.test` | Operaciones | Alcaldía A | Sale en Equipo SJ (A) |
| `ops.b@sj-sig.test` | Operaciones | Entidad B | Sale en Equipo SJ (B) |
| `tecnico@sj-sig.test` | Técnico | Alcaldía A | Solo electrónica |
| `supervisor.a@sj-sig.test` | Supervisor de cliente | Alcaldía A | Universo A |
| `supervisor.b@sj-sig.test` | Supervisor de cliente | Entidad B | Universo B |

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
- **Escáner** (carpetas indexadas): agente Windows / TWAIN vs. solo digitalización externa + Subir (v1 solo Subir + indexar).

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
| 2026-09-08 | Instalaciones → puestos con modalidad (8/12/24 h) y unidades (cupo de vigilantes). Tabla demo de usuarios/claves en el informe. |
| 2026-09-09 | Acceso LAN: Listen `0.0.0.0:8086`, script firewall y docs para cualquier equipo de la misma red. |
| 2026-09-09 | Especificación Historia Laboral (gestión documental indexada): catálogo de 26 tipos, Subir PDF + indexar, Listado con preview. Escáner dejado pendiente. |
| 2026-09-09 | Implementación Historia Laboral: lote PDF, indexador por rangos, checklist Listado + preview, N/A. Escáner sigue pendiente. |
| 2026-09-09 | Indexador: páginas no consecutivas + miniaturas PDF.js. |
| 2026-09-09 | Indexar lote: columna izquierda (contexto + lista) y derecha (páginas) con scroll independiente. |
| 2026-09-09 | Carga interna: tarjetas por carpeta (arrastrar, pegar o seleccionar; icono + nombre antes de confirmar). |
| 2026-09-09 | Afiliaciones indexadas: EPS, AFP, cesantías y caja de compensación (mismo flujo que Historia Laboral). |
| 2026-09-09 | Catálogo Afiliaciones ampliado a 8 tipos (estado, EPS, cesantías, pensiones, ARL, caja, vida, exequial). |
| 2026-09-09 | Certificados indexados: exámenes médico, psicofísico y psicosensométrico (salen de Historia Laboral; HV queda en 23 tipos). |
| 2026-09-09 | Historia Laboral actualizada a 27 tipos (cursos Supervigilancia + EPS/AFP/cesantías; estos últimos se comparten con Afiliaciones). |
| 2026-09-09 | Cursos y capacitación indexados (catálogo Super 25 + otro). HV queda en 26. EPS/AFP/cesantías de HV (empleado) ya no se cruzan con Afiliaciones (empresa). |
| 2026-09-09 | Contratación indexada: 9 tipos obligatorios (contrato, ética, inducción, carné, carta). Demo 0/9. No se cruza con HV. |
| 2026-09-09 | Tope de carga 50 MB. LAN de demo: Wi‑Fi `sjsp.net` `http://172.16.23.47:8086/ingreso`. |
| 2026-09-09 | Otros indexados: tipo libre, máx. 20 por trabajador. Bloquea nombres que cruzan con las otras carpetas. |
| 2026-09-09 | Un solo PDF para indexar (`/lote`): carpeta + tipo por corte. `document_batches.folder` nullable. Las 6 tarjetas de carga quedan en una. |
