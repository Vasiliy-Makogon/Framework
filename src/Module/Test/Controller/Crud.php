<?php

namespace Krugozor\Framework\Module\Test\Controller;

use Krugozor\Framework\Context;
use Krugozor\Framework\Controller;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Type\Datetime;

/**
 * Пример CRUD + различие между пустым значением свойства в объекте модели в оперативной памяти
 * и значением свойства объекта в Track.
 */
class Crud extends Controller
{
    public function run()
    {
        if (!$this->getCurrentUser()->isAdministrator()) {
            return $this->createNotification()
                ->setMessage('<p>Тестировать CRUD может только администратор</p>')
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/admin/')
                ->run();
        }

        $this->getResponse()->clearCookie()->clearHeaders();

        try {
            $object = $this->getMapper('User/User')->createModel();
            $data = array(
                'id' => '0',
                'active' => 1,
                'group' => '2',
                'login' => 'vasya',
                'email' => 'info@server.ru',
                'password' => 'xxxxx',
                'regdate' => new Datetime('2013-01-01'),
                'visitdate' => '2014-08-24 18:04:12',
                'ip' => '',
                'first_name' => 'Василий',
                'last_name' => '',
                'age' => new Datetime('1982-08-18'),
                'sex' => 'M',
                'city' => '1',
                'region' => '1',
                'country' => '1',
                'phone' => '123-45-67',
                'icq' => '10456',
                'url' => 'http://ya.ru',
                'skype' => 'my_skipe',
            );
            $object->setData($data);

            echo "Сохранение объекта:\n";
            echo "---------\n";

            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);
            $this->getMapper('User/User')->saveModel($object);
            echo "Query: " . Context::getInstance()->getDatabase()->getQueryString() . "\n";
            echo "Obj create: " . $object->getId() . PHP_EOL;
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name); // NULL  т.к. Mapper/CommonMapper.php:244

            $this->getMapper('User/User')->saveModel($object);


            echo "\n\nsetLastName = '':\n";
            echo "---------\n";

            $object->setLastName('');
            $object->setEmail('info@server.com');
            $object->setSex('F');
            $object->setAge(new Datetime());
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);
            $this->getMapper('User/User')->saveModel($object);
            echo "Query: " . Context::getInstance()->getDatabase()->getQueryString() . "\n";
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);


            echo "\n\nsetLastName = '' only:\n";
            echo "---------\n";

            $object->setLastName('');
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);
            $this->getMapper('User/User')->saveModel($object);
            echo "Query: " . Context::getInstance()->getDatabase()->getQueryString() . "\n";
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);

            echo "\n\nfindModelById:\n";
            echo "---------\n";

            $object = $this->getMapper('User/User')->findModelById($object);
            echo "Query: " . Context::getInstance()->getDatabase()->getQueryString() . "\n";
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);

            echo "\n\nsetLastName = 'Иванов' only:\n";
            echo "---------\n";

            $object->setLastName('Иванов');
            $this->getMapper('User/User')->saveModel($object);
            echo "Query: " . Context::getInstance()->getDatabase()->getQueryString() . "\n";
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $this->getResponse();
    }
}