# 📦 Orders Dashboard — Motor de Procesamiento de Pedidos

> Panel interno de logística para e-commerce. Visualiza el estado de pedidos y aplica cargos automáticos por envío exprés a pedidos prioritarios.

**Stack:** Laravel 12 • Inertia.js • React 19 • MySQL 8.0 • Docker • GitHub OAuth  
**Versión:** 1.0  
**Estado:** Producción ✅

---

## 🎯 ¿Qué hace este proyecto?

- **Dashboard de logística** en tiempo real con 4 secciones: Por Enviar, Retrasados, Entregados, Cancelados
- **Visualización de pedidos** con información de cliente, productos, fecha de entrega y total
- **Motor de cargos automático** que aplica 10% de recargo a pedidos con "Manejo Especial" que se entregan mañana
- **Autenticación segura** vía GitHub OAuth 2.0
- **Base de datos optimizada** con 1000+ pedidos de prueba

---

## 📋 Requisitos Previos

Antes de empezar, verifica que tienes instalado:

- **Docker Desktop** (https://www.docker.com/products/docker-desktop) — v29+
- **Composer** (https://getcomposer.org/download/) — v2.7+
- **Node.js** (https://nodejs.org/) — v18+

Verifica las versiones:

```powershell
docker --version
composer --version
node --version
npm --version
```

---

## 🚀 Setup Rápido

### **1. Clonar el repositorio**

```powershell
git clone https://github.com/tu-usuario/prueba-pedidos.git
cd prueba-pedidos
```

### **2. Instalar dependencias**

```powershell
# Dependencias PHP
composer install

# Dependencias Node.js
npm install
```

### **3. Configurar variables de entorno**

```powershell
# Copiar archivo de ejemplo
copy .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### **4. Configurar GitHub OAuth (opcional, pero recomendado)**

Si quieres que el login funcione, crea una OAuth App en GitHub:

1. Ve a https://github.com/settings/developers
2. Click en **New OAuth App**
3. Llena el formulario:
   ```
   Application name:        Orders Dashboard Local
   Homepage URL:            http://localhost
   Authorization callback:  http://localhost/auth/github/callback
   ```
4. Copia el **Client ID** y genera un **Client Secret**
5. Actualiza el archivo `.env`:
   ```env
   GITHUB_CLIENT_ID=tu_client_id
   GITHUB_CLIENT_SECRET=tu_client_secret
   ```

Si omites este paso, puedes seguir viendo el dashboard, pero el login no funcionará.

### **5. Levantar los contenedores Docker**

```powershell
# En Windows, usa docker compose directamente (no ./vendor/bin/sail)
docker compose up -d

# Verifica que los 3 contenedores principales están corriendo
docker compose ps

# Esperado: laravel.test, mysql:8.0, redis:alpine todos en "Up"
```

**Primera vez:** Esto toma 3-5 minutos mientras descarga las imágenes.

### **6. Ejecutar migraciones y seeders**

```powershell
# Crea las tablas e inserta datos de prueba
docker compose exec laravel.test php artisan migrate:fresh --seed

# Esperado: "Database reset successfully" y "Seeded successfully"
```

Esto crea:
- 4 tablas: clientes, productos, pedidos, pedido_producto
- 20 productos de prueba
- 1000+ pedidos con diferentes estados

### **7. Iniciar el servidor de desarrollo (React)**

En una **NUEVA terminal PowerShell**:

```powershell
cd prueba-pedidos
docker compose exec laravel.test npm run dev

# Verás algo como:
# VITE v5.x.x  ready in 234 ms
# ➜  Local:   http://localhost:5173/
```

**Mantén esta terminal abierta.** Es el dev server de Vite para hot reload.

### **8. Abrir en el navegador**

```
http://localhost
```

Verás:
- Página de welcome con botón "Login with GitHub" (si configuraste OAuth)
- O el dashboard directamente si saltaste OAuth

---

## 📍 URLs Principales

| Servicio | URL / Puerto |
|----------|-------------|
| **Laravel App** | http://localhost |
| **Vite Dev Server** | http://localhost:5173 (automático) |
| **MySQL** | localhost:3306 (user: sail, pass: password) |
| **Redis** | localhost:6379 |
| **Mailpit UI** | http://localhost:8025 (emails capturados) |

---

## 🛠️ Comandos Útiles

### **Gestionar contenedores**

```powershell
# Levantar contenedores
docker compose up -d

# Parar contenedores (mantiene datos)
docker compose down

# Ver logs en vivo
docker compose logs -f laravel.test

# Reconstruir contenedores
docker compose build
```

### **Artisan (Laravel)**

```powershell
# Ejecutar migraciones
docker compose exec laravel.test php artisan migrate

# Revertir a estado inicial
docker compose exec laravel.test php artisan migrate:fresh --seed

# Ejecutar el Command de cargos exprés
docker compose exec laravel.test php artisan pedidos:cargo-expres

# Ver comandos disponibles
docker compose exec laravel.test php artisan list
```

### **Acceso directo**

```powershell
# Bash en el contenedor app
docker compose exec laravel.test bash

# MySQL CLI
docker compose exec mysql mysql -uroot -p  # Contraseña: password
```

---

## 📊 Estructura de Bases de Datos

### **clientes**
```sql
id, nombre, email (unique), telefono, created_at, updated_at
```

### **productos**
```sql
id, nombre, sku (unique), precio (decimal), created_at, updated_at
```

### **pedidos**
```sql
id, cliente_id (FK), fecha_entrega (date), total (decimal),
estado (enum: pendiente|entregado|cancelado), created_at, updated_at
```

### **pedido_producto** (pivote)
```sql
pedido_id (FK), producto_id (FK), cantidad (int), precio_unitario (decimal)
```

**Nota especial:** El producto con `id = 5` es "Manejo Especial" y se usa en el comando de cargos.

---

## 🤖 Motor de Cargos Automático

Existe un **Artisan Command** que aplica 10% de recargo a pedidos específicos:

```powershell
docker compose exec laravel.test php artisan pedidos:cargo-expres
```

**Filtros:** Aplica recargo cuando se cumplen TODAS estas condiciones:
1. Estado = "pendiente"
2. Fecha de entrega = mañana (comparación solo de fecha)
3. Contiene producto con ID = 5 ("Manejo Especial")

**Acción:** `total = total * 1.10` (10% de recargo)

**Programación:** Se ejecuta automáticamente diariamente a las 00:00 (medianoche).

Puedes verlo en: `app/Console/Commands/AplicarCargoExpres.php`

---

## 🔐 Autenticación

El proyecto usa **GitHub OAuth 2.0** como único método de autenticación.

### Flujo:
1. Click en "Login with GitHub"
2. Redirige a GitHub para autorizar
3. Vuelve con token de autenticación
4. Se crea usuario automáticamente (firstOrCreate)
5. Acceso al dashboard

### Variables requeridas:
```env
GITHUB_CLIENT_ID=tu_id
GITHUB_CLIENT_SECRET=tu_secret
GITHUB_REDIRECT_URI=http://localhost/auth/github/callback
```

Si no configuras esto, verás un error al intentar loguearte.

---

## 🧪 Testing y Verificación

### Verificar que todo funciona:

```powershell
# 1. Contenedores corriendo
docker compose ps

# 2. Base de datos migrada
docker compose exec laravel.test php artisan migrate:status

# 3. Frontend construyendo
# (Deberías ver "ready in XXX ms" en terminal 2)

# 4. Acceso a la app
# Abre http://localhost en navegador
```

### Ejecutar tests (opcional):

```powershell
docker compose exec laravel.test php artisan test
```

---

## 🐛 Troubleshooting

### **Error: "port 80 already in use"**
```powershell
# Otra aplicación usa puerto 80
# Opción 1: Parar la otra app
# Opción 2: Ver qué usa el puerto
netstat -ano | findstr :80
```

### **Error: "docker: command not found"**
Docker no está instalado o no está en PATH. Reinstala Docker Desktop.

### **Vite dev server no inicia**
```powershell
# Verifica logs
docker compose logs laravel.test

# Reinicia
docker compose down
docker compose up -d
docker compose exec laravel.test npm run dev
```

### **Base de datos vacía**
```powershell
# Re-ejecuta migraciones + seeders
docker compose exec laravel.test php artisan migrate:fresh --seed
```

### **GitHub OAuth no funciona**
1. Verifica que `GITHUB_CLIENT_ID` y `GITHUB_CLIENT_SECRET` están en `.env`
2. Verifica que `GITHUB_REDIRECT_URI` es exacto: `http://localhost/auth/github/callback`
3. Verifica logs: `docker compose logs laravel.test`

---

## 📁 Estructura del Proyecto

```
prueba-pedidos/
├── app/
│   ├── Console/Commands/
│   │   └── AplicarCargoExpres.php    # Motor de cargos
│   ├── Http/Controllers/
│   │   ├── DashboardController.php   # Dashboard principal
│   │   └── SocialiteController.php   # OAuth GitHub
│   └── Models/
│       ├── Cliente.php
│       ├── Pedido.php               # Con Local Scopes
│       └── Producto.php
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   └── Dashboard.jsx        # Página principal
│   │   └── Components/
│   │       ├── OrdersTable.jsx      # Tabla de pedidos
│   │       └── TabNavigation.jsx    # Sistema de pestañas
│   └── css/
│       └── app.css                  # Tailwind
├── database/
│   ├── migrations/                  # 4 tablas
│   ├── factories/                   # Factories para seeders
│   └── seeders/                     # Datos de prueba
├── routes/
│   ├── web.php                      # Rutas públicas
│   └── api.php                      # Rutas API
├── docker-compose.yaml              # Configuración Docker
├── .env.example                     # Variables de entorno
├── composer.json                    # Dependencias PHP
├── package.json                     # Dependencias Node
├── CLAUDE.md                        # Memoria técnica del proyecto
├── AI_JOURNEY.md                    # Experiencia como Tech Lead
└── README.md                        # Este archivo
```

---

## 📚 Tecnologías Usadas

| Capa | Tecnología |
|------|-----------|
| **Backend Framework** | Laravel 12 |
| **Base de Datos** | MySQL 8.0 |
| **ORM** | Eloquent |
| **Frontend Bridge** | Inertia.js |
| **Frontend** | React 19 |
| **Estilos** | Tailwind CSS 4 |
| **Autenticación** | Laravel Socialite (GitHub OAuth) |
| **Caché/Sesiones** | Redis |
| **Contenedorización** | Docker + Laravel Sail |
| **Build Tool** | Vite |
| **Control de Versiones** | Git + GitHub |

---

## 🎓 Decisiones de Arquitectura

Para entender las decisiones técnicas tomadas, consulta:
- **CLAUDE.md** — Memoria técnica del proyecto, reglas de código, schemas
- **AI_JOURNEY.md** — Experiencia como Tech Lead, correcciones realizadas, aprendizajes

---

## 📝 Notas para Windows

1. **Usa `docker compose` directamente**, no `./vendor/bin/sail`
   - `./vendor/bin/sail` busca bash (Unix), en Windows falla
   - Ejemplo: `docker compose up -d` en lugar de `./vendor/bin/sail up -d`

2. **Hot reload funciona automáticamente**
   - Edita archivos React, guarda, el navegador se recarga solo
   - Edita código PHP, guarda, Laravel lo detecta automáticamente

3. **Permisos:** No necesitas `sudo` en Windows

4. **Rutas en Docker:**
   - Dentro del contenedor: `/var/www/html`
   - En tu máquina: `F:\apps_node\prueba-pedidos`
   - Docker mapea automáticamente

---

## 🚀 Siguiente Pasos (Post-Producción)

Si quieres extender el proyecto:

- [ ] Agregar roles y permisos (Admin, Logística, etc.)
- [ ] Implementar API REST para apps móviles
- [ ] Agregar gráficos de analítica (Chart.js, Recharts)
- [ ] Notificaciones por correo para cambios de estado
- [ ] Exportar reportes a PDF/Excel
- [ ] Sistema de comentarios internos en pedidos

---

## 📞 Soporte

Para reportar bugs o preguntas:
- Abre un issue en GitHub
- Revisa `CLAUDE.md` para entender la arquitectura
- Revisa `AI_JOURNEY.md` para correcciones técnicas previas

---

## 📄 Licencia

MIT License — Libre para usar, modificar y distribuir.

---

**Última actualización:** 17 de Mayo de 2026  
**Versión:** 1.0  
**Mantenedor:** Tech Lead (IA)

