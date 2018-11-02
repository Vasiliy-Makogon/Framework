<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Registry;
use Krugozor\Framework\Thumbnail\Factory;
use Krugozor\Framework\Utility\Upload\DirectoryGenerator;
use Krugozor\Framework\Utility\Upload\File;

class Thumbnail extends Controller
{
    public function run()
    {
        if (Request::isPost() && !empty($_FILES['file'])) {
            try {
                $upload = new File($_FILES['file']);
                $upload
                    ->setMaxFileSize(Registry::getInstance()->UPLOAD['MAX_FILE_SIZE'])
                    ->setAllowableMimeType('image/jpeg', 'image/gif', 'image/png', 'image/pjpeg', 'image/x-png');

                if ($upload->isFileUpload()) {
                    if ($upload->hasMimeTypeErrors()) {
                        $this->getView()->error = 'Загруженный файл имеет недопустимый mime-тип';
                    } else if ($upload->hasFileSizeErrors()) {
                        $this->getView()->error = 'Загруженный файл имеет недопустимый размер, допустимый размер: ' .
                            Registry::getInstance()->UPLOAD['MAX_FILE_SIZE'];
                    }

                    if (!$this->getView()->error) {
                        $upload
                            ->setFileNameAsUnique()
                            ->copy(DOCUMENTROOT_PATH . Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL']);

                        $directory_generator = new DirectoryGenerator($upload->getFileNameWithoutExtension());

                        $directory = $directory_generator->create(DOCUMENTROOT_PATH . Registry::getInstance()->UPLOAD['THUMBNAIL_150x100']);
                        $creator = Factory::create(
                            DOCUMENTROOT_PATH . Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL'] . $upload->getFileNameWithExtension(),
                            $directory . $upload->getFileNameWithoutExtension()
                        );

                        $creator->setResizedWidth(130);
                        $creator->setResizedHeight(86);
                        $creator->resizeFixed();

                        $this->getView()->path_to_image = $directory_generator->getHttpPath() . $creator->getFileNameWithExt();

                        $directory = $directory_generator->create(DOCUMENTROOT_PATH . Registry::getInstance()->UPLOAD['THUMBNAIL_800x800']);
                        $creator = Factory::create(
                            DOCUMENTROOT_PATH . Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL'] . $upload->getFileNameWithExtension(),
                            $directory . $upload->getFileNameWithoutExtension()
                        );
                        $creator->setResizedWidth(800);
                        $creator->setResizedHeight(800);
                        $creator->resize(strtoupper(Registry::getInstance()->HOSTINFO['HOST_SIMPLE']));

                        @unlink(DOCUMENTROOT_PATH . Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL'] . $upload->getFileNameWithExtension());

                        $data = new CoverArray();
                        $data->file_name = $creator->getFileNameWithExt();
                        $file = $this->getMapper('Advert/Thumbnail')->createModel();
                        $file->setData($data);
                        $this->getMapper('Advert/Thumbnail')->saveModel($file);
                        $this->getView()->thumbnail_id = $file->getId();
                    }
                } else {
                    if ($size = $upload->hasFileSizeErrors()) {
                        $this->getView()->error =
                            'Файл не был загружен, т.к. имеет недопустимый размер, допустимый размер: ' .
                            File::getStringFromBytes($size) . ' Мб.';
                    }
                }
            } catch (\Exception $e) {
                @unlink(DOCUMENTROOT_PATH . Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL'] . $upload->getFileNameWithExtension());

                $this->getView()->error = $e->getMessage();
            }
        }

        return $this->getView();
    }
}