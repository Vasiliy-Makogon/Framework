<?php
try {
    require('./vendor/autoload.php');
    require('./configuration/bootstrap.php');

    Krugozor_Http_Response::getInstance()
        ->setHeader(Krugozor_Http_Response::HEADER_CONTENT_TYPE, 'text/html; charset=utf-8')
        ->setHeader('Content-Language', Krugozor_Registry::getInstance()->LOCALIZATION['LANG'])
        ->setHeader('Expires', 'Mon, 26 Jul 2008 05:00:00 GMT')
        ->setHeader('Last-Modified', gmdate("D, d M Y H:i:s") . " GMT")
        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
        ->setHeader('Pragma', 'no-cache');

    Krugozor_Context::getInstance()
        ->setRequest(Krugozor_Http_Request::getInstance())
        ->setResponse(Krugozor_Http_Response::getInstance())
        ->setDatabase(\Krugozor\Database\Mysql\Mysql::create(
            Krugozor_Registry::getInstance()->DATABASE['HOST'],
            Krugozor_Registry::getInstance()->DATABASE['USER'],
            Krugozor_Registry::getInstance()->DATABASE['PASSWORD']
        )->setDatabaseName(Krugozor_Registry::getInstance()->DATABASE['NAME'])
            ->setCharset(Krugozor_Registry::getInstance()->DATABASE['CHARSET'])
        );

    $application = new Krugozor_Application(Krugozor_Context::getInstance());
    $application->setRoutesFromPhpFile(ROUTES_PATH);
    $application->run();

} catch (Exception $e) {
    error_log($e->getMessage(), 0);

    $mail = new Krugozor_Mail();
    $mail->setTo(Krugozor_Registry::getInstance()->EMAIL['ADMIN'])
        ->setFrom(Krugozor_Registry::getInstance()->EMAIL['NOREPLY'])
        ->setReplyTo(Krugozor_Registry::getInstance()->EMAIL['NOREPLY'])
        ->setHeader('Error on ' . Krugozor_Registry::getInstance()->HOSTINFO['HOST_SIMPLE'])
        ->setTemplate(DOCUMENTROOT_PATH . '/Krugozor/Module/Common/Template/ErrorInfo.mail');
    $mail->message = $e->getMessage();
    $mail->trace = $e->getTraceAsString();
    $mail->line = $e->getLine();
    $mail->file = $e->getFile();
    $mail->host = Krugozor_Registry::getInstance()->HOSTINFO['HOST'];
    $mail->send();

    Krugozor_Context::getInstance()->setResponse(Krugozor_Http_Response::getInstance());
    Krugozor_Context::getInstance()->getResponse()
        ->setHeader(Krugozor_Http_Response::HEADER_CONTENT_TYPE, 'text/plain; charset=utf-8')
        ->sendHeaders();

    echo $e->getMessage() . PHP_EOL . PHP_EOL;

    if (Krugozor_Registry::getInstance()->DEBUG['ENABLED_DEBUG_INFO']) {
        echo print_r($e->getTraceAsString(), 1)  . PHP_EOL . PHP_EOL;

        if (Krugozor_Context::getInstance()->getDatabase()) {
            echo Krugozor_Context::getInstance()->getDatabase()->getQueryString();
        }
    }
}