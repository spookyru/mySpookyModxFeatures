<?php
use MODX\Revolution\modResource;
use Collections\Model\CollectionContainer;
use SiteStatistics\Model\PageStatistics;
use SiteStatistics\Model\OnlineUser;

require_once 'config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web'); 
$modx->getService('error', 'error.modError');

// Укажите путь к вашему JSON файлу
$jsonFile = 'c.json';

if (!file_exists($jsonFile)) {
    die("JSON файл не найден: $jsonFile");
}

// Загружаем и декодируем JSON
$jsonContent = file_get_contents($jsonFile);
$data = json_decode($jsonContent, true);

if (!$data) {
    die("Неверный формат JSON файла.");
}

// Подключение пакета SiteStatistics
$modx->addPackage('sitestatistics', MODX_CORE_PATH . 'components/sitestatistics/model/');

// Создаем коллекции и материалы
$collections = [];
$materialsCount = 0;
$hitsCount = 0;
$stat;
$resII = '';
$uniqueUsersCount = 0;
foreach ($data as $item) {
    if (!isset($collections[$item['category_id']])) {
      // $collection = $modx->newObject(modResource::class);
        $collection = $modx->newObject(CollectionContainer::class);
        $collection->fromArray([
            'pagetitle' => $item['category_title'],
            'alias' => $item['category_alias'],
            'template' => 2, //id шаблона
            'published' => 1,
            'isfolder' => 1, // Указываем, что это контейнер
            'class_key' => CollectionContainer::class, // Указываем, что это коллекция
        ]);
        
        if ($collection->save()) {
            $collections[$item['category_id']] = $collection->get('id');
            echo "Коллекция '{$item['category_title']}' создана с ID {$collection->get('id')} <br>";
        } else {
            echo "Ошибка при создании коллекции '{$item['category_title']}' <br>";
            continue;
        }
    }

    // Создаем материал
    $resource = $modx->newObject(modResource::class);
    $resource->fromArray([
        'pagetitle' => $item['content_title'],
        'alias' => $item['content_alias'],
        'template' => 2, // Укажите ID шаблона для материалов
        'parent' => $collections[$item['category_id']], // Привязываем к коллекции
        'content' => $item['introtext'] . $item['fulltext'], // Конкатенируем introtext и fulltext
        'published' => 1,
        'createdon' => strtotime($item['created']),
        'editedon' => strtotime($item['modified']),
        'hidemenu' => 0,
        'searchable' => 1,
        'introtext' => $item['introtext'], // Заполняем аннотацию
        'class_key' => 'modDocument', // Обычный документ
    ]);

    if ($resource->save()) {

        $materialsCount++;
       
         // Заполняем TV-поле для изображения аннотации (imageIntro)
    $modx->cacheManager->refresh();

    if ($resource->save()) {
      $materialsCount++;

      // Обработка изображения аннотации (TV-поле imageIntro)
      $images = json_decode($item['images'], true);
      $imageIntro = !empty($images['image_intro']) ? $images['image_intro'] : '';
      if (!empty($imageIntro)) {
        // Убираем параметры после расширения файла
        $imageIntro = preg_replace('/(\.(jpg|jpeg|png|gif|webp))([?#].*?)?(?=\s|$)/i', '$1', $imageIntro);
        $resII = "С картинкой аннотацией: " . $imageIntro;
        // Устанавливаем значение TV-поля
        $resource->setTVValue('imageIntro', $imageIntro);
      }

      //делаем статистику 
      /* первый вариант самый простой - мы просто записываем количество просмотров на одного пользователя, как было в joomla        
      $stat = $modx->newObject( 'PageStatistics');
      $stat->set('rid', $resource->get('id'));
      $stat->set('user_key', md5(uniqid($resource->get('id'), true))); // Уникальный user_key
      $stat->set('date', date('Y-m-d', strtotime($item['created']))); // Дата создания материала
      $stat->set('month', date('n', strtotime($item['created'])));
      $stat->set('year', date('Y', strtotime($item['created'])));
      $stat->set('views', intval($item['hits'])); // Общее количество просмотров
*/

      //вариант 2 - мы делаем так, чтобы количество просмотров было равномерно распределено по дням, как в joomla
      $totalViews = intval($item['hits']);
      $created = strtotime($item['created']);
      $date = date('Y-m-d', $created);


      // Реалистичное количество уникальных пользователей (10-30% от общего числа просмотров) так как SiteStatistics поддерживает пользователей, а joomla не делила на пользователей и просмотры. И получается, что после импорта у нас 1 пользователей посмотрел 80 000+ раз ресурс, что неверно. Но и точно мы не можем сказать сколько было пользователей на просмотры. Поэтому используем среднее значение. Это нужно для отображения статистики в админке по уникальным пользователям в дальнейшем

        // Реалистичное количество уникальных пользователей (10-30% от общего числа просмотров)
        $uniqueUsers = max(ceil($totalViews * 0.1), rand(100, 500)); // Минимум 100 пользователей
        $viewsPerUser = ceil($totalViews / $uniqueUsers);
      
        // Подготовка данных для массовой вставки
      $onlineUsersData = [];
      $pageStatisticsData = [];

      for ($i = 0; $i < $uniqueUsers; $i++) {
        $userKey = md5(uniqid($resource->get('id') . $i, true));

        // Данные для spks_stat_online_users
        $onlineUsersData[] = [
          'user_key' => $userKey,
          'date' => date('Y-m-d H:i:s', strtotime("+$i seconds", $created)),
          'last_active' => date('Y-m-d H:i:s', strtotime("+$i seconds", $created)),
        ];

        // Данные для spks_stat_page_statistics
        $pageStatisticsData[] = [
          'rid' => $resource->get('id'),
          'user_key' => $userKey,
          'date' => $date,
          'month' => date('n', $created),
          'year' => date('Y', $created),
          'views' => rand(1, $viewsPerUser),
        ];
      }
      // Выполняем массовую вставку для spks_stat_online_users
      if (!empty($onlineUsersData)) {
        $modx->exec("INSERT INTO {$modx->getTableName('UserStatistics')} (user_key, date, last_active) VALUES " . implode(',', array_map(function ($data) {
          return "('" . implode("','", $data) . "')";
        }, $onlineUsersData)));
        $uniqueUsersCount += count($onlineUsersData);
      }

      // Выполняем массовую вставку для spks_stat_page_statistics
      if (!empty($pageStatisticsData)) {
        $modx->exec("INSERT INTO spks_stat_page_statistics (rid, user_key, date, month, year, views) VALUES " . implode(',', array_map(function ($data) {
          return "('" . implode("','", $data) . "')";
        }, $pageStatisticsData)));
        $hitsCount += array_sum(array_column($pageStatisticsData, 'views'));
      }
        
    }
    echo "Материал '{$item['content_title']}' создан с ID {$resource->get('id')} '{$resII}' <br>";
    } else {
        echo "Ошибка при создании материала '{$item['content_title']}' <br>";
    }
}

// Вывод итоговой информации
echo "<hr> Импорт завершён <br>";
echo "Создано категорий: " . count($collections) . "<br>";
echo "Импортировано материалов: $materialsCount  <br>";
echo "Импортировано записей статистики: $hitsCount <br>";
?>