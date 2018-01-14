<?php
class Krugozor_Module_Test_Controller_Validator extends Krugozor_Controller
{
    public function run()
    {
        $this->getResponse()->clearCookie();

        try {
            $validator = new Krugozor_Validator('common/general', 'advert/edit', 'user/common', 'user/registration');

            $object = $this->getMapper('Advert/Advert')->createModel();
            $data = array(
                'id' => '0',
                'id_user' => -12,
                'active' => '1',
                'type' => 'sale',
                'category' => '1',
                'header' => 'header info@trevoga.su and url http://ya.ru http://www.yandex.ru',
                'hash' => '',
                'text' => 'text info@trevoga.su and url http://ya.ru',
                'place_country' => 1,
                'place_region' => 1,
                'place_city' => 1,
            );
            // Валидация ошибок с помощью самой модели через set-методы.
            $object->setData($data);

            if ($err = $object->getValidateErrors()) {
                // Ошибки в "чистом" виде, возвращённые моделью.
                print_r($err);

                // Ошибки в "человекопонятном" виде, сгенерированные Krugozor_Validator
                // на основании языковых файлов.
                $validator->addModelErrors($err);
                print_r($validator->getErrors());

                // Добавление ошибки (без валидации)
                $validator->addError('contact_info', 'EMPTY_CONTACT_INFO');
                print_r($validator->getErrors());

                // Добавление валидаторов
                $validator->add('id_user', new Krugozor_Module_User_Validator_UserIdExists(
                    $object->getIdUser(), $this->getMapper('User/User')
                ));
                $validator->add('two_string_compare', new Krugozor_Module_User_Validator_UserPasswordsCompare('abc', 'abcd'));

                $validator->validate();
                print_r($validator->getErrors());
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $this->getResponse();
    }
}