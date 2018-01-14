<?php
class Krugozor_Module_Getpassword_Mapper_Getpassword extends Krugozor_Mapper_Common
{
    /**
     * Находит объект по хэшу.
     *
     * @param string $hash
     * @return Krugozor_Module_Getpassword_Model_Getpassword
     */
    public function findByHash($hash)
    {
        $params = array(
            'where' => array(
                'hash = "?s"' => array($hash),
            ),
        );

        return parent::findModelByParams($params);
    }
}