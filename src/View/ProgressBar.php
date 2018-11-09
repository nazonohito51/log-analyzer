<?php
namespace LogAnalyzer\View;

use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use ProgressBar\Manager;
use ProgressBar\Registry;

class ProgressBar
{
    private $manager;
    private $counterWhileInterval = 0;
    private $beforeTimestamp;
    private $intervalSec;

    public function __construct($max, $intervalSec = 0.5)
    {
        $this->manager = new Manager(0, $max, 120);
        $this->manager->setFormat("%current%/%max% [%bar%] %percent%% %eta%   Loading: %file%(%line%/%lineMax%)");
        $this->manager->addReplacementRule('%file%', 5, function ($buffer, $registry) {
            /** @var Registry $registry */
            return $registry->getValue('file');
        });
        $this->manager->addReplacementRule('%line%', 5, function ($buffer, $registry) {
            /** @var Registry $registry */
            return $registry->getValue('line');
        });
        $this->manager->addReplacementRule('%lineMax%', 5, function ($buffer, $registry) {
            /** @var Registry $registry */
            return $registry->getValue('lineMax');
        });

        $this->beforeTimestamp = microtime(true);
        $this->intervalSec = $intervalSec;
    }

    public function update(LogFile $file)
    {
        $this->counterWhileInterval++;
        if (microtime(true) - $this->beforeTimestamp > $this->intervalSec) {
            $this->manager->getRegistry()->setValue('file', $file->getFilename());
            $this->manager->getRegistry()->setValue('lineMax', $file->count());
            $this->manager->getRegistry()->setValue('line', $file->key());
            $this->manager->update($this->manager->getRegistry()->getValue('current') + $this->counterWhileInterval);

            $this->counterWhileInterval = 0;
            $this->beforeTimestamp = microtime(true);
        }
    }
}
