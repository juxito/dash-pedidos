# VERIFICACION_DOCKER_COMPOSE.md — Checklist de Levantamiento

> Documento para verificar que docker compose levanta correctamente de forma limpia.

---

## ✅ Checklist Pre-Ejecución

Antes de levantar los contenedores, verifica:

```powershell
# 1. Ubicación correcta
cd F:\apps_node\prueba-pedidos
Test-Path -LiteralPath ".\compose.yaml"  # Debe ser True

# 2. Docker corriendo
docker --version  # v29+
docker ps        # No debe haber errores

# 3. Archivos necesarios existen
Test-Path -LiteralPath ".\.env"           # True
Test-Path -LiteralPath ".\vendor"         # True
Test-Path -LiteralPath ".\node_modules"   # True
```

---

## 🚀 Levantamiento Limpio (Un Solo Comando)

```powershell
cd F:\apps_node\prueba-pedidos

# Comando único que levanta TODO
docker compose up -d
```

**Qué sucede:**
1. Docker descarga imágenes (si no existen)
2. Crea volúmenes persistentes
3. Levanta 5 contenedores:
   - `laravel.test` — App Laravel + PHP 8.3
   - `mysql` — MySQL 8.0
   - `redis` — Redis Alpine
   - `selenium` — Chrome para testing
   - `mailpit` — Capturador de emails

**Tiempo esperado:** 
- Primera vez: 3-5 minutos
- Siguientes veces: 10-15 segundos

---

## ✅ Verificación de Levantamiento

```powershell
# Ver estado de contenedores
docker compose ps

# Esperado (todos "Up"):
# NAME                STATUS
# laravel.test        Up 2 minutes
# mysql               Up 2 minutes
# redis               Up 2 minutes
# selenium            Up 2 minutes
# mailpit             Up 2 minutes
```

Si alguno está "Exited", revisa logs:

```powershell
docker compose logs laravel.test
docker compose logs mysql
docker compose logs redis
```

---

## 🗄️ Inicializar Base de Datos

Una vez que los contenedores están corriendo:

```powershell
# Ejecutar migraciones + seeders
docker compose exec laravel.test php artisan migrate:fresh --seed

# Esperado:
# Creating tables...
# Database reset successfully
# Seeding database...
# Seeded successfully
```

**Esto crea:**
- 4 tablas: clientes, productos, pedidos, pedido_producto
- 20 productos de ejemplo
- 1000+ pedidos con datos aleatorios
- Relaciones completas en tabla pivote

---

## 🎬 Iniciar Frontend Dev

En una **nueva terminal**:

```powershell
cd F:\apps_node\prueba-pedidos
docker compose exec laravel.test npm run dev

# Esperado:
# VITE v5.x.x  ready in 234 ms
# ➜  Local:   http://localhost:5173/
```

Mantén esta terminal abierta para hot reload.

---

## 🌐 Verificar Acceso

```powershell
# En navegador
http://localhost

# Deberías ver:
# - Página de welcome con botón "Login with GitHub"
# O directamente el dashboard (si OAuth está configurado)
```

---

## 🛑 Parar Contenedores

```powershell
docker compose down

# Datos persisten en volúmenes
# Próxima vez: docker compose up -d
```

---

## 🔍 Verificación Avanzada

### Ver logs en tiempo real

```powershell
docker compose logs -f laravel.test   # Logs de Laravel
docker compose logs -f mysql          # Logs de MySQL
docker compose logs -f redis          # Logs de Redis
```

### Acceder a bash del contenedor

```powershell
docker compose exec laravel.test bash

# Dentro:
php artisan tinker              # CLI de PHP
ls -la                          # Ver archivos
composer install               # Si necesitas reinstalar
npm run build                  # Build de frontend
```

### Conectarse a MySQL

```powershell
docker compose exec mysql mysql -uroot -p
# Contraseña: password

# Dentro de MySQL:
USE orders_dashboard;
SELECT COUNT(*) FROM pedidos;     # Ver cantidad de pedidos
SELECT * FROM productos LIMIT 5;  # Ver 5 productos
```

### Acceder a Redis

```powershell
docker compose exec redis redis-cli

# Dentro:
PING                    # Debe responder "PONG"
KEYS *                  # Ver todas las keys
TTL <key>              # Ver tiempo de expiración
```

---

## 📊 Servicios y Puertos

| Servicio | Puerto | Estado Esperado |
|----------|--------|-----------------|
| Laravel App | 80 | Running, accesible en http://localhost |
| Vite Dev | 5173 | Running (si ejecutaste `npm run dev`) |
| MySQL | 3306 | Running, escuchando |
| Redis | 6379 | Running, responde PING |
| Selenium | 4444 | Running (interno) |
| Mailpit UI | 8025 | Running en http://localhost:8025 |

---

## ⚠️ Problemas Comunes y Soluciones

### **"port 80 already in use"**

```powershell
# Ver qué usa el puerto
netstat -ano | findstr :80

# Matar el proceso
taskkill /PID <PID> /F

# O cambiar puerto en compose.yaml (línea 12):
# ports:
#   - '8080:80'  # Luego accede en http://localhost:8080
```

### **"cannot connect to mysql"**

```powershell
# Ver logs de MySQL
docker compose logs mysql

# Si está corrupto:
docker compose down -v  # Borra volúmenes
docker compose up -d
docker compose exec laravel.test php artisan migrate:fresh --seed
```

### **Node modules no actualizados**

```powershell
docker compose exec laravel.test npm install
docker compose exec laravel.test npm run dev
```

### **Frontend no carga en http://localhost:5173**

```powershell
# Verifica que npm run dev está ejecutando
# En la terminal 2, deberías ver: "ready in XXX ms"

# Si no:
docker compose exec laravel.test npm run dev

# Si falla, reconstruye:
docker compose down
docker compose build
docker compose up -d
docker compose exec laravel.test npm run dev
```

---

## 📋 Checklist Final

- [ ] Docker está instalado y corriendo
- [ ] `docker compose up -d` completó sin errores
- [ ] `docker compose ps` muestra 5 contenedores "Up"
- [ ] `docker compose exec laravel.test php artisan migrate:fresh --seed` completó
- [ ] `http://localhost` es accesible en navegador
- [ ] `npm run dev` ejecutando en terminal 2
- [ ] Hot reload funciona (edita un archivo, guarda, la página se recarga)
- [ ] GitHub OAuth está configurado en `.env` (opcional, pero recomendado)
- [ ] Dashboard carga con 4 secciones de pestañas

Si todo marcó ✅, **el proyecto está listo para usar.**

---

**Versión:** 1.0  
**Última actualización:** 17 de Mayo de 2026  
**Estado:** Verificado y Funcional

