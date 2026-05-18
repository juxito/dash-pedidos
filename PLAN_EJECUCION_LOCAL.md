# PLAN DE EJECUCIÓN LOCAL — Windows 11

> Documento creado para ejecutar el proyecto localmente en máquina Windows 11.
> Última actualización: 17 Mayo 2026

---

## 📋 PREREQUISITOS VALIDADOS

- ✓ Node.js v18+ instalado
- ✓ Código del proyecto clonado localmente en `F:\apps_node\prueba-pedidos`
- ⚠ Docker Desktop: **NO instalado** (incluido en este plan)
- ⚠ GitHub OAuth: **NO configurado** (incluido en este plan)

---

## 🎯 OBJETIVO

Ejecutar el dashboard de pedidos en `http://localhost` con:
- Laravel 12 + Inertia.js + React 19
- MySQL 8.0 en Docker
- Redis para sesiones en Docker
- GitHub OAuth para autenticación

---

## 📋 FASE 0: PREPARACIÓN (INSTALACIONES)

### 1.1 Instalar Docker Desktop

**Ubicación:** https://www.docker.com/products/docker-desktop

**Pasos:**
1. Descargar **Docker Desktop for Windows**
2. Ejecutar instalador
3. Dejar configuración por defecto (WSL 2 como backend)
4. **Importante:** Reiniciar máquina después de instalación
5. Verificar en PowerShell:
   ```powershell
   docker --version
   ```

### 1.2 Instalar Composer (si no está)

**Ubicación:** https://getcomposer.org/download/

**Pasos:**
1. Descargar e instalar
2. Verificar en PowerShell:
   ```powershell
   composer --version
   ```

### 1.3 Verificar Node.js

```powershell
node --version
npm --version
```

Debe estar en v18+

---

## 📋 FASE 1: PREPARAR EL PROYECTO

### 2.1 Navegar al directorio del proyecto

```powershell
cd F:\apps_node\prueba-pedidos
```

### 2.2 Instalar dependencias PHP

```powershell
composer install
```

**Tiempo:** ~2-3 minutos (primera vez)

### 2.3 Instalar dependencias Node.js

```powershell
npm install
```

**Tiempo:** ~1-2 minutos

### 2.4 Copiar archivo .env

```powershell
copy .env.example .env
```

### 2.5 Generar APP_KEY

```powershell
./vendor/bin/sail artisan key:generate
```

**Resultado esperado:** Clave de 32 caracteres generada en `.env` bajo `APP_KEY=`

---

## 📋 FASE 2: CONFIGURAR OAUTH DE GITHUB

### 3.1 Crear App OAuth en GitHub

**Ubicación:** https://github.com/settings/developers

**Pasos:**
1. Click en **New OAuth App**
2. Llenar formulario:
   ```
   Application name:        Orders Dashboard Local
   Homepage URL:            http://localhost
   Authorization callback:  http://localhost/auth/github/callback
   ```
3. Click **Register application**
4. Copiar:
   - **Client ID**
   - **Client Secret** (click "Generate a new client secret")

### 3.2 Actualizar .env

Abrir `F:\apps_node\prueba-pedidos\.env` con editor de texto y reemplazar:

```env
GITHUB_CLIENT_ID=<paste-tu-client-id>
GITHUB_CLIENT_SECRET=<paste-tu-client-secret>
GITHUB_REDIRECT_URI=http://localhost/auth/github/callback
```

---

## 📋 FASE 3: LEVANTAR CONTENEDORES

### 4.1 Iniciar Docker (primera vez toma 3-5 min)

En PowerShell, en el directorio del proyecto:

```powershell
./vendor/bin/sail up -d
```

**Qué hace:**
- Descarga imágenes de Docker (Laravel, MySQL 8.0, Redis)
- Levanta 3 contenedores: app, mysql, redis
- Abre puerto 80 (Laravel) y 3306 (MySQL)

### 4.2 Ejecutar migraciones + seeders

```powershell
./vendor/bin/sail artisan migrate:fresh --seed
```

**Qué crea:**
- 4 tablas: clientes, productos, pedidos, pedido_producto
- 20 productos
- 1000+ pedidos con datos aleatorios
- Datos pivote completos

**Tiempo:** ~10-15 segundos

### 4.3 Verificar contenedores

```powershell
./vendor/bin/sail ps
```

**Resultado esperado:**
```
CONTAINER ID   IMAGE           STATUS
xxxxxx         app             Up 2 minutes
xxxxxx         mysql:8.0       Up 2 minutes
xxxxxx         redis:latest    Up 2 minutes
```

---

## 📋 FASE 4: EJECUTAR FRONTEND

### 5.1 Abrir nueva terminal PowerShell

Mantener la primera terminal corriendo (con los contenedores).

En la nueva terminal:

```powershell
cd F:\apps_node\prueba-pedidos
./vendor/bin/sail npm run dev
```

**Qué hace:**
- Compila React + Tailwind en modo development
- Abre puerto 5173 (Vite dev server)
- Hot reload habilitado

