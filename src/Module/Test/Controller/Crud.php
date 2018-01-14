<?php

/**
 * Пример CRUD + различие между пустым значением свойства в объекте модели в оперативной памяти
 * и значением свойства объекта в Krugozor_Model_Track.
 */
class Krugozor_Module_Test_Controller_Crud extends Krugozor_Controller
{
    public function run()
    {
        $this->getResponse()->clearCookie();

        try {
            $object = $this->getMapper('User/User')->createModel();
            $data = array(
                'id' => '0',
                'active' => 1,
                'group' => '2',
                'login' => 'vasya',
                'email' => 'info@mirgorod.ru',
                'password' => 'xxx',
                'regdate' => new Krugozor_Type_Datetime('2013-01-01'),
                'visitdate' => '2014-08-24 18:04:12',
                'ip' => '',
                'first_name' => 'Василий',
                'last_name' => '',
                'age' => new Krugozor_Type_Datetime('1982-08-18'),
                'sex' => 'M',
                'city' => '1',
                'region' => '1',
                'country' => '1',
                'phone' => '123-45-67',
                'icq' => '10456',
                'url' => 'http://ya.ru',
                'skype' => 'vasiliyMakogon',
            );
            $object->setData($data);

            echo "Сохранение объекта:\n";
            echo "---------\n";

            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);
            $this->getMapper('User/User')->saveModel($object);
            echo "Query: " . Krugozor_Context::getInstance()->getDatabase()->getQueryString() . "\n";
            echo "Obj create: " . $object->getId() . PHP_EOL;
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);

            $this->getMapper('User/User')->saveModel($object);


            echo "\n\nsetLastName = '':\n";
            echo "---------\n";

            $object->setLastName('');
            $object->setEmail('info@wirgorod.ru');
            $object->setSex('F');
            $object->setAge(new Krugozor_Type_Datetime());
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);
            $this->getMapper('User/User')->saveModel($object);
            echo "Query: " . Krugozor_Context::getInstance()->getDatabase()->getQueryString() . "\n";
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);


            echo "\n\nsetLastName = '' only:\n";
            echo "---------\n";

            $object->setLastName('');
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);
            $this->getMapper('User/User')->saveModel($object);
            echo "Query: " . Krugozor_Context::getInstance()->getDatabase()->getQueryString() . "\n";
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);


            echo "\n\nfindModelById:\n";
            echo "---------\n";

            $object = $this->getMapper('User/User')->findModelById($object);
            echo "Query: " . Krugozor_Context::getInstance()->getDatabase()->getQueryString() . "\n";
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);


            echo "\n\nsetLastName = 'Иванов' only:\n";
            echo "---------\n";

            $object->setLastName('Иванов');
            $this->getMapper('User/User')->saveModel($object);
            echo "Query: " . Krugozor_Context::getInstance()->getDatabase()->getQueryString() . "\n";
            var_dump($object->getLastName());
            var_dump($object->getTrack()->last_name);

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $this->getResponse();
    }
}