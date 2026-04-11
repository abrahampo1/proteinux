# Proteinux

> Del FASTA a la estructura 3D anotada en tres clics.

Proteinux es una interfaz web en español que democratiza el acceso a AlphaFold2
sobre el supercomputador **CESGA Finis Terrae III**. Abstrae la complejidad del
HPC (SLURM, CUDA, entornos, bases de datos masivas) y le devuelve al
investigador una estructura 3D interactiva, métricas de confianza, propiedades
biológicas y un análisis interpretativo generado por IA.

Construido para **IMPACTHON 2026** / Cátedra CAMELIA de Medicina Personalizada.

---

## Características

- Envío de trabajos AlphaFold2 con una secuencia FASTA desde el navegador: sin
  terminal, sin SLURM, sin CUDA.
- Polling en vivo del estado del job (`PENDING → RUNNING → COMPLETED`) con
  trazado del pipeline en tres etapas.
- **Visor 3D interactivo** con 3Dmol.js coloreado por pLDDT canónico. Incluye
  fallback a RCSB cuando el simulador devuelve PDBs con solo Cα, para que
  `cartoon` y `stick` sigan viéndose.
- Gráfica de pLDDT por residuo, heatmap PAE, donut de estructura secundaria.
- Propiedades biológicas (solubilidad, índice de inestabilidad, alertas de
  toxicidad y alergenicidad) y recursos HPC consumidos (horas GPU/CPU,
  eficiencia, tiempo de pared).
- **Análisis IA automático** al completar el job: el LLM interpreta los
  resultados en cuatro secciones — calidad de la predicción, biología,
  regiones a investigar y siguientes pasos.
- **Chat Q&A** sobre los datos del job, con el contexto inyectado en cada
  turno.
- **BYOK — Bring Your Own Key** para el LLM: soportamos Anthropic, OpenAI y
  Google Gemini. Las API keys se cifran con AES-256-GCM y viven únicamente en
  la sesión del navegador del usuario.
- UI completamente responsive, estética *cuaderno de laboratorio* (IBM Plex +
  paleta crema + líneas finas).
- Todo el texto de usuario está en español.

---

## Arquitectura en una línea

El frontend es un **thin wrapper** sobre la API HTTP del CESGA: no hay base de
datos de dominio. Todo (jobs, proteínas, outputs, accounting) se consulta en
vivo contra el simulador. SQLite se usa únicamente para las tablas internas de
Laravel (sesiones, caché, cola).

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
    │                                  AlphaFold2 ──► PDB · mmCIF · pLDDT · PAE
    │
    ├──► 3Dmol.js (CDN) para el visor
    │
    └──► AiProviderFactory ──► Anthropic / OpenAI / Gemini
                               (HTTPS con la API key de la sesión del usuario)
