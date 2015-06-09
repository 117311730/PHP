<?php
/**
 * @filename SphinxXMLFeed.php 
 * @encoding UTF-8 
 * @author wicky 
 * @link http://www.ijie.com http://www.theknot.com 
 * @copyright Copyright (C) theknot.com 
 * @datetime Jun 6, 2014  11:19:17 AM
 * @Description
 */

class SphinxXMLFeed extends XMLWriter
{

    private $fields = array();
    private $attributes = array();
    private $killList = array();

    public function __construct($options = array())
    {
        $defaults = array(
            'indent' => false,
        );
        $options = array_merge($defaults, $options);

        // Store the xml tree in memory
        $this->openMemory();

        if ($options['indent']) {
            $this->setIndent(true);
        }
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function addDocument($doc)
    {
        $this->startElement('sphinx:document');
        $this->writeAttribute('id', $doc['id']);

        foreach ($doc as $key => $value) {
            // Skip the id key since that is an element attribute
            if ($key == 'id')
                continue;

            $this->startElement($key);
            $this->text($value);
            $this->endElement();
        }

        $this->endElement();
        print $this->outputMemory();
    }

    public function addKillLists($id)
    {
        $this->killList[] = $id;
    }

    public function setKillLists()
    {
        if (!empty($this->killList)) { //add sphinx:killlist
            $this->startElement('sphinx:killlist');
            foreach ($this->killList as $value) {
                $this->startElement('id');
                $this->text($value);
                $this->endElement();
            }
            $this->endElement();
        }
    }
    
    public function beginOutput()
    {
        $this->startDocument('1.0', 'UTF-8');
        $this->startElement('sphinx:docset');
        $this->startElement('sphinx:schema');

        // add fields to the schema
        foreach ($this->fields as $field) {
            $this->startElement('sphinx:field');
            $this->writeAttribute('name', $field);
            $this->endElement();
        }

        // add attributes to the schema
        foreach ($this->attributes as $attributes) {
            $this->startElement('sphinx:attr');
            foreach ($attributes as $key => $value) {
                $this->writeAttribute($key, $value);
            }
            $this->endElement();
        }

        // end sphinx:schema
        $this->endElement();
        print $this->outputMemory();
    }

    public function endOutput()
    {
        // end sphinx:docset
        $this->endElement();
        print $this->outputMemory();
    }

}

