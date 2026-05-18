# AI_JOURNEY.md — Experiencia como Tech Lead

> Un documento sobre cómo guié a Claude (IA) para construir este dashboard de logística,
> qué decisiones tomamos en el camino, y qué correcciones técnicas fueron necesarias.

---

## 📖 El Viaje

Cuando empezamos este proyecto, el objetivo era claro: **un dashboard interno para visualizar el estado de pedidos
en e-commerce, con cargos automáticos por envío exprés**. Pero el verdadero reto fue definir un stack seguro,
escalable, y sobretodo, que cumpliera reglas estrictas de código para evitar problemas comunes como N+1 queries
y lógica de negocio esparcida en controladores.

Actué como **Tech Lead**, no solo pidiendo features, sino dirigiendo cómo implementarlas. Aquí está el viaje.

---

## 🎯 Los Prompts Clave que Funcionaron

### **Fase 0: Scaffolding & Repositorio**

**El prompt de apertura** fue crítico. No solo pedí "crea un proyecto Laravel", sino que establecí las reglas desde el inicio:

> "Necesito un dashboard de logística. Laravel 12 con Inertia.js + React. La pieza más importante es que **NUNCA habrá lazy loading** en las queries de base de datos. Siempre eager loading con `with()`. Además, quiero **Local Scopes en los modelos**, no lógica en controladores. Las actualizaciones masivas con `->update()` usando `DB::raw()`, **nunca loops**. Los seeders también en bulk. Escribe `declare(strict_types=1)` en cada archivo PHP y docblocks completos."

Esto funcionó porque desde el principio quedó claro: **no es un proyecto pequeño con deuda técnica permitida**. Es un proyecto donde cada regla existe por una razón.

También especifiqué:

> "Usa Docker con Sail. MySQL 8.0, Redis para sesiones y caché. GitHub OAuth únicamente para autenticación. No Breeze ni Jetstream. Quiero control manual de Socialite."

**Por qué funcionó:** Las restricciones explícitas previenen decisiones arquitectónicas que hubiera tenido que corregir después.

---

### **Fase 1: Base de Datos**

El prompt de BD fue muy específico:

> "Cuatro tablas: clientes, productos, pedidos, pedido_producto. Los pedidos tienen estado (enum), fecha_entrega (date). Quiero que crees factories que generen datos realistas: 1000+ pedidos con diferentes estados, 20 productos. Los seeders **usando `insert()` en chunks**, no `create()` en loops para que sea rápido. Y asegúrate de que el producto con `id = 5` es especial: es 'Manejo Especial' porque se usará en el comando de cargos."

Luego vino el prompt sobre **Local Scopes**:

> "En el modelo Pedido, quiero 4 scopes: `scopePorEnviar` (estado pendiente + entrega en próximos 3 días), `scopeRetrasados` (estado pendiente + entrega vencida), `scopeEntregados`, `scopeCancelados`. Estos scopes son **la única forma de filtrar estados**, nunca en el controlador."

**Corrección que hicimos:** La IA sugirió inicialmente queries con `get()` y luego filter en memoria. Le pedí refactorizar a scopes de Eloquent. Esto evitaba traer datos innecesarios a PHP.

---

### **Fase 2: Autenticación OAuth**

El prompt fue:

> "GitHub OAuth únicamente. Crea una SocialiteController manual. En el callback, usa `firstOrCreate` para usuarios. La tabla users debe tener `github_id` y `avatar`, **sin password ni remember_token** porque no hay login tradicional. Vista welcome simple con botón 'Login with GitHub'."

**Decisión clave aquí:** Rechacé usar Laravel Breeze o Jetstream porque agrega rutas, migraciones y lógica que no necesitábamos. Socialite puro es más limpio para este caso.

---

### **Fase 3: Dashboard de Logística**

Este fue el prompt más exigente:

> "Dashboard con 4 secciones en pestañas: Por Enviar, Retrasados, Entregados, Cancelados. Cada una muestra una tabla con paginación real (15 registros por página).
> Las queries en el controlador deben usar los scopes que creamos + **eager loading con `with()`**.
> Cada query es independiente. Nunca `->get()` seguido de `slice()` en memoria.
> Columnas: ID, Cliente, Productos (como tags), Fecha entrega, Total, Acciones.
> El componente React debe ser funcional con hooks. **Cero lógica de negocio en componentes de vista**."

**Corrección importante:** La IA inicialmente sugirió un solo query con condicionales y filters en memoria. Le dije: "No. Quiero 4 queries independientes, cada una con su scope, paginación separada. Es más limpio y escalable."

También especifiqué:

> "Los tags de productos van en la columna de Productos. Usa colores diferentes. No vendría mal agregar la firma de cada componente con PropTypes para tipar los props."

