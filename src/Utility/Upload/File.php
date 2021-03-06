<?php

namespace Krugozor\Framework\Utility\Upload;

/**
 * Класся для загрузки файлов.
 */
class File
{
    /**
     * Массив, содержащий информацию о загруженном файле.
     * Аналог содержания одного элемента $_FILES.
     *
     * @var array
     */
    protected $file;

    /**
     * Максимально-допустимый размер загружаемого файла, в байтах.
     *
     * @var int
     */
    protected $max_file_size;

    /**
     * Массив допустимых MIME-типов загружаемого файла.
     *
     * @var array
     */
    protected $allowable_mime_types = array();

    /**
     * Будущее расширение загруженного файла. Опционально.
     *
     * @var string
     */
    protected $file_ext;

    /**
     * Будущее имя загружаемого файла. Опционально.
     *
     * @var string
     */
    protected $file_name;

    /**
     * Директория, в которую будет загружен файл.
     *
     * @var string
     */
    protected $file_directory;

    /**
     * Максимально-допустимая длинна имени файла.
     *
     * @var int
     */
    const FILE_NAME_MAX_LENGTH = 255;

    /**
     * Максимально-допустимая длинна расширения файла.
     *
     * @var int
     */
    const FILE_EXT_MAX_LENGTH = 10;

    /**
     * Принимает значение одного элемента массива $_FILES
     *
     * @param array
     */
    public function __construct(array $file)
    {
        $this->file = $file;
    }

    /**
     * Устанавливает будущее имя загружаемого файла.
     * Если имя не указывается, файл будет сохранён с оригинальным именем.
     *
     * @param string $file_name
     * @return File
     */
    public function setFileName(string $file_name): self
    {
        $this->file_name = self::deleteBadSymbols(trim($file_name));

        if (strlen($this->file_name) > self::FILE_NAME_MAX_LENGTH) {
            $this->file_name = substr($this->file_name, 0, self::FILE_NAME_MAX_LENGTH);
        }

        return $this;
    }

    /**
     * Устанавливает будущее имя загружаемого файла как хэш md5 от случйной строки.
     *
     * @return File
     */
    public function setFileNameAsUnique(): self
    {
        $this->file_name = md5(microtime(true) . $this->file['tmp_name']);

        return $this;
    }

    /**
     * Устанавливает расширение загружаемого файла.
     * Если расширение не указывается, файл будет сохранён с оригинальным расширением.
     *
     * @param string $file_ext
     * @return File
     */
    public function setFileExt(string $file_ext): self
    {
        $this->file_ext = self::deleteBadSymbols(trim($file_ext));

        if (strlen($this->file_ext) > self::FILE_EXT_MAX_LENGTH) {
            $this->file_ext = substr($this->file_ext, 0, self::FILE_EXT_MAX_LENGTH);
        }

        return $this;
    }

    /**
     * Устанавливает максимально-допустимый размер файла.
     * Значение $size может быть любой формой представления
     * человекопонятной нумерации размерности данных, принятых в PHP: 8M, 2B, 30G
     *
     * @param string $size
     * @return File
     */
    public function setMaxFileSize(string $size): self
    {
        $this->max_file_size = self::getBytesFromString($size);

        return $this;
    }

    /**
     * Возвращает максимально-допустимый размер файла.
     *
     * @return string
     */
    public function getMaxFileSize($type = 'M'): string
    {
        return self::getStringFromBytes($this->max_file_size, $type);
    }

