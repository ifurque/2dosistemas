IMPULSO - README (resumen rapido)

Que es este proyecto
- Es una aplicacion web hecha con Laravel.
- La idea general es gestionar emprendimientos (businesses), contenido, turnos, consultas y reseñas.

Tecnologias principales
- PHP + Laravel
- Base de datos SQLite (por defecto en este repo)
- Vite para assets frontend

Como levantarlo (local)
1) Ir a la carpeta del proyecto.
2) Instalar dependencias PHP (si hiciera falta): composer install
3) Instalar dependencias frontend (si hiciera falta): npm install
4) Configurar entorno:
   - Copiar .env.example a .env (si no existe)
   - Generar key: php artisan key:generate
5) Base de datos:
   - Este repo usa sqlite por defecto
   - Archivo: database/database.sqlite
   - Correr migraciones: php artisan migrate
6) Levantar servidor:
   - php artisan serve --host=127.0.0.1 --port=8000
7) Abrir en navegador:
   - http://127.0.0.1:8000

Datos y modelo:
- Usuarios
- Emprendimientos (businesses)
- Productos, posts, ingresos/egresos
- Turnos (appointments), consultas (inquiries), reviews

Estructura util
- app/Models: modelos Eloquent
- app/Http/Controllers: controladores
- database/migrations: estructura de tablas
- database/seeders: datos de prueba
- resources/views: vistas blade
- routes/web.php: rutas web

Notas
- Si aparece "conexion rechazada", revisar que php artisan serve siga corriendo.
- Si queres resetear la base y recrear todo: php artisan migrate:fresh
- Si queres resetear y seedear: php artisan migrate:fresh --seed

Fin.
