# Control de Gastos (versión web)

Aplicación web para llevar tus finanzas personales: cuentas, movimientos (ingresos/gastos), deudas, proyectos freelance y reportes con gráficas. Hecha con **Laravel + Inertia + Vue 3 + Tailwind**, la misma familia de herramientas que tus otros proyectos.

Pensada para que la uses tú (o quien invites) desde el navegador, en la compu o el celular.

## Qué incluye

- **Inicio**: saldo total, ingresos/gastos del mes, deuda pendiente, por cobrar de proyectos, tus cuentas y últimos movimientos.
- **Cuentas**: crea las que quieras (efectivo, bancos, tarjetas, ahorro...), cada una con su saldo calculado solo.
- **Movimientos**: ingresos y gastos por categoría, con filtros, más transferencias entre tus propias cuentas.
- **Deudas**: registra lo que debes y ve abonando; el saldo de la cuenta desde donde pagas se descuenta automático.
- **Proyectos**: para tu trabajo freelance — cuánto cobras, cliente, y vas registrando los pagos que te hacen hasta completarlo.
- **Reportes**: gastos por categoría, ingresos vs. gastos por mes, evolución de tu saldo, rentabilidad de proyectos, resumen de deudas.

Cada usuario que se registre tiene sus propios datos, completamente separados.

## Desarrollo local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run dev        # en otra terminal
php artisan serve
```

Entra a `http://localhost:8000`, crea una cuenta desde "Regístrate" y ya puedes usar la app. Cada usuario nuevo recibe automáticamente el set de categorías por defecto.

---

## Cómo subirla gratis a internet (Render + Supabase)

Usamos dos servicios gratuitos y separados a propósito:

- **Render.com** aloja la aplicación (gratis, pero el Postgres gratis de Render se borra a los 30 días).
- **Supabase** aloja la base de datos Postgres (gratis y sin fecha de expiración — solo se "pausa" si no la usas en 7 días, y la reactivas con un clic sin perder nada).

Los pasos que llevan creación de cuentas los tienes que hacer tú mismo (no puedo crear cuentas en tu nombre). Aquí va la guía completa:

### 1. Sube el proyecto a GitHub

```bash
git init
git add -A
git commit -m "Control de Gastos"
```

Crea un repositorio nuevo (privado si quieres) en [github.com/new](https://github.com/new) y sigue las instrucciones que te da GitHub para conectarlo y hacer `git push`.

### 2. Crea la base de datos en Supabase

1. Ve a [supabase.com](https://supabase.com) y crea una cuenta gratis.
2. "New project" → ponle un nombre (ej. `control-gastos`) → elige una contraseña fuerte para la base de datos (guárdala) → elige la región más cercana a ti.
3. Cuando esté listo, ve a **Project Settings → Database → Connection string** y copia la que dice **"Transaction pooler"** (puerto 6543) — se ve algo así:
   ```
   postgresql://postgres.xxxxxxxxxxxx:[TU-PASSWORD]@aws-0-xxxxx.pooler.supabase.com:6543/postgres
   ```
   Reemplaza `[TU-PASSWORD]` por la contraseña que pusiste. Guarda esta URL completa, la vas a necesitar.

### 3. Crea el servicio en Render

1. Ve a [render.com](https://render.com) y crea una cuenta gratis (puedes entrar con tu cuenta de GitHub, así queda conectado de una vez).
2. **New +** → **Blueprint** → selecciona el repositorio que subiste a GitHub. Render va a leer el archivo `render.yaml` que ya viene en el proyecto y va a preguntar por las variables marcadas como `sync: false`:
   - `APP_KEY`: genera una localmente con `php artisan key:generate --show` y pégala aquí (algo como `base64:...`).
   - `APP_URL`: la vas a saber hasta que Render te dé la URL (ej. `https://control-gastos.onrender.com`) — puedes dejarla en blanco al inicio y actualizarla después en **Environment**.
   - `DB_URL`: pega la connection string de Supabase del paso 2.
3. Dale a **Apply** / **Deploy**. La primera vez tarda varios minutos porque compila todo dentro de Docker.
4. Cuando termine, abre la URL que te dio Render, entra a `/register` y crea tu cuenta.

### Limitaciones del plan gratis (para que no te agarren en curva)

- El servicio de Render **se "duerme" tras 15 minutos sin visitas** — la primera vez que entres después de eso, tarda entre 30 y 50 segundos en despertar. Las siguientes visitas son rápidas.
- Supabase **pausa el proyecto tras 7 días sin actividad** — tus datos NO se borran, solo tienes que entrar a tu dashboard de Supabase y darle "Restore/Resume" (un clic) para reactivarlo.
- Si quieres que la recuperación de contraseña funcione de verdad (enviar correos), tienes que configurar un SMTP real en las variables de entorno (`MAIL_MAILER`, `MAIL_HOST`, etc. — por ejemplo con Gmail, como hiciste en SIGAM).

### Actualizar la app después de cambios

Cada vez que hagas `git push` a la rama que Render está vigilando, se despliega solo automáticamente.

## Estructura del proyecto

```
app/
├── Models/              # Cuenta, Categoria, Movimiento, Transferencia, Deuda, AbonoDeuda, Proyecto, PagoProyecto
├── Http/Controllers/     # un controlador por sección
resources/js/
├── Layouts/AuthenticatedLayout.vue   # barra lateral + navegación
├── Pages/                # Dashboard, Cuentas, Movimientos, Deudas, Proyectos, Reportes
└── Components/            # tarjetas, gráficas (Chart.js), modales, etc.
```
