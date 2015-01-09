<?php

namespace Portal\Data\Styles;

use Respect\Data\Styles\Standard;

/**
 * PortalStyle is a Respect/Relational database style that helps the object mapper (Respect/Relational) to understand how the
 * database columns are named and how to infer primary/foreign keys and such.
 * This style is intended to work with the Portal database, as accessed by the routelandia-server project.
 *
 * NOTE:
 *   This style was copied from the NorthWind style that comes with Respect/Relational and modified to work.
 */
class PortalStyle extends Standard
{
    /*
    public function realName($name)
    {
        return $name;
    }

    public function styledName($name)
    {
        return $name;
    }
    */

    public function composed($left, $right)
    {
        $left = $this->pluralToSingular($left);
        return "{$left}{$right}";
    }

    public function identifier($name)
    {
        return $this->pluralToSingular($name) . 'id';
    }

    public function remoteIdentifier($name)
    {
        return $this->pluralToSingular($name) . 'id';
    }
    /*
    public function isRemoteIdentifier($name)
    {
        return (strlen($name) - 2 === strripos($name, 'id'));
    }

    public function remoteFromIdentifier($name)
    {
        if ($this->isRemoteIdentifier($name)) {
            return $this->singularToPlural(substr($name, 0, -2));
        }
    }
    */

}

