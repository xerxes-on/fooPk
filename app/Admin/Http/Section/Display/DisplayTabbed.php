<?php
/**
 * @copyright   Copyright © 2020 Lindenvalley GmbH (http://www.lindenvalley.de/)
 * @author      Andrey Rayfurak <andrey.rayfurak@lindenvalley.de>
 * @date        23.01.2020
 */

namespace App\Admin\Http\Section\Display;

use SleepingOwl\Admin\Contracts\Display\TabInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;

class DisplayTabbed extends \SleepingOwl\Admin\Display\DisplayTabbed
{
    /**
     * @param int $id
     *
     * @return \SleepingOwl\Admin\Display\DisplayTabbed
     */
    public function setId($id)
    {
        $this->getTabs()->each(function (TabInterface $tab) use ($id) {
            if ($tab instanceof FormInterface) {
                $tab->setId($id);
            }
        });

        return $this;
    }
}
