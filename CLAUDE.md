# CLAUDE.md — Motor de Procesamiento y Dashboard de Pedidos

> Este archivo es la memoria persistente del proyecto. Léelo completo antes de cualquier tarea.
> Al terminar cada fase, actualiza la sección "Estado actual" marcando el checkbox correspondiente
> y agrega cualquier decisión técnica relevante en "Decisiones tomadas".

---

## Contexto del proyecto

Panel interno de logística para e-commerce. Permite al equipo visualizar el estado de pedidos
y aplica cargos automáticos por envío exprés a pedidos prioritarios.

---

## Stack exacto — no cambiar nada de esto

| Capa | Tecnología |
|------|-----------|
| Framework | Laravel 12 instalado |
| Base de datos | MySQL 8.0 |
| Entorno | Docker via Laravel Sail |
| Auth | Laravel Socialite — GitHub OAuth 2.0 únicamente |
| Frontend bridge | Inertia.js |
| Frontend UI | React 19 |
| Estilos | Tailwind CSS + shadcn/ui |
| Cache / Queue | Redis |
| Scheduler | Laravel Task Scheduling nativo |
| Control de versiones | GitHub (repositorio ya creado) |

---

## Esquema de base de datos — 4 tablas

```sql
-- clientes
id, nombre, email (unique), telefono, timestamps

-- productos
id, nombre, sku (unique), precio (decimal 10,2), timestamps

-- pedidos
id, cliente_id (FK -> clientes), fecha_entrega (date),
total (decimal 10,2), estado (enum: pendiente|entregado|cancelado), timestamps

-- pedido_producto  [tabla pivote]
pedido_id (FK -> pedidos), producto_id (FK -> productos),
cantidad (int), precio_unitario (decimal 10,2)
```

**Nota:** el producto con `id = 5` es el artículo de "Manejo Especial" usado por el Command.

---

## Reglas de código — obligatorias en cada tarea

### Eloquent / Base de datos
- **Nunca lazy loading.** Siempre `with(['relacion'])` en queries del dashboard.
- **Paginación real de BD:** `->paginate(15)`. Nunca `->get()` seguido de slice en memoria.
- **Local Scopes** en el modelo `Pedido` para cada estado. No en controladores.
- **Actualizaciones masivas:** usar `->update(['total' => DB::raw('total * 1.10')])`. Nunca loops con `->save()`.
- **Filtrar por relaciones** con `whereHas()`. No joins manuales innecesarios.
- **Seeders masivos:** usar `insert()` en chunks, no `create()` en loops.

### PHP
- Tipos estrictos: `declare(strict_types=1)` en cada archivo.
- Docblocks en todos los métodos públicos.
- Retornar tipos declarados en firmas de métodos.

### Frontend
- Componentes React funcionales con hooks.
- No mezclar lógica de negocio en componentes de vista.
- Props tipadas con PropTypes o TypeScript si se adopta.

### General
- Un archivo por tarea cuando sea posible (facilita revisión).
- Incluir siempre el path completo del archivo al generar código.
- Si se reemplaza código existente, indicar qué líneas eliminar.

---

## Local Scopes requeridos en modelo Pedido

```php
// Estado 'pendiente' con fecha_entrega en los próximos 3 días (incluyendo hoy)
public function scopePorEnviar(Builder $query): Builder

// Estado 'pendiente' con fecha_entrega anterior a hoy
public function scopeRetrasados(Builder $query): Builder

// Estado 'entregado'
public function scopeEntregados(Builder $query): Builder

// Estado 'cancelado'
public function scopeCancelados(Builder $query): Builder
```

---

## Regla de negocio del Command

Archivo: `app/Console/Commands/AplicarCargoExpres.php`
Signature: `pedidos:cargo-expres`

Condiciones del filtro (las 3 deben cumplirse simultáneamente):
1. `estado = 'pendiente'`
2. `DATE(fecha_entrega) = DATE(NOW() + 1 día)` — solo comparar fecha, ignorar hora
3. Existe registro en `pedido_producto` con `producto_id = 5`

Acción: `total = total * 1.10` en un solo UPDATE masivo (no loop).
Registrar en log cuántos pedidos fueron actualizados.
Programado en Scheduler para ejecutarse diariamente a medianoche.

---

## Estado actual de fases

