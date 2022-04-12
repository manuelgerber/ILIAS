<?php declare(strict_types = 1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
trait ilWebDAVCommonINodeFunctionsTrait
{
    protected function retrieveLastModifiedAsIntFromObjectLastUpdateString(string $last_update) : ?int
    {
        $last_update_int = strtotime($this->obj->getLastUpdateDate());
        if ($last_update_int !== false) {
            return $last_update_int;
        }
        
        return null;
    }
}