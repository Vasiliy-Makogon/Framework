<?php

namespace Krugozor\Framework\Utility;

class Sitemap
{
    /**
     * DomDocument объект
     *
     * @var \DOMDocument
     */
    private $_xml;

    /**
     * Узел <urlset>, содержащий перечень узлов текущего файла sitemap.
     *
     * @var \DOMElement
     */
    private $_urlset;

    /**
     * Максимально-возможное кол-во записей в файле sitemap.
     *
     * @var int
     */
    private $max_rows_count = 50000;

    /**
     * Счётчик кол-ва строк добавленных в узел $this->_urlset.
     *
     * @var int
     */
    public $rows_counter = 0;

    /**
     * Счётчик кол-ва дочерних файлов sitemap и, по-совместительству,
     * постфикс имён дочерних файлов sitemap.
     *
     * @var int
     */
    private $sitemap_files_counter = 1;

    /**
     * Путь сохранения и расположения файлов sitemap.
     *
     * @var string
     */
    private $sitemap_file_location;

    /**
     * Имя файла sitemap.
     *
     * @var string
     */
    private $sitemap_file_name = 'sitemap';

    /**
     * Расширение файла sitemap.
     *
     * @var string
     */
    private $sitemap_file_extension = '.xml';

    /**
     * Логирование результатов в строку.
     *
     * @var string
     */
    private $log = '';

    /**
     * URL-адрес хоста
     *
     * @var string
     */
    private $http_host;

    /**
     * @param string $sitemap_file_location Путь сохранения и расположения файлов sitemap
     * @param string $http_host URL-адрес хоста
     */
    public function __construct($sitemap_file_location, $http_host)
    {
        $this->sitemap_file_location = rtrim($sitemap_file_location, '/\\') . DIRECTORY_SEPARATOR;
        $this->http_host = rtrim($http_host, '/') . '/';

        $this->log .= "Genearate sitemap " . date('d.m.Y H:i:s') . "\n";
    }

    /**
     * Сохранение дочернего файла sitemap на диск.
     *
     * @return Sitemap
     */
    public function SaveXml()
    {
        $size = 0;

        if ($this->rows_counter > 0) {
            $this->_xml->formatOutput = true;
            $sitemap_file_path = $this->sitemap_file_location . $this->sitemap_file_name . $this->sitemap_files_counter . $this->sitemap_file_extension;
            $size = $this->_xml->save($sitemap_file_path);

            $this->log .= "Write $this->rows_counter rows at sitemap $sitemap_file_path, size: $size bytes\n";
        }

        return $this;
    }

    /**
     * Инициализация объекта xml для генерации дочерних sitemap.
     *
     * @return Sitemap
     */
    public function CreateRoot()
    {
        $this->_xml = new \DomDocument('1.0', 'utf-8');
        // создаем узел <urlset> в корне
        $this->_urlset = $this->_xml->appendChild($this->_xml->createElement('urlset'));
        // создаем атрибут "xmlns"
        $attr = $this->_xml->createAttribute('xmlns');
        // наполняем его конктретным значением
        $attr->value = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        // добавляем к <urlset> атрибут "xmlns"
        $this->_urlset->appendChild($attr);

        return $this;
    }

    /**
     * Добавление информации о странице в sitemap.
     *
     * @param string $url URL-адрес страницы
     * @param string $priority Приоритетность URL относительно других URL на Вашем сайте.
     * @param string $changefreq Вероятная частота изменения этой страницы.
     * @param string $lastmod Дата последнего изменения страницы.
     * @return Sitemap
     */
    public function AddNode($loc, $priority, $changefreq, $lastmod = null)
    {
        // в <urlset> добавляем узел <url>
        $_url = $this->_urlset->appendChild($this->_xml->createElement('url'));

        // в <url> добавляем узелы <loc>, <priority>, <changefreq> и <lastmod>
        $_url->appendChild($this->_xml->createElement('loc'))->appendChild($this->_xml->createTextNode($loc));
        $_url->appendChild($this->_xml->createElement('priority'))->appendChild($this->_xml->createTextNode($priority));
        $_url->appendChild($this->_xml->createElement('changefreq'))->appendChild($this->_xml->createTextNode($changefreq));

        if ($lastmod !== null && strtotime($lastmod) !== false) {
            $_url->appendChild($this->_xml->createElement('lastmod'))->appendChild($this->_xml->createTextNode($lastmod));
        }

        $this->rows_counter++;

        if ($this->rows_counter >= $this->max_rows_count) {
            $this->SaveXml();
            $this->sitemap_files_counter++;
            $this->rows_counter = 0;
            $this->CreateRoot();
        }

        return $this;
    }

    /**
     * Создает индексной файл sitemap.
     *
     * @return Sitemap
     */
    public function CreateMainFile()
    {
        $this->_xml = new \DomDocument('1.0', 'utf-8');
        // создаем узел <sitemapindex> в корне
        $sitemapindex = $this->_xml->appendChild($this->_xml->createElement('sitemapindex'));
        // создаем атрибут "xmlns"
        $attr = $this->_xml->createAttribute('xmlns');
        // наполняем его конктретным значением
        $attr->value = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        // добавляем к <sitemapindex> атрибут "xmlns"
        $sitemapindex->appendChild($attr);

        for ($i = 1; $i <= $this->sitemap_files_counter; $i++) {
            // в <sitemapindex> добавляем узел <sitemap>
            $sitemap = $sitemapindex->appendChild($this->_xml->createElement('sitemap'));
            // в <sitemap> добавляем узел <loc>
            $_loc = $sitemap->appendChild($this->_xml->createElement('loc'))->appendChild(
                $this->_xml->createTextNode($this->http_host . $this->sitemap_file_name . $i . '.xml')
            );
        }

        $this->_xml->formatOutput = true;
        $main_sitemap_file_path = $this->sitemap_file_location . $this->sitemap_file_name . $this->sitemap_file_extension;
        $size = $this->_xml->save($main_sitemap_file_path);

        $this->log .= "Create sitemap index file $main_sitemap_file_path, size: $size bytes \n";

        return $this;
    }

    /**
     * Возвращает строку с информацией о генерации файлов sitemap.
     *
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }
}