```
[x] Fase 0 — Scaffolding & Repositorio
      - Proyecto Laravel con Sail
      - docker-compose.yml (app, mysql, redis)
      - .env.example completo
      - Repositorio GitHub inicializado
      - CLAUDE.md creado

[x] Fase 1 — Base de datos
      - Migraciones (4 tablas)
      - Modelos con relaciones y Local Scopes
      - Factories (Cliente, Producto, Pedido)
      - Seeders (20 productos, 1000+ pedidos, pivote)

[x] Fase 2 — Autenticación OAuth
      - Socialite instalado
      - SocialiteController (redirect + callback)
      - Rutas protegidas con middleware auth
      - firstOrCreate en callback
      - Vista welcome con botón GitHub
      - Tabla users actualizada (github_id, avatar) ← estás aquí

[x] Fase 3 — Dashboard de Logística
      - Inertia.js + React configurado
      - DashboardController con 4 queries (scopes + eager loading)
      - Componente OrdersTable con paginación
      - Tags de productos en columna
      - 4 secciones: Por Enviar / Retrasados / Entregados / Cancelados


[x] Fase 4 — Motor de Cargos (Artisan Command)
      - AplicarCargoExpres command
      - Filtro con whereHas (sin traer datos a memoria)
      - UPDATE masivo con DB::raw
      - Registro en Scheduler (medianoche)
      - Logging del resultado

[x] Fase 5 — Entrega
      - AI_JOURNEY.md documentado
      - README.md con instrucciones de setup
      - VERIFICACION_DOCKER_COMPOSE.md con checklist
      - Verificación de docker compose limpio
```

---

## Decisiones técnicas tomadas

_Agrega aquí las decisiones importantes que surjan durante el desarrollo.
Formato sugerido: fecha — decisión — motivo._

- Inicio — Se elige Inertia.js como bridge en lugar de API REST pura, para mantener
  el routing de Laravel y evitar duplicar lógica de autenticación en el frontend.
- Inicio — shadcn/ui sobre otros component libraries por compatibilidad nativa con
  Tailwind y ausencia de dependencias pesadas.
- Inicio — Redis para sesiones (`SESSION_DRIVER=redis`) para preparar escalabilidad
  futura con queues.
- Fase 0 — Laravel 12.x instalado (compatible con requerimientos del proyecto).
- Fase 0 — MySQL fijado en 8.0, PHP en 8.3 para estabilidad.
- Fase 0 — Tailwind 4 ya incluido via Vite desde el scaffold inicial.
- Fase 1 — Seeders con bulk insert (no create() en loops) para evitar N+1. Explícit
  IDs en pedidos para mapeo inmediato con pivote.
- Fase 2 — Sem Breeze/Jetstream: Socialite puro + SocialiteController manual. No password ni remember_token en users.
   GitHub es el único provider OAuth obligatorio.
- Fase 3 — Implementación de pestañas manual: Se implementó un sistema de pestañas manual en `Dashboard.jsx` utilizando componentes de React simples (`TabNavigation.jsx`) debido a un error de instalación de `shadcn/ui` `Tabs` relacionado con WSL2/virtualización.
- Fase 4 — Comando AplicarCargoExpres: Implementado con whereHas() en lugar de con()->get() para evitar traer datos innecesarios.
   El UPDATE es un único query masivo con DB::raw(), sin loops. Scheduler configurado para ejecutarse diariamente
   a las 00:00 usando ->dailyAt('00:00'). Logging de pedidos actualizados con Log::info().
- Fase 5 — Documentación Final: Creado AI_JOURNEY.md como narrativa de decisiones técnicas y correcciones realizadas.
   README.md completamente reescrito con instrucciones paso a paso para Windows (usando docker compose directamente, 
   no ./vendor/bin/sail). Se documentaron 7 correcciones clave: N+1 queries, seeders en loops, Local Scopes, 
   whereHas() vs joins, paginación real, componentes sin lógica de negocio, UPDATE masivo vs loops.

---

## Variables de entorno requeridas (.env)

```env
APP_NAME="Orders Dashboard"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=orders_dashboard
DB_USERNAME=sail
DB_PASSWORD=password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=http://localhost/auth/github/callback
```

---

## Comandos útiles de referencia

```bash
# Levantar entorno
./vendor/bin/sail up -d

# Migraciones + seeders
./vendor/bin/sail artisan migrate:fresh --seed

# Frontend dev server
./vendor/bin/sail npm run dev

# Ejecutar el Command manualmente (para probar)
./vendor/bin/sail artisan pedidos:cargo-expres

# Instalar dependencias PHP
./vendor/bin/sail composer require laravel/socialite inertiajs/inertia-laravel

# Instalar dependencias JS
./vendor/bin/sail npm install @inertiajs/react react react-dom
```

---

## Documentación de Referencia

| Archivo | Propósito |
|---------|-----------|
| **README.md** | Setup e instrucciones de uso del proyecto |
| **AI_JOURNEY.md** | Decisiones técnicas y correcciones realizadas como Tech Lead |
| **VERIFICACION_DOCKER_COMPOSE.md** | Checklist para levantar docker compose |
| **ENTREGA_FINAL.md** | Resumen final de la Fase 5 |
| **CLAUDE.md** | Este archivo — memoria técnica del proyecto |

---

## Cómo actualizar este archivo

Al terminar cada fase, pídele al agente:

```
Actualiza el CLAUDE.md: marca la Fase [N] como completada con [x]
y agrega en "Decisiones técnicas tomadas" cualquier decisión importante de esta fase.
```