```

---

## Stack

| Capa          | Tecnología |
| ------------- | ---------- |
| Backend       | PHP 8.4, Laravel 13, Blade |
| Frontend      | Tailwind CSS v4, 3Dmol.js (CDN), Axios |
| Tests         | Pest 4 (21 tests, 65 assertions) |
| Lint          | Laravel Pint |
| Infra HPC     | CESGA Finis Terrae III · AlphaFold2 · GPU NVIDIA A100 |
| Tipografía    | IBM Plex Sans / Mono / Serif (Bunny Fonts) |
| Analíticas    | Microsoft Clarity |

---

## Puesta en marcha local

**Requisitos**: PHP 8.4+, Composer, Node 20+, SQLite.

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
- worker de cola
- `php artisan pail` para streaming de logs
- Vite en modo dev

Si haces cambios en Blade/CSS y no los ves en el navegador: probablemente falta
`npm run dev` o `npm run build`.

---

## Variables de entorno relevantes

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

Las **API keys** de los proveedores LLM no viven en `.env`. Cada usuario
introduce las suyas desde `/ajustes-ia` y quedan persistidas cifradas en su
propia sesión.

---

## Configurar IA (BYOK)

1. Arranca la app y ve a `/ajustes-ia`.
2. Introduce tu API key de Anthropic, OpenAI o Google Gemini (o las tres).
3. Al pulsar *guardar*, el proveedor al que hayas introducido una key queda
   activo automáticamente.
4. Envía un job desde `/jobs/submit`. Cuando llegue a `COMPLETED`, debajo de
   los resultados aparecerán el panel de análisis automático y el chat Q&A.

Las keys se cifran con `Crypt::encryptString` (AES-256-GCM, `APP_KEY` como
llave) y no se serializan al frontend en ningún momento — la pantalla de
ajustes solo muestra los últimos 4 caracteres de cada key guardada.

---

## Tests y lint

```bash
php artisan test --compact                          # suite completa
php artisan test --compact --filter=JobAiChat       # filtrar un test
vendor/bin/pint --dirty --format agent              # lint de archivos modificados
composer run test                                   # alias: clear config + pest
```

Los tests de IA usan `Http::fake()` para mockear las respuestas de cada
proveedor, así que no necesitas keys reales para correrlos.

---

## Estructura relevante

```
app/
  Exceptions/            AiNotConfiguredException · AiProviderException · CesgaApiException
  Http/
    Controllers/         HomeController · JobController · AiSettingsController · Api/*
    Requests/            JobSubmitRequest · UpdateAiSettingsRequest
  Services/
    CesgaApiService.php  cliente HTTP único contra CESGA
    Ai/
      AiProvider.php         interfaz común
      AnthropicProvider.php  /v1/messages
      OpenAiProvider.php     /v1/chat/completions
      GeminiProvider.php     generateContent
      AiProviderFactory.php  selecciona el proveedor activo
      JobContextBuilder.php  serializa outputs → texto compacto para el LLM
  Support/Ai/
    AiSettings.php       wrapper sobre session() con cifrado

resources/
  css/app.css            tokens de tema (ink-*, signal-*, plddt-*)
  views/
    layouts/app.blade.php     layout base (status strip · navbar · Clarity)
    jobs/show.blade.php       ~1000 líneas: visor + gráficas + paneles IA
    settings/ai.blade.php     configurar proveedores LLM
    components/*              alert, protein-card, loading-spinner, etc.

routes/web.php           14 rutas
config/services.php      bloques cesga y llm
```

---

## Rutas

```
GET  /                              home
GET  /proteins                      catálogo
GET  /proteins/{id}                 detalle proteína
GET  /jobs/submit                   formulario de envío
POST /jobs                          crear job (→ redirige a jobs.show)
GET  /jobs/{jobId}                  progreso · resultados · error

GET  /ajustes-ia                    configurar providers LLM
POST /ajustes-ia                    guardar ajustes
DELETE /ajustes-ia/{provider}       borrar una key

GET  /api/jobs/{jobId}/status       proxy JSON del estado
GET  /api/jobs/{jobId}/outputs      proxy JSON de outputs
GET  /api/jobs/{jobId}/accounting   proxy JSON de accounting
GET  /api/jobs/{jobId}/ai-analysis  análisis IA (one-shot)
POST /api/jobs/{jobId}/ai-chat      chat Q&A sobre el job
```

---

## Gotchas documentadas

- **PDBs truncados del simulador**: el mock del CESGA devuelve, para
  secuencias personalizadas, un PDB sintético solo con átomos Cα. 3Dmol no
  puede renderizar `cartoon` ni `stick` con eso. El visor detecta el caso y se
  baja el PDB canónico desde RCSB (`files.rcsb.org/download/{pdb_id}.pdb`)
  cuando hay un `pdb_id` en los metadatos.
- **IDs del DOM en `jobs/show.blade.php`**: el bloque JS inline referencia
  muchos IDs (`#viewer-container`, `#plddt-chart`, `#pae-heatmap`,
  `#ai-analysis-panel`, `#ai-chat-panel`...). No los cambies sin actualizar
  también el JS o se rompe todo.
- **Barra de estado superior** (`nodo · cesga.ft3`, pipeline version, GPU) son
  strings cosméticos, no métricas en vivo.
- **Home — panel "live" de métricas** (queue depth, GPU utilization, median
  runtime): también son valores hardcodeados para la demo.

---

## Créditos

- **Infraestructura**: [CESGA](https://www.cesga.es) — Centro de Supercomputación
  de Galicia (Finis Terrae III)
- **Respaldo académico**: Cátedra CAMELIA de Medicina Personalizada,
  [CiTIUS](https://citius.gal)
- **Contexto**: IMPACTHON 2026 · Xunta de Galicia
- **Modelo de predicción**: AlphaFold2 (DeepMind · EMBL-EBI)
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
