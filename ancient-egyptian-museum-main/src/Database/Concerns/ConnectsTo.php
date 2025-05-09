<?php

namespace AncientEgyptianMuseum\Database\Concerns;

use AncientEgyptianMuseum\Database\Managers\Contracts\DatabaseManager;

trait ConnectsTo
{
    public static function connect(DatabaseManager $manager)
    {
        return $manager->connect();
    }
}