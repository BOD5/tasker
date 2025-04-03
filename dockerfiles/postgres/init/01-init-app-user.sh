#!/bin/bash
set -e # Зупинити виконання при першій помилці

# Використовуємо змінні, явно передані через environment сервісу db
APP_USER="${DB_APP_USER}"
APP_PASSWORD="${DB_APP_PASSWORD}"
APP_DB="${POSTGRES_DB}" # Ця доступна за замовчуванням

echo "=== Запуск скрипта ініціалізації для користувача $APP_USER на базі $APP_DB ==="

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$APP_DB" <<-EOSQL
    -- Створюємо роль для додатка, ЯКЩО вона ще не існує
    DO \$\$
    BEGIN
       IF NOT EXISTS (
          SELECT FROM pg_catalog.pg_roles
          WHERE  rolname = '$APP_USER') THEN

          CREATE ROLE "$APP_USER" WITH LOGIN PASSWORD '$APP_PASSWORD';
          RAISE NOTICE 'Роль "$APP_USER" створено.';
       ELSE
          RAISE NOTICE 'Роль "$APP_USER" вже існує.';
       END IF;
    END
    \$\$;

    -- Надаємо права на підключення до бази даних
    GRANT CONNECT ON DATABASE "$APP_DB" TO "$APP_USER";

    -- Надаємо права на використання схеми public
    GRANT USAGE, CREATE ON SCHEMA public TO "$APP_USER";

    -- Надаємо базові права на роботу з таблицями та послідовностями (поточні та майбутні)
    ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO "$APP_USER";
    ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO "$APP_USER";
    -- Надаємо права і на вже існуючі (хоча їх не має бути при першому запуску)
    GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO "$APP_USER";
    GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO "$APP_USER";

EOSQL

echo "**** Права для користувача '$APP_USER' на базу даних '$APP_DB' надано успішно ****"