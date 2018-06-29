<?php
namespace LogAnalyzer\View;

use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use ProgressBar\Manager;
use ProgressBar\Registry;

class ProgressBar
{
    const INTERVAL_SEC = 0.5;

    private $manager;
    private $counter = 0;
    private $beforeTimestamp;

    public function __construct($max)
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
    }

    public function update(LogFile $file, $linePos)
    {
        $this->counter++;
        if (microtime(true) - $this->beforeTimestamp > self::INTERVAL_SEC) {
            $this->manager->getRegistry()->setValue('file', $file->getFilename());
            $this->manager->getRegistry()->setValue('lineMax', $file->getLineCount());
            $this->manager->getRegistry()->setValue('line', $linePos);
            $this->manager->update($this->manager->getRegistry()->getValue('current') + $this->counter);

            $this->counter = 0;
            $this->beforeTimestamp = microtime(true);
        }
    }
}
