<?php

namespace Krugozor\Framework\Module\Test\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\User\Validator\UserIdExists;
use Krugozor\Framework\Module\User\Validator\UserPasswordsCompare;
use Krugozor\Framework\Validator as BaseValidator;

class Validator extends Controller
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
            $validator = new BaseValidator('common/general', 'advert/edit', 'user/common', 'user/registration');

            $object = $this->getMapper('Advert/Advert')->createModel();
            $data = array(
                'id' => '0',
                'id_user' => -12,
                'active' => '1',
                'type' => 'sale',
                'category' => '1',
                'header' => 'header info@server.su and url http://ya.ru http://www.yandex.ru',
                'hash' => '',
                'text' => 'text info@server.su and url http://ya.ru',
                'place_country' => 1,
                'place_region' => 1,
                'place_city' => 1,
//                'price_type' => 'RUB1',
            );
            // Валидация ошибок с помощью самой модели через set-методы.
            $object->setData($data);

            if ($err = $object->getValidateErrors()) {
                // Ошибки в "чистом" виде, возвращённые моделью.
                print_r($err);

                // Ошибки в "человекопонятном" виде, сгенерированные BaseValidator
                // на основании языковых файлов.
                $validator->addModelErrors($err);
                print_r($validator->getErrors());

                // Добавление ошибки (без валидации)
                $validator->addError('contact_info', 'EMPTY_CONTACT_INFO');
                print_r($validator->getErrors());

                // Добавление валидаторов
                $validator->add('id_user', new UserIdExists($object->getIdUser(), $this->getMapper('User/User')));
                $validator->add('two_string_compare', new UserPasswordsCompare('abc', 'abcd'));

                $validator->validate();
                print_r($validator->getErrors());
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $this->getResponse();
    }
}