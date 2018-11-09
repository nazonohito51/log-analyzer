<?php
namespace LogAnalyzer\View;

use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use ProgressBar\Manager;
use ProgressBar\Registry;

class ProgressBarObserver
{
    private $progressBarView;
    private $counterWhileInterval = 0;
    private $beforeTimestamp;
    private $intervalSec;

    public function start($max, $intervalSec = 0.5)
    {
        $this->progressBarView = new Manager(0, $max, 120);
        $this->progressBarView->setFormat("%current%/%max% [%bar%] %percent%% %eta%   %additionMessage%");
        $this->progressBarView->addReplacementRule('%additionMessage%', 5, function ($buffer, $registry) {
            /** @var Registry $registry */
            return $registry->getValue('additionMessage');
        });

        $this->intervalSec = $intervalSec;
        $this->beforeTimestamp = microtime(true);
    }

    public function end()
    {
        $this->progressBarView = null;
    }

    public function update($additionMessage = '')
    {
        $this->counterWhileInterval++;
        if (microtime(true) - $this->beforeTimestamp > $this->intervalSec) {
            $this->progressBarView->getRegistry()->setValue('additionMessage', $additionMessage);
//            $this->pregressBarView->getRegistry()->setValue('file', $file->getFilename());
//            $this->pregressBarView->getRegistry()->setValue('lineMax', $file->count());
//            $this->pregressBarView->getRegistry()->setValue('line', $file->key());
            $this->progressBarView->update($this->progressBarView->getRegistry()->getValue('current') + $this->counterWhileInterval);

            $this->counterWhileInterval = 0;
            $this->beforeTimestamp = microtime(true);
        }
    }
}
