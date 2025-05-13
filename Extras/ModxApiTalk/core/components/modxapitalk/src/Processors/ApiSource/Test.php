<?php

namespace ModxApiTalk\Processors\ApiSource;

use MODX\Revolution\Processors\Processor;
use GuzzleHttp\Client;
use Throwable;

class Test extends Processor
{
    public function process()
    {
        $url = $this->getProperty('url');
        $authType = $this->getProperty('auth_type');           // apikey | bearer
        $authValue = $this->getProperty('auth_header_value');  // token или значение ключа
        $authKey = $this->getProperty('auth_header_key');      // имя ключа (только для apikey)
        $authLocation = $this->getProperty('auth_header_type'); // header | query
        $paramsRaw = $this->getProperty('params');
  
        // Параметры запроса
        $params = json_decode($paramsRaw, true);
        if (!is_array($params)) {
            return $this->failure('Некорректный JSON параметров.');
        }

        try {
            $headers = [];
            $query = $params;
          
            // === Авторизация ===
            if ($authType === 'bearer') {
                $headers['Authorization'] = 'Bearer ' . $authValue;
            } elseif ($authType === 'apikey') {
                if ($authKey && $authValue) {
                    if ($authLocation === 'header') {
                        $headers[$authKey] = $authValue;
                    } else { // query
                        $query[$authKey] = $authValue;
                    }
                }
            }

            $client = new Client();

            $response = $client->get($url, [
                'headers' => $headers,
                'query' => $query,
            ]);

           
            $body = json_decode($response->getBody(), true);
            if (!is_array($body)) {
                return $this->failure('Ответ API не является JSON.');
            }

            // Попробуем вытащить вложенные массивы (если это структура вида { data: [...] })
            $possibleList = array_filter($body, 'is_array');
            if (count($possibleList) === 1) {
                $body = reset($possibleList);
            }
           /* $this->modx->log(1, 'Full URL: ' . $url . '?' . http_build_query($query));
            $this->modx->log(1, 'Request Headers: ' . json_encode($headers));
            $this->modx->log(1, 'Response: ' . $response->getBody());*/

         //   $this->modx->log(1, 'Response2: ' . print_r(array_slice($body, 0, 3),true));
            // Отдаём только 3 записи
            return $this->success('Ответ получен', [
              'results' => array_slice($body, 0, 3),
            ]);
        } catch (Throwable $e) {
            return $this->failure('Ошибка при выполнении запроса: ' . $e->getMessage());
        }
    }
}
