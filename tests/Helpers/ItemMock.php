<?php
namespace Tests\LogAnalyzer\Helpers;

use LogAnalyzer\CollectionBuilder\Items\Item;

class ItemMock extends Item
{
    public function getIncludedFiles()
    {
        return explode(',', $this->get('included_files'));
    }
}
