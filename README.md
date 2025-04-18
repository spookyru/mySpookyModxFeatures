# Joomla to MODX Migration Script

This repository contains a script to migrate content from a Joomla website to MODX. The script handles the creation of categories, materials (resources), and associated metadata like images, statistics, and paths. Below, you'll find detailed instructions and key nuances to ensure a smooth migration.

Данный скрипт позволяет перенести сайт с joomla на MODx Revo 3. А именно решить главную задачу - сохранения URL. Суть в чем: есть существующий сайт построенный в Joomla CMS, где каждый материал принадлежит категории. Меню сайта может содержать и блог категории или материалы категории. Поэтому нам нужно будет сделать экспорт в JSON  из БД joomla материалы и меню так, чтобы получить основные данные для будущего сайта на modx. 
Категория - используется компонент Collections
Количество просмотров материала - используется компонент SiteStatistics
Изображение аннотация - TV поле IntroImage

---

## Table of Contents

1. [Overview \ Обзор](#overview)
2. [Prerequisites \ Требования](#prerequisites)
3. [Exporting Data from Joomla \ Экспорт данных](#exporting-data-from-joomla)
4. [Running the Migration Script \ Запуск скрипта](#running-the-migration-script)
5. [Key Features of the Script \ Особенности](#key-features-of-the-script)
6. [Important Notes \ замечания](#important-notes)

---

## Overview

This script automates the migration process from Joomla to MODX. It:
- Creates collections for categories in Joomla.
- Migrates articles (materials) to MODX resources.
- Preserves image paths, aliases, and metadata.
- Includes user statistics and page hits information.

The script is designed to ensure that URLs and paths remain intact during the migration process.

Этот скрипт автоматизирует процесс миграции из Joomla в MODX. Он:
- Создает коллекции для категорий Joomla.
- Переносит статьи (материалы) в ресурсы MODX.
- Сохраняет пути к изображениям, алиасы и метаданные.
- Включает статистику пользователей и просмотров страниц.

Скрипт разработан так, чтобы гарантировать сохранение URL-адресов и путей при миграции.

---

## Prerequisites

Before running the script, ensure the following:
1. You have a working MODX installation.
2. The `sitestatistics`, `collections` package is installed and properly configured in MODX.
3. A JSON export of the Joomla database is available (see the next section for details).
4. TV field `imageIntro`
5. Template page `2`

Перед запуском скрипта убедитесь, что выполнены следующие шаги:
1. Установлена рабочая версия MODX.
2. Пакет `sitestatistics`, `collections` установлен и настроен в MODX.
3. Есть JSON-экспорт базы данных Joomla (см. следующий раздел).
4. Создано ТВ поле `imageIntro`
5. Существует Шаблон страницы с номером `2`

---

## Exporting Data from Joomla

To export data from Joomla, use the following SQL query to create a JSON dump of your Joomla content, menu, and category data. This query ensures that you include important metadata such as URLs, aliases, and paths:

Чтобы экспортировать данные из Joomla, выполните следующий SQL-запрос для получения JSON-дампа контента, меню и категорий Joomla. Этот запрос включает важные метаданные, такие как URL-адреса, алиасы и пути:

Обязательно укажите правильные названия таблиц

```sql
SELECT
    c.id AS content_id,
    c.title AS content_title,
    c.alias AS content_alias,
    c.introtext,
    c.fulltext,
    c.created,
    c.modified,
    c.hits,
    c.metadesc,
    c.metakey,
    c.images,
    c.catid AS category_id,

    cat.title AS category_title,
    cat.alias AS category_alias,

    m.path AS menu_path,
    m.alias AS menu_alias,
    m.title AS menu_title,
    m.link AS menu_link

FROM #__content AS c
LEFT JOIN #__categories AS cat ON c.catid = cat.id
LEFT JOIN #__menu AS m ON m.link LIKE CONCAT('%id=', c.catid) 
WHERE c.state = 1 AND m.published = 1
ORDER BY c.created DESC;
```

### Steps to Export Data:
1. Run the above SQL query in your Joomla database.
2. Export the result as a JSON file (e.g., `joomla_export.json`).
3. Place the exported JSON file in the directory with the migration script.

1. Выполните запрос SQL в базе данных Joomla.
2. Экспортируйте результат в файл формата JSON (например, `joomla_export.json`).
3. Поместите экспортированный JSON-файл в директорию со скриптом миграции.

ВАЖНО! в файле должен быть чистый json, без добавленных данных вначале от phpmyadmin 

```json
[{"content_id":"1","content_title":"Title Item","content_alias":"..."}]
```

---

## Running the Migration Script

1. Download this joomla_json.php into your MODX environment.
2. Place the exported JSON file (`joomla_export.json`) in the same directory as the script.
3. Open the migration script and update paths to your files:
4. Run the script:
5. The script will:
   - Create collections for each Joomla category.
   - Migrate articles and preserve their metadata (e.g., introtext, fulltext, images, etc.).
   - Generate user statistics and page views for MODX.

1. Скачайте файл joomla_json.php MODX.
2. Поместите экспортированный JSON-файл (`joomla_export.json`) в ту же директорию, что и скрипт.
3. Откройте скрипт и обновите пути к вашим файлам :
4. Запустите скрипт:
5. Скрипт выполнит следующие задачи:
   - Создаст коллекции для каждой категории Joomla.
   - Перенесет статьи и сохранит их метаданные (например, introtext, fulltext, изображения и т.д.).
   - Сгенерирует статистику пользователей и просмотров страниц для MODX.

---

## Key Features of the Script

1. **Image Processing:**
   - The script extracts and cleans image paths from Joomla's `images` field.
   - Parameters like `#joomlaImage://` and URL parameters (`?width=800&height=450`) are stripped using the following regular expression:
     ```php
     $imageIntro = preg_replace('/(\.(jpg|jpeg|png|gif|webp))([?#].*?)?(?=\s|$)/i', '$1', $imageIntro);
     ```
   - TV field `imageIntro` is populated with the cleaned image path.

   - Скрипт извлекает и очищает пути к изображениям из поля `images` Joomla.
    - Параметры, такие как `#joomlaImage://` и URL-параметры (`?width=800&height=450`), удаляются с помощью следующего регулярного выражения:
  ```php
  $imageIntro = preg_replace('/(\.(jpg|jpeg|png|gif|webp))([?#].*?)?(?=\s|$)/i', '$1', $imageIntro);
  ```
    - Поле TV `imageIntro` заполняется очищенным путем к изображению (должно быть заранее создано).

2. **Statistics Integration:**

   - User statistics and page hits are migrated into the `UserStatistics` and `PageStatistics` tables in MODX.
   - Data insertion uses mass transactions for efficiency:
     ```php
     $modx->exec("INSERT INTO {$modx->getTableName('UserStatistics')} (user_key, date, last_active) VALUES " . implode(',', array_map(function ($data) {
         return "('" . implode("','", $data) . "')";
     }, $onlineUsersData)));
     ```
  
  - Статистика пользователей и просмотров страниц переносится в таблицы `UserStatistics` и `PageStatistics` в MODX.
   - Вставка данных выполняется массово для повышения производительности:
     ```php
     $modx->exec("INSERT INTO {$modx->getTableName('UserStatistics')} (user_key, date, last_active) VALUES " . implode(',', array_map(function ($data) {
         return "('" . implode("','", $data) . "')";
     }, $onlineUsersData)));
     ```
     Обратите внимание, что joomla пишет общее количество просмотров, в то время как пакет SiteStatistics подразумевает еще и уникальных пользователей. И если мигрировать просто так (в файле предусмотрен такой вариант), то мы получим, что 1 человек просмотрел много раз какую-то  страницу. Поэтому заполним случайным количеством пользователей, чтобы симитировать примерную посещаемость до миграции. При этом количество просмотров остается таким, каким и было до миграции. То есть, если у вас материал был просмотрел 80 000раз, то скрипт создаст статистику, что примерно 100 человек в сумме просмотрели 80 000 раз. Еще раз, это не обязательно, можно расскомментировать вариант простого переноса просмотров.
3. **Preservation of URLs:**
   - The script uses Joomla's `alias` and `menu_path` fields to ensure that MODX resources maintain the same URLs.
   - Скрипт использует поля `alias` и `menu_path` Joomla, чтобы ресурсы MODX сохраняли те же URL-адреса.

4. **MODX-TV Fields:**
   - Key metadata such as image paths are stored in MODX TV fields (e.g., `imageIntro`).
   - Ключевые метаданные, такие как пути к изображениям, сохраняются в TV-поля MODX (например, `imageIntro`).

---

## Important Notes

1. **Joomla Data Integrity:**
   - Ensure that all necessary data (categories, articles, menus) are present and valid in Joomla before exporting.
   - Verify that URLs and paths are consistent.

2. **MODX Configuration:**
   - Ensure that the `sitestatistics` package is installed and configured in MODX for user statistics and page views.
   - Убедитесь, что пакеты `sitestatistics` и `collections` установлены и настроены в MODX для работы со статистикой пользователей и просмотров страниц.

3. **Performance Optimization:**
   - The script uses batch inserts for efficiency. Large datasets may still take time to process, depending on your system's performance.
   - Скрипт использует пакетные вставки для повышения скорости. Однако обработка больших наборов данных может занять время в зависимости от производительности вашей системы.

4. **Backup First:**
   - Always back up your Joomla and MODX databases before running the migration.
   - Всегда создавайте резервные копии баз данных Joomla и MODX перед миграцией.

---

## Example Output

Upon successful execution, the script will output messages like:
```
Коллекция 'Новости' создана с ID 12.
Материал 'Обновление функциональности' создан с ID 34.
TV-поле imageIntro заполнено для материала 'Обновление функциональности' значением 'images/news/update.jpg'.
Импорт завершён
Создано категорий: 5
Импортировано материалов: 50
Импортировано записей статистики: 500
Добавлено уникальных пользователей: 100
```
После успешного выполнения скрипт выведет сообщения, например:
```
Коллекция 'Новости' создана с ID 12.
Материал 'Обновление функциональности' создан с ID 34.
TV-поле imageIntro заполнено для материала 'Обновление функциональности' значением 'images/news/update.jpg'.
Импорт завершён
Создано категорий: 5
Импортировано материалов: 50
Импортировано записей статистики: 500
Добавлено уникальных пользователей: 100
```

---

## Support
Надеюсь, что у вас все получится и вы не испортите данные. Не забывайте про резервные копии. Я тестировал на чистой установке modx с этими пакетами. 

If you encounter any issues during migration, please feel free to open an issue in this repository or contact the maintainer.

Happy migrating!
Удачной миграции!