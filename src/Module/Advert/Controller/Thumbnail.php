<?php
class Krugozor_Module_Advert_Controller_Thumbnail extends Krugozor_Controller
{
    public function run()
    {
        if (Krugozor_Http_Request::isPost() && !empty($_FILES['file']))
        {
            try
            {
                $upload = new Krugozor_Utility_Upload_File($_FILES['file']);
                $upload->setMaxFileSize(Krugozor_Registry::getInstance()->UPLOAD['MAX_FILE_SIZE'])
                       ->setAllowableMimeType('image/jpeg', 'image/gif', 'image/png', 'image/pjpeg', 'image/x-png');

                if ($upload->isFileUpload())
                {
                    if ($upload->hasMimeTypeErrors())
                    {
                        $this->getView()->error = 'Загруженный файл имеет недопустимый mime-тип';
                    }
                    // Файл загружен, но превышает размер MAX_FILE_SIZE из файла конфигурации
                    else if ($upload->hasFileSizeErrors())
                    {
                        $this->getView()->error = 'Загруженный файл имеет недопустимый размер, допустимый размер: ' .
                                                  Krugozor_Registry::getInstance()->UPLOAD['MAX_FILE_SIZE'];
                    }

                    if (!$this->getView()->error)
                    {
                        $upload->setFileNameAsUnique()->copy(DOCUMENTROOT_PATH . Krugozor_Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL']);

                        $directory_generator = new Krugozor_Utility_Upload_DirectoryGenerator($upload->getFileNameWithoutExtension());

                        $directory = $directory_generator->create(DOCUMENTROOT_PATH . Krugozor_Registry::getInstance()->UPLOAD['THUMBNAIL_150x100']);
                        $creator = Krugozor_Thumbnail_Factory::create(
                            DOCUMENTROOT_PATH . Krugozor_Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL'] . $upload->getFileNameWithExtension(), // возвр полный путь
                            $directory . $upload->getFileNameWithoutExtension()
                        );

                        $creator->setResizedWidth(150);
                        $creator->setResizedHeight(100);
                        $creator->resizeFixed();

                        $this->getView()->path_to_image = $directory_generator->getHttpPath() . $creator->getFileNameWithExt();

                        $directory = $directory_generator->create(DOCUMENTROOT_PATH . Krugozor_Registry::getInstance()->UPLOAD['THUMBNAIL_800x800']);
                        $creator = Krugozor_Thumbnail_Factory::create(
                            DOCUMENTROOT_PATH . Krugozor_Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL'] . $upload->getFileNameWithExtension(), // возвр полный путь
                            $directory . $upload->getFileNameWithoutExtension()
                        );
                        $creator->setResizedWidth(800);
                        $creator->setResizedHeight(800);
                        $creator->resize(strtoupper(Krugozor_Registry::getInstance()->HOSTINFO['HOST_SIMPLE']));

                        @unlink(DOCUMENTROOT_PATH . Krugozor_Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL'] . $upload->getFileNameWithExtension());

                        $data = new Krugozor_Cover_Array();
                        $data->file_name = $creator->getFileNameWithExt();
                        $file = $this->getMapper('Advert/Thumbnail')->createModel();
                        $file->setData($data->getDataAsArray());
                        $this->getMapper('Advert/Thumbnail')->saveModel($file);
                        $this->getView()->thumbnail_id = $file->getId();
                    }
                }
                else
                {
                    // Загрузка остановлена на основании значения поля MAX_FILE_SIZE в HTML форме
                    // или размер принятого файла превысил максимально допустимый размер, который задан
                    // директивой upload_max_filesize конфигурационного файла php.ini.
                    if (($size = $upload->hasFileSizeErrorFormSize()) || ($size = $upload->hasFileSizeErrors()))
                    {
                        $this->getView()->error = 'Файл не был загружен, т.к. имеет недопустимый размер, допустимый размер: ' .
                                                  Krugozor_Utility_Upload_File::getStringFromBytes($size) . ' Мб.';
                    }
                }
            }
            catch (Exception $e)
            {
                @unlink(DOCUMENTROOT_PATH . Krugozor_Registry::getInstance()->UPLOAD['THUMBNAIL_ORIGINAL'] . $upload->getFileNameWithExtension());

                $this->getView()->error = $e->getMessage();
            }
        }

        return $this->getView();
    }
}