<?php

class Krugozor_Html_ElementOptgroup extends Krugozor_Html_Element
{
    private $options = array();

    public function __construct()
    {
        parent::__construct();

        $this->attrs = array
        (
            'disabled' => array('disabled'),
            'label' => 'Text',
        );

        $this->all_attrs = array_merge($this->attrs, $this->coreattrs, $this->i18n, $this->events);
    }

    /*
    * Добаволяет новый option к коллекции.
    *
    * @access public
    * @param object Krugozor_Html_ElementOption
    * @return void
    */
    public function addOption(Krugozor_Html_Element $option)
    {
        $this->options[] = $option;
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOptionById($id)
    {
        return isset($this->options[$id]) ? $this->options[$id] : null;
    }

    protected function createDocObject()
    {
        $class = __CLASS__;

        if (is_object($this->doc) && $this->doc instanceof $class) {
            return;
        }

        $this->doc = new DOMDocument('1.0', 'utf-8');

        $optgroup = $this->doc->createElement('optgroup');

        foreach ($this->data as $key => $value) {
            $optgroup->setAttribute($key, $value);
        }

        foreach ($this->options as $option) {
            $node = $this->doc->importNode($option->exportNode(), true);

            $optgroup->appendChild($node);
        }

        $this->doc->appendChild($optgroup);
    }
}

?>