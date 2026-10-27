# Operación desatendida (VPS CloudPanel + servidor/PC local)

Estos units de `systemd` hacen que la app funcione sola, sin que nadie tenga que
revisar nada a diario: arrancan solos al bootear el servidor y se reinician
solos si el proceso se cae. Son los mismos dos archivos para CloudPanel y para
el entorno local — solo cambian las rutas.

## 1. Worker de colas (`itassets-queue.service`)

Sin esto, las notificaciones de garantía/licencia/mantenimiento y el envío de
documentos de asignación **nunca se procesan** (quedan encoladas para
siempre), porque todas usan `ShouldQueue`.

```bash
cp itassets-queue.service /etc/systemd/system/
# editar WorkingDirectory= y User= dentro del archivo copiado
systemctl daemon-reload
systemctl enable --now itassets-queue.service
```

`Restart=always` + `--max-time=3600` hacen que el worker se reinicie solo cada
hora (evita fugas de memoria en procesos PHP de larga duración) y ante
cualquier caída.

## 2. Scheduler (`itassets-scheduler.service` + `.timer`)

Reemplaza al crontab clásico de Laravel (`* * * * * php artisan schedule:run`).
Dispara cada minuto lo que esté en `routes/console.php`:
`notifications:check` (08:00), `backup:run` (03:00) → `backup:clean`, y
`backup:monitor` (03:30).

```bash
cp itassets-scheduler.service itassets-scheduler.timer /etc/systemd/system/
# editar WorkingDirectory= y User= en itassets-scheduler.service
systemctl daemon-reload
systemctl enable --now itassets-scheduler.timer
```

`Persistent=true` recupera el tick si la PC local estuvo apagada.

## 3. Backups automáticos (`spatie/laravel-backup`, ya instalado)

No requiere un paso de instalación aparte — corre solo todos los días a las
03:00 vía el scheduler de arriba. Queda en `storage/app/private/<APP_NAME>/`
(disco `local`, fuera del document root, no público) con rotación automática
(`config/backup.php` → `cleanup.default_strategy`: 7 días completos, luego
diarios 16 días, semanales 8 semanas, mensuales 4 meses).

Si falla el backup, o no hay un backup sano reciente, se envía un mail a
`BACKUP_NOTIFICATION_EMAIL` (o `ADMIN_EMAIL` si no se configuró) usando el
mismo mailer configurado en Sistema → Correo — sin que nadie tenga que mirar
un dashboard.

**Importante**: esto cubre backups *locales al servidor*. Si el disco del VPS
o de la PC local se rompe, se pierde el backup también. Para backup
*fuera del servidor* (recomendado), agregar un disco S3-compatible
(Hostinger/Backblaze/etc.) en `config/filesystems.php` y sumarlo a
`destination.disks` en `config/backup.php` — requiere credenciales que no
están configuradas todavía.

## 4. Logs

`LOG_STACK=daily` (ver `.env.example`) rota y poda logs solo cada
`LOG_DAILY_DAYS` días (14 por defecto) — no requiere logrotate del sistema.

## Checklist de instalación en un servidor nuevo

1. Clonar el repo y `cp .env.example .env`, completar credenciales reales.
2. `composer install --no-dev && npm run build && php artisan migrate --seed`
3. Copiar e instalar los 3 units de este directorio (editando rutas/usuario).
4. Verificar que corrieron: `systemctl status itassets-queue itassets-scheduler.timer`
5. Verificar un backup manual: `php artisan backup:run` y confirmar el zip en
   `storage/app/private/`.