**Verás algo como:**
```
VITE v5.x.x  ready in 234 ms

➜  Local:   http://localhost:5173/
➜  press h to show help
```

---

## 📋 FASE 5: ACCEDER A LA APLICACIÓN

### 6.1 Abrir navegador

```
http://localhost
```

**Qué verás:**
- Página de welcome con botón **"Login with GitHub"**
- Click en botón → redirige a GitHub OAuth
- Autorizar acceso
- Redirige de vuelta al dashboard

### 6.2 Verificar funcionalidad

Una vez logueado, deberías ver:

✓ Dashboard con 4 secciones de pestañas:
  - **Por Enviar** (pendientes, entrega próx. 3 días)
  - **Retrasados** (pendientes con entrega vencida)
  - **Entregados** (estado = entregado)
  - **Cancelados** (estado = cancelado)

✓ Tabla con columnas:
  - ID, Cliente, Productos (tags), Fecha entrega, Total, Acciones

✓ Paginación funcionando (15 registros por página)

✓ Tags de productos con colores

---

## 📋 FASE 6: PROBAR EL COMMAND (OPCIONAL)

### 7.1 Ejecutar Command AplicarCargoExpres

En cualquier terminal (con Sail running):

```powershell
./vendor/bin/sail artisan pedidos:cargo-expres
```

**Qué hace:**
- Busca pedidos con:
  - Estado = pendiente
  - Fecha entrega = mañana
  - Producto ID = 5 (Manejo Especial)
- Aplica 10% de cargo al total
- Registra en log cuántos se actualizaron

**Resultado esperado:**
```
[info] Se aplicó cargo exprés a X pedidos con entrega mañana y manejo especial
```

---

## 📋 FASE 7: DETENER CUANDO TERMINES

### 8.1 Parar contenedores

```powershell
./vendor/bin/sail down
```

**Qué sucede:**
- Detiene app, mysql, redis
- Mantiene datos en volúmenes (persistentes)
- Libera puertos 80 y 3306

### 8.2 Próxima vez que levantes

```powershell
./vendor/bin/sail up -d
```

Los datos estarán intactos (no necesitas `migrate:fresh` nuevamente).

---

## 🔧 TROUBLESHOOTING

### Error: "docker: command not found"
- Docker Desktop no está corriendo o no instalado correctamente
- Verificar que la instalación finalizó
- Reiniciar máquina

### Error: "port 80 already in use"
- Otro servicio ocupa puerto 80
- Verificar: `netstat -ano | findstr :80`
- O cambiar puerto en `docker-compose.yml` (no recomendado para desarrollo)

### Error: "Connection refused" en http://localhost
- Contenedores no levantaron correctamente
- Verificar: `./vendor/bin/sail ps`
- Revisar logs: `./vendor/bin/sail logs app`

### GitHub OAuth no funciona
- Verificar GITHUB_CLIENT_ID y GITHUB_CLIENT_SECRET en .env
- Verificar que GITHUB_REDIRECT_URI sea exacto: `http://localhost/auth/github/callback`
- Revisar logs: `./vendor/bin/sail logs app`

### Base de datos vacía
- Ejecutar nuevamente: `./vendor/bin/sail artisan migrate:fresh --seed`

---

## 📌 RESUMEN DE COMANDOS

| Acción | Comando |
|--------|---------|
| Levantar contenedores | `./vendor/bin/sail up -d` |
| Detener contenedores | `./vendor/bin/sail down` |
| Migraciones + seeders | `./vendor/bin/sail artisan migrate:fresh --seed` |
| Dev server (React) | `./vendor/bin/sail npm run dev` |
| Ver logs | `./vendor/bin/sail logs app` |
| Acceder a bash en app | `./vendor/bin/sail bash` |
| Acceder a MySQL CLI | `./vendor/bin/sail mysql` |
| Ejecutar Command | `./vendor/bin/sail artisan pedidos:cargo-expres` |

---

## 📍 PUERTOS Y URLs

| Servicio | Puerto | URL |
|----------|--------|-----|
| Laravel | 80 | http://localhost |
| Vite Dev | 5173 | http://localhost:5173 |
| MySQL | 3306 | localhost:3306 |
| Redis | 6379 | localhost:6379 |

---

## ✅ CHECKLIST FINAL

Antes de considerar listo el proyecto:

- [ ] Docker Desktop instalado y running
- [ ] Composer instalado
- [ ] Node.js v18+ verificado
- [ ] `composer install` completado
- [ ] `npm install` completado
- [ ] `.env` actualizado con GITHUB_CLIENT_ID y SECRET
- [ ] `./vendor/bin/sail up -d` completado
- [ ] `./vendor/bin/sail artisan migrate:fresh --seed` completado
- [ ] `./vendor/bin/sail npm run dev` ejecutando
- [ ] http://localhost accesible y login con GitHub funcionando
- [ ] Dashboard mostrando 4 secciones con datos

---

**Guardado:** 17 Mayo 2026
**Estado:** Listo para ejecutar
**Fase:** Pre-Ejecución
