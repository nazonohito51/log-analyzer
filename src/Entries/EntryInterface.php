<?php
namespace LogAnalyzer\Entries;

interface EntryInterface
{
    public function haveProperty($property_name);
    public function getProperties();
}