    /**
     * Устанавливает допустимые mime-типы загружаемых файлов.
     *
     * @param array|string массив или строка - допустимые mime-типы
     * @return File
     */
    public function setAllowableMimeType(): self
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $val) {
                    $this->setTrueFilesType($val);
                }
            } else {
                if (!in_array($arg, $this->allowable_mime_types)) {
                    $this->allowable_mime_types[] = strtolower($arg);
                }
            }
        }

        return $this;
    }

    /**
     * Возвращает TRUE, если файл был загружен на сервер и FALSE в противном случае.
     *
     * @return bool
     */
    public function isFileUpload(): bool
    {
        return $this->file['error'] === UPLOAD_ERR_OK && is_uploaded_file($this->file['tmp_name']);
    }

    /**
     * Копирует загруженный файл в директорию $directory.
     *
     * @param string $directory
     * @return File
     */
    public function copy(string $directory): self
    {
        if (!is_dir($directory)) {
            throw new \RuntimeException(__METHOD__ . ': Не найдена указанная директория для загрузки: ' . $directory);
        }

        $this->file_directory = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;

        if ($this->file['error'] === UPLOAD_ERR_OK && file_exists($this->file['tmp_name']) && $this->isFileUpload()) {
            $pathinfo = pathinfo($this->file['name']);

            // Если расширение файла явно объявленно, то оно станет раширением файла при копировании.
            // В противном случае расширением будет оригинальное расширение загруженного файла
            $this->setFileExt($this->file_ext ?: (isset($pathinfo['extension']) ? strtolower($pathinfo['extension']) : ''));

            // Имя файла будет либо оригинальное, либо объявленное пользователем.
            $this->setFileName($this->file_name ?: $pathinfo['filename']);

            if (!@move_uploaded_file($this->file['tmp_name'], $this->file_directory . $this->getFileNameWithExtension())) {
                throw new \RuntimeException(
                    __METHOD__ . ': Ошибка копирования в директорию ' . $this->file_directory
                );
            }
        }

        return $this;
    }

    /**
     * Метод проверки MIME-типа файла.
     * Возвращает mime_type в случае ошибки и false в противном случае.
     *
     * @return bool|string
     */
    public function hasMimeTypeErrors()
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($this->file['tmp_name']);

        if (!empty($mime_type) && $this->allowable_mime_types && !in_array($mime_type, $this->allowable_mime_types)) {
            return $mime_type;
        }

        return false;
    }

    /**
     * Проверяет размер загруженного изображения.
     * Ошибка, вознакающая в ходе данной проверки, может быть вызвана следующими условиями:
     * 1. Размер принятого файла превысил максимально допустимый размер,
     *    который задан директивой upload_max_filesize конфигурационного файла php.ini.
     * 2. Размер загружаемого файла превысил размер $this->max_file_size
     *
     * @return int
     */
    public function hasFileSizeErrors(): int
    {
        if ($this->file['error'] === UPLOAD_ERR_INI_SIZE) {
            return self::getBytesFromString(ini_get('upload_max_filesize'));
        } else if (!is_null($this->max_file_size) && $this->max_file_size < $this->file['size']) {
            return self::getBytesFromString($this->max_file_size);
        }

        return 0;
    }

    /**
     * Возвращает int, если размер загружаемого файла превысил
     * значение MAX_FILE_SIZE, указанное в HTML-форме.
     *
     * @return int
     */
    public function hasFileSizeErrorFormSize(): int
    {
        if ($this->file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return self::getBytesFromString($this->max_file_size);
        }

        return 0;
    }

    /**
     * Возвращает имя файла с расширением.
     *
     * @return string
     */
    public function getFileNameWithExtension(): string
    {
        return $this->file_name . ($this->file_ext ? '.' . $this->file_ext : '');
    }

    /**
     * Возвращает имя файла без расширения.
     *
     * @return string
     */
    public function getFileNameWithoutExtension(): string
    {
        return $this->file_name;
    }

    /**
     * Возвращает числовое представление строки, которая является одним из форматов представления размера данных в PHP.
     * Доступные опции: K (для килобайт), M (для мегабайт) и G (для гигабайт; доступна начиная с PHP 5.1.0);
     * они регистронезависимы. Все остальное считается байтами.
     * 1M равно одному мегабайту или 1048576 байтам. 1K равно одному килобайту или 1024 байтам.
     *
     * @param string
     * @return int
     */
    public static function getBytesFromString(string $val): int
    {
        $val = str_replace(' ', '', $val);

        if ($val === '0') {
            return 0;
        }

        $size = substr($val, -1);
        $size = preg_match('~[KMG]~i', $size) === 1 ? $size : null;
        $val = $size ? substr($val, 0, -1) : $val;

        switch (strtolower($size)) {
            case 'g':
                $val *= 1024;

            case 'm':
                $val *= 1024;

            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * Возвращает представление числа, которое является кол-вом байтов.
     *
     * @param int $val
     * @param string $type один из типов размерности данных: g, m, k
     */
    public static function getStringFromBytes($val, string $type = 'M'): string
    {
        $val = (int)$val;
        $type = mb_strtoupper($type);

        if (!$val) {
            return 0;
        }

        switch ($type) {
            case 'G':
                $val /= 1024;

            case 'M':
                $val /= 1024;

            case 'K':
                $val /= 1024;
        }

        return round($val, 1) . $type;
    }

    /**
     * Удаляет из строки все служебные символы Windows и Unix.
     *
     * @param string
     * @return string
     */
    private static function deleteBadSymbols(string $in): string
    {
        return preg_replace('~[/\:*?"<>|]~', '', $in);
    }
}