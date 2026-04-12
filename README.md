# Proteinux

> **Del FASTA a la estructura 3D anotada en tres clics.**
> Una interfaz web en español sobre AlphaFold 2 en el supercomputador CESGA Finis Terrae III.

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-v4-38BDF8?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Pest](https://img.shields.io/badge/tests-Pest%204-8b5cf6)](https://pestphp.com)
[![License: PolyForm NC](https://img.shields.io/badge/license-PolyForm%20NC%201.0.0-0a66c2)](./LICENSE)

Proteinux abstrae la complejidad del HPC (SLURM, CUDA, entornos, bases de datos
masivas) y le devuelve al investigador una estructura 3D interactiva, métricas
de confianza, propiedades biológicas y un análisis interpretativo generado por
un LLM. Construido para **[IMPACTHON 2026](https://impacthon-web.vercel.app)** / Cátedra CAMELIA de Medicina Personalizada.

---

## Índice

1. [Qué hace Proteinux](#qué-hace-proteinux)
2. [Arquitectura](#arquitectura)
3. [Stack](#stack)
4. [Puesta en marcha local](#puesta-en-marcha-local)
5. [Variables de entorno](#variables-de-entorno)
6. [Configurar IA (BYOK)](#configurar-ia-byok)
7. [Flujo de uso](#flujo-de-uso)
8. [Tests y lint](#tests-y-lint)
9. [Rutas](#rutas)
10. [Estructura del proyecto](#estructura-del-proyecto)
11. [Gotchas conocidas](#gotchas-conocidas)
12. [Contribuir](#contribuir)
13. [Créditos](#créditos)
14. [Licencia](#licencia)

---

## Qué hace Proteinux

### Envío y seguimiento de jobs

- Envío de trabajos AlphaFold 2 con una secuencia FASTA desde el navegador:
  sin terminal, sin SLURM, sin CUDA.
- Polling en vivo del estado del job (`PENDING → RUNNING → COMPLETED`) con
  trazado del pipeline en tres etapas.
- **Biblioteca local (`/biblioteca`)** con deduplicación por hash SHA-256 de la
  secuencia: si ya predijiste una proteína, la reutilizas sin relanzar el job.
  Enriquecimiento perezoso contra la API del CESGA para rellenar nombre,
  organismo, UniProt/PDB ID y pLDDT medio.
- **Re-ejecución con un clic** desde la biblioteca para recalcular una
  predicción previa.

### Visualización de resultados

- **Visor 3D interactivo** con 3Dmol.js coloreado por pLDDT canónico. Incluye
  fallback a RCSB cuando el simulador devuelve PDBs con solo Cα, para que
  `cartoon` y `stick` sigan viéndose correctamente.
- Gráfica de pLDDT por residuo, heatmap PAE y donut de estructura secundaria.
- Propiedades biológicas (solubilidad, índice de inestabilidad, alertas de
  toxicidad y alergenicidad) y recursos HPC consumidos (horas GPU/CPU,
  eficiencia, tiempo de pared).

### Análisis asistido por IA

- **Análisis automático** al completar el job: el LLM interpreta los resultados
  en cuatro secciones — *calidad de la predicción · biología · regiones a
  investigar · siguientes pasos*.
- **Chat Q&A** sobre los datos del job, con el contexto inyectado en cada turno
  (sin alucinar sobre residuos que no existen).
- **BYOK — Bring Your Own Key**: Anthropic, OpenAI y Google Gemini. Las API
  keys se cifran con AES-256-GCM (`APP_KEY` como llave) y viven únicamente en
  la sesión del navegador del usuario. Nunca se serializan al frontend — la
  pantalla de ajustes solo muestra los últimos 4 caracteres.

### UX

- UI completamente responsive, estética *cuaderno de laboratorio* (IBM Plex +
  paleta crema + líneas finas).
- Todo el texto de usuario está en español, incluidos los prompts del LLM.

---

## Arquitectura

Proteinux es, fundamentalmente, un **thin wrapper** sobre la API HTTP del
CESGA: jobs, outputs, accounting y metadatos de proteína se consultan en vivo
contra el simulador. La única persistencia de dominio es una tabla ligera
(`predicted_jobs`) que actúa como caché/biblioteca con deduplicación por hash
de secuencia. Todo lo demás vive en sesión o en las tablas internas de Laravel
(caché, cola, sesiones).

```
navegador
    │
    ▼
Laravel 13 + Blade ──► CesgaApiService ──► CESGA API (FastAPI)
    │                                            │
    │                                            ▼
    │                                  SLURM ──► Finis Terrae III (A100 40 GB)
    │                                                        │
    │                                                        ▼
    │                                  AlphaFold 2 ──► PDB · mmCIF · pLDDT · PAE
    │
    ├──► SQLite (predicted_jobs)  ◄── JobLibrary (dedup por hash SHA-256)
    │
    ├──► 3Dmol.js (CDN) para el visor
    │
    └──► AiProviderFactory ──► Anthropic / OpenAI / Gemini
                               (HTTPS con la API key de la sesión del usuario)
```

---

## Stack

| Capa          | Tecnología                                                  |
| ------------- | ----------------------------------------------------------- |
| Backend       | PHP 8.3+, Laravel 13, Blade                                 |
| Frontend      | Tailwind CSS v4, 3Dmol.js (CDN), Axios                      |
| Persistencia  | SQLite (sesiones, cola, caché, `predicted_jobs`)            |
| Tests         | Pest 4 (feature + unit, mocks HTTP para IA y CESGA)         |
| Lint          | Laravel Pint                                                |
| Infra HPC     | CESGA Finis Terrae III · AlphaFold 2 · NVIDIA A100 40 GB    |
| Tipografía    | IBM Plex Sans / Mono / Serif (Bunny Fonts)                  |
| Analíticas    | Microsoft Clarity                                           |

---

## Puesta en marcha local

**Requisitos**: PHP 8.3+, Composer, Node 20+, SQLite.

```bash
git clone https://github.com/abrahampo1/proteinux.git
cd proteinux

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate

composer run dev
```

`composer run dev` levanta a la vez:

- `php artisan serve` en `http://localhost:8000`
- worker de cola (`queue:listen`)
- `php artisan pail` para streaming de logs
- Vite en modo dev

Si haces cambios en Blade/CSS y no los ves en el navegador: probablemente
falta `npm run dev` o `npm run build`.

> **Atajo**: `composer setup` hace todo lo de arriba (instalar deps, copiar
> `.env`, generar key, migrar y construir assets) en un solo comando.

---

## Variables de entorno

Solo dos bloques son específicos de Proteinux, el resto es `.env.example`
estándar de Laravel.

```dotenv
# API del simulador CESGA
CESGA_API_URL=https://api-mock-cesga.onrender.com
CESGA_API_TIMEOUT=45

# Modelos LLM por defecto (el usuario puede sobreescribirlos en /ajustes-ia)
ANTHROPIC_DEFAULT_MODEL=claude-sonnet-4-5
OPENAI_DEFAULT_MODEL=gpt-4o-mini
GEMINI_DEFAULT_MODEL=gemini-2.0-flash
LLM_TIMEOUT=60
```

Las **API keys** de los proveedores LLM *no* viven en `.env`. Cada usuario
introduce las suyas desde `/ajustes-ia` y se persisten cifradas en su propia
sesión.

---

## Configurar IA (BYOK)

1. Arranca la app y ve a `/ajustes-ia`.
2. Introduce tu API key de Anthropic, OpenAI o Google Gemini (una, dos o las
   tres).
3. Al pulsar *guardar*, el proveedor al que hayas introducido una key queda
   activo automáticamente. Si guardas varias, el último añadido prevalece
   salvo que elijas otro explícitamente.
4. Envía un job desde `/jobs/submit`. Cuando llegue a `COMPLETED`, debajo de
   los resultados aparecerán el **panel de análisis automático** y el **chat
   Q&A**.

Detalles de seguridad:

- Cifrado con `Crypt::encryptString` (AES-256-GCM, `APP_KEY` como llave).
- Las keys nunca se envían al frontend; la UI solo renderiza máscaras del tipo
  `•••••••••••••••abcd`.
- Borrar una key es un `DELETE /ajustes-ia/{provider}` — un solo clic en la UI.
- Las llamadas al LLM salen directamente desde el backend a Anthropic / OpenAI
  / Google por HTTPS. Proteinux no proxy-ea tokens ni guarda historial de
  conversación persistente.

---

## Flujo de uso

1. **Envía** una secuencia en `/jobs/submit` (pegas el FASTA o subes el archivo).
2. **Observa** el progreso en `/jobs/{jobId}`: estado SLURM en vivo, pipeline
   gráfico de tres etapas.
3. **Explora** la proteína al terminar:
   - visor 3D rotable con escala pLDDT,
   - pLDDT por residuo, heatmap PAE, estructura secundaria,
   - propiedades fisicoquímicas y alertas,
   - accounting de GPU/CPU.
4. **Interpreta** los resultados con el panel de IA (si hay BYOK configurado).
5. **Pregunta** lo que quieras en el chat Q&A — el contexto del job se inyecta
   automáticamente.
6. **Reutiliza** desde `/biblioteca`: busca por nombre, organismo o UniProt/PDB
   ID, o relanza una predicción con un clic.

---

## Tests y lint

```bash
php artisan test --compact                          # suite completa
php artisan test --compact --filter=JobAiChat       # filtrar un test
vendor/bin/pint --dirty --format agent              # lint de archivos modificados
composer run test                                   # alias: clear config + pest
```

La suite cubre los providers de IA, los flujos de ajustes, análisis y chat,
la biblioteca/deduplicación de jobs y el builder de contexto. Los tests de IA
usan `Http::fake()` para mockear las respuestas de cada proveedor, así que
**no necesitas keys reales** para correrlos.

---

## Rutas

```
GET    /                                 home
GET    /proteins                         catálogo
GET    /proteins/{proteinId}             detalle proteína

GET    /jobs/submit                      formulario de envío
POST   /jobs                             crear job (→ redirige a jobs.show)
GET    /jobs/{jobId}                     progreso · resultados · error

GET    /biblioteca                       biblioteca de predicciones locales
POST   /biblioteca/{predictedJob}/rerun  re-lanzar una predicción existente

GET    /ajustes-ia                       configurar providers LLM
POST   /ajustes-ia                       guardar ajustes
DELETE /ajustes-ia/{provider}            borrar una key

GET    /api/jobs/{jobId}/status          proxy JSON del estado SLURM
GET    /api/jobs/{jobId}/outputs         proxy JSON de outputs
GET    /api/jobs/{jobId}/accounting      proxy JSON de accounting
GET    /api/jobs/{jobId}/ai-analysis     análisis IA (one-shot)
POST   /api/jobs/{jobId}/ai-chat         chat Q&A sobre el job
```

Listado completo en [`routes/web.php`](routes/web.php).

---

## Estructura del proyecto

```
app/
  Exceptions/            AiNotConfiguredException · AiProviderException · CesgaApiException
  Http/
    Controllers/         HomeController · JobController · JobLibraryController
                         ProteinCatalogController · AiSettingsController · Api/*
    Requests/            JobSubmitRequest · UpdateAiSettingsRequest
  Models/
    PredictedJob.php     fila de la biblioteca local (dedup por sequence_hash)
    User.php             scaffolding Laravel, sin uso en la app
  Services/
    CesgaApiService.php      cliente HTTP único contra la API del CESGA
    JobLibrary.php           persistencia + enrichment de la biblioteca
    Ai/
      AiProvider.php         interfaz común
      AnthropicProvider.php  /v1/messages
      OpenAiProvider.php     /v1/chat/completions
      GeminiProvider.php     generateContent
      AiProviderFactory.php  selecciona el proveedor activo
      JobContextBuilder.php  serializa outputs → texto compacto para el LLM
  Support/Ai/
    AiSettings.php       wrapper sobre session() con cifrado AES-256-GCM

database/
  migrations/            tablas Laravel + predicted_jobs

resources/
  css/app.css            tokens de tema (ink-*, signal-*, plddt-*)
  views/
    layouts/app.blade.php     layout base (status strip · navbar · Clarity)
    home.blade.php            landing
    jobs/create.blade.php     formulario de envío
    jobs/show.blade.php       ~1000 líneas: visor + gráficas + paneles IA
    library/index.blade.php   biblioteca con búsqueda y rerun
    proteins/*                catálogo + detalle
    settings/ai.blade.php     configurar proveedores LLM
    components/*              alert, protein-card, loading-spinner, stat-card, etc.

routes/web.php           rutas web y API interna
config/services.php      bloques cesga y llm
tests/                   Pest 4 (Feature + Unit)
```

---

## Gotchas conocidas

- **PDBs truncados del simulador**: el mock del CESGA devuelve, para secuencias
  personalizadas, un PDB sintético solo con átomos Cα. 3Dmol no puede
  renderizar `cartoon` ni `stick` con eso. El visor detecta el caso y se baja
  el PDB canónico desde RCSB (`files.rcsb.org/download/{pdb_id}.pdb`) cuando
  hay un `pdb_id` en los metadatos.
- **IDs del DOM en `jobs/show.blade.php`**: el bloque JS inline referencia
  muchos IDs (`#viewer-container`, `#plddt-chart`, `#pae-heatmap`,
  `#ai-analysis-panel`, `#ai-chat-panel`...). No los cambies sin actualizar
  también el JS o se rompe todo.
- **Barra de estado superior** (`nodo · cesga.ft3`, pipeline version, GPU) son
  strings cosméticos, no métricas en vivo.
- **Panel "live" de la home** (queue depth, GPU utilization, median runtime):
  también son valores hardcodeados para la demo.
- **Biblioteca enriquecida en lote limitado**: el render de `/biblioteca` solo
  consulta CESGA para hasta 10 filas pendientes por página (ver
  `JobLibraryController::LAZY_ENRICH_BUDGET`) para no saturar la API.

---

## Contribuir

Contribuciones en forma de issues y PRs son bienvenidas para uso no comercial
(ver [licencia](#licencia)). Antes de abrir un PR:

1. Corre la suite: `composer run test`.
2. Pasa el linter: `vendor/bin/pint --dirty --format agent`.
3. Respeta el código existente — las guías específicas del proyecto están en
   [`CLAUDE.md`](CLAUDE.md) (convenciones Laravel, PHP, Pest y Tailwind).
4. Todo el texto mostrado al usuario debe estar en español.

---

## Créditos

- **Infraestructura**: [CESGA](https://www.cesga.es) — Centro de Supercomputación
  de Galicia (Finis Terrae III)
- **Respaldo académico**: Cátedra CAMELIA de Medicina Personalizada,
  [CiTIUS](https://citius.gal)
- **Contexto**: [IMPACTHON 2026](https://impacthon-web.vercel.app) · Xunta de Galicia
- **Modelo de predicción**: AlphaFold 2 (DeepMind · EMBL-EBI)
- **Visor 3D**: [3Dmol.js](https://3dmol.csb.pitt.edu/)
- **Framework**: [Laravel](https://laravel.com)

---

## Licencia

Proteinux se publica bajo la **PolyForm Noncommercial License 1.0.0**.

Puedes leerlo, auditarlo, estudiarlo, modificarlo y redistribuirlo para uso
personal, académico, docente, investigación y organizaciones sin ánimo de
lucro. **Cualquier uso comercial requiere una licencia de pago firmada por el
autor.**

Para licencias comerciales: **abraham@leiro.dev**

Ver [`LICENSE`](LICENSE) para los detalles completos.
