<?php  //[STAMP] af6840482dfdf8ee9508769edeb38d10
namespace _generated;

// This class was automatically generated by build task
// You should not change it manually as it will be overwritten on next build
// @codingStandardsIgnoreFile

use Codeception\Module\MessageHelper;

trait MessageGuyActions
{
   
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     *
     * @see \Codeception\Module\MessageHelper::getMessage()
     */
    public function getMessage($name) {
        return $this->scenario->runStep(new \Codeception\Step\Action('getMessage', func_get_args()));
    }
}