**El problema con shadcn/ui:** Hubo un intento de usar `shadcn/ui` Tabs que falló por problemas de WSL2. La solución fue elegante: **implementé manualmente un sistema de pestañas en React simple**. Menos dependencias, más control.

---

### **Fase 4: Motor de Cargos**

El prompt del command fue muy preciso:

> "Crea un Artisan Command: `pedidos:cargo-expres`. Debe filtrar pedidos que cumplan **LAS TRES CONDICIONES SIMULTÁNEAMENTE**:
> 1. Estado = pendiente
> 2. Fecha de entrega = mañana (solo comparar fecha, sin hora)
> 3. Existe un registro en `pedido_producto` con `producto_id = 5`
>
> Cuando encuentre los pedidos, **un único UPDATE masivo**: `total = total * 1.10` usando `DB::raw()`.
> Registra en logs cuántos se actualizaron.
> Programa esto en el Scheduler de Laravel para ejecutarse diariamente a las 00:00."

**Corrección crucial:** La IA sugirió primero hacer un `with('productos')` y luego filtrar en un loop en PHP. Le dije:

> "No. Usa `whereHas('productos', fn ($q) => $q->where('producto_id', 5))` para que el filtro ocurra **en la base de datos**, no en PHP. Y el UPDATE debe ser un único query con `DB::raw()`, sin loops de `->save()`."

Esto evitó un N+1 classic y un UPDATE ineficiente.

---

## 🔧 Las Correcciones Técnicas que Hicimos

### **1. N+1 Queries en Dashboard — La Refactorización Más Importante**

**El problema:** La IA inicialmente sugirió algo como:

```php
$pedidos = Pedido::where('estado', 'pendiente')->get();
foreach ($pedidos as $pedido) {
    $pedido->cliente;  // N+1: una query por cada pedido
    $pedido->productos; // Otra N+1
}
```

**Mi corrección:** "No toques ese código. Vamos a usar eager loading desde el inicio."

La versión final:

```php
$pedidos = Pedido::porEnviar()
    ->with(['cliente', 'productos'])
    ->paginate(15);
```

**Por qué importa:** En un dashboard con 1000+ pedidos, la diferencia es brutal. Sin eager loading: ~1000 queries. Con `with()`: 3 queries totales.

---

### **2. Seeders en Loops vs Bulk Insert**

**Lo que sugirió la IA inicialmente:**

```php
foreach ($productos as $producto) {
    Producto::create($producto);  // 1 query por producto
}
```

**La refactorización:**

```php
Producto::insert($productos);  // 1 query para todos
```

O para datos más complejos con relaciones:

```php
collect($pedidos)->chunk(100)->each(function ($chunk) {
    Pedido::insert($chunk->toArray());
});
```

**Impacto:** 1000 pedidos: de 1000+ queries a solo 10 queries (con chunks).

---

### **3. Filtros en Controlador vs Local Scopes**

**El antipatrón que sugerió:**

```php
// En DashboardController
if ($request->filter === 'por-enviar') {
    $pedidos = Pedido::where('estado', 'pendiente')
        ->whereDate('fecha_entrega', '<=', now()->addDays(3))
        ->get();
}
```

**La forma correcta (Local Scopes):**

En el modelo Pedido:

```php
public function scopePorEnviar(Builder $query): Builder {
    return $query->where('estado', 'pendiente')
        ->whereDate('fecha_entrega', '<=', now()->addDays(3));
}
```

En el controlador:

```php
$pedidos = Pedido::porEnviar()->with(['cliente', 'productos'])->paginate(15);
```

**Por qué:** Los scopes **centralizan la lógica de negocio en el modelo**, no esparcida en controladores. Si cambias la regla "próximos 3 días", cambias en un solo lugar.

---

### **4. WHERE con Relaciones: whereHas() es la Clave**

**Lo incorrecto (join manual):**

```php
$pedidos = Pedido::join('pedido_producto', 'pedidos.id', '=', 'pedido_producto.pedido_id')
    ->where('pedido_producto.producto_id', 5)
    ->distinct()
    ->get();
```

**Lo correcto:**

```php
$pedidos = Pedido::whereHas('productos', function ($q) {
    $q->where('producto_id', 5);
})->get();
```

**Ventaja:** `whereHas()` es más legible, Eloquent lo optimiza automáticamente, y es menos propenso a errores de duplicados.

---

### **5. Paginación Real vs En Memoria**

**El error que evitamos:**

```php
$todos = Pedido::get();
$pedidos = $todos->slice(0, 15); // Falso pagination
```

**Lo correcto:**

```php
$pedidos = Pedido::paginate(15); // Real, desde la BD
```

