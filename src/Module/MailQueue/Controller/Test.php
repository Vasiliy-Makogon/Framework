<?php

namespace Krugozor\Framework\Module\MailQueue\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\MailQueue\Model\MailQueue;

class Test extends Controller
{
    public function run()
    {
        if (!$this->getCurrentUser()->isAdministrator()) {
            echo "Тестировать MailQueue может только администратор";
            exit;
        }

        $mailQueue = new MailQueue();

        $anchor = 'Krugozor\\Framework\\Module\\MailQueue\\Anchor';
        if (!class_exists($anchor)) {
            throw new \RuntimeException("Not found Anchor-file at `$anchor`");
        }

        $mailQueue
            ->setSendDate(new \Krugozor\Framework\Type\Datetime())
            ->setTemplate($anchor::getPath() . '/Template/Test.mail')
            ->setToEmail('to_email@test.ru')
            ->setFromEmail('from_email@test.ru')
            ->setHeader('test mail')
            ->setCcEmail('cc_email@test.ru')
            ->setReplyEmail('reply_email@test.ru')
            ->setMailData([
                'name' => 'Вася',
            ]);

        echo $this->getMapper('MailQueue/MailQueue')->saveModel($mailQueue)->getId();
        exit;
    }
}