Cuando tienes 1000+ registros, traer todos a memoria es catastrófico.

---

### **6. Componentes React sin Lógica de Negocio**

**El antipatrón que sugerí NO hacer:**

```jsx
// ❌ NO hacer esto
function OrdersTable({ orders }) {
    const filtered = orders.filter(o => o.status === 'pending');
    return <table>...</table>;
}
```

**Lo correcto:**

```jsx
// ✅ Hacer esto
function OrdersTable({ orders }) {
    // Solo mostrar. Los datos ya vienen filtrados del servidor.
    return <table>...</table>;
}
```

**Razón:** El componente es **una vista pura**. La lógica está en el controlador. Si la BD tiene 1000 registros y el componente filtra en memoria, ¿por qué los traes a memoria?

---

### **7. UPDATE Masivo en Command**

**Lo que NO quería:**

```php
$pedidos = Pedido::wherePendiente()->get();
foreach ($pedidos as $pedido) {
    $pedido->total *= 1.10;
    $pedido->save();  // 1 query por pedido
}
```

**Lo que implementamos:**

```php
$updated = Pedido::wherePendiente()
    ->whereHas('productos', fn ($q) => $q->where('producto_id', 5))
    ->whereDate('fecha_entrega', now()->addDay())
    ->update([
        'total' => DB::raw('total * 1.10')
    ]);

Log::info("Se aplicó cargo exprés a $updated pedidos");
```

**Una sola query.** Eficiencia total.

---

## 💡 Lecciones Aprendidas

### **1. Las Reglas Estrictas al Inicio Previenen Deuda Técnica**

Si hubiera permitido lazy loading "por ahora", tendría un proyecto lleno de N+1 queries. Al imponer reglas desde el CLAUDE.md, el código se escribió bien desde el principio.

### **2. Local Scopes son Subestimados**

Mucha gente los considera "azúcar sintáctico". Realidad: son **la forma elegante de encapsular lógica de negocio** en Eloquent. Hacen el código más mantenible y testeable.

### **3. Las Restricciones Fuerzan Soluciones Mejores**

Cuando dije "sin shadcn/ui Tabs debido a WSL2", se creó un sistema de pestañas manual **más limpio y liviano** que cualquier librería.

### **4. Eager Loading No es Negociable**

`with()` no es un "nice to have". Es fundamental en cualquier aplicación que escale. La diferencia entre 10 queries y 1000 queries es la diferencia entre un dashboard que funciona y uno que falla.

### **5. Bulk Operations > Loops**

Siempre. `insert()`, `update()`, `delete()` en bulk son **uno o dos órdenes de magnitud más rápidos** que loops con `save()`.

---

## 📊 Decisiones Arquitectónicas Clave

| Decisión | Por Qué | Beneficio |
|----------|--------|-----------|
| **Inertia.js en lugar de API REST** | Mantiene routing de Laravel, evita duplicar autenticación | Menos código, más seguro |
| **Local Scopes obligatorios** | Encapsulación de lógica en modelos | Controladores limpios, reutilizable |
| **Seeders en bulk** | Rendimiento con 1000+ registros | Setup rápido, ciencia pura |
| **whereHas() para relaciones** | Filtro en BD, no en PHP | Menos datos traídos, más rápido |
| **Socialite puro (sin Breeze)** | Menos dependencias, más control | Autenticación simple y clara |
| **Pestañas manuales (no shadcn/ui)** | Evitar problemas de WSL2 | Componentes más livianos |
| **UPDATE masivo con DB::raw()** | Una sola query vs N queries | Rendimiento para 1000+ pedidos |

---

## 🚀 Si Tuviera que Hacerlo Nuevamente

1. **Las reglas de CLAUDE.md funcionan.** Las mantendría exactamente igual.
2. **Documentaría más temprano.** Este documento debería existir desde la Fase 1.
3. **Los seeders en bulk desde el inicio.** Ahorra tiempo en development.
4. **Local Scopes antes de cualquier controlador.** Evita refactorización después.
5. **Eager loading siempre.** No existe caso donde lazy loading sea mejor en un dashboard.

---

## 🎓 Para el Próximo Proyecto Similar

Si alguien vuelve a hacer un dashboard así, que recuerde:

- **Reglas explícitas desde el inicio**
- **Local Scopes en modelos, lógica en modelos, vistas limpias en React**
- **Eager loading no es opcional**
- **Bulk operations siempre**
- **whereHas() para relaciones con filtros**
- **Paginación real desde el primer query**

La deuda técnica se contrae en decisiones pequeñas. Estas no fueron pequeñas.

---

**Escrito como Tech Lead después de completar 5 fases de desarrollo.**

Fecha: 17 de Mayo de 2026  
Versión: 1.0  
Estado: Producción

