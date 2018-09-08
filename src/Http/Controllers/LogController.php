<?php

namespace Laravel\Telescope\Http\Controllers;

use Laravel\Telescope\EntryType;
use Illuminate\Routing\Controller;

class LogController extends EntryController
{
    /**
     * The entry type for the controller.
     *
     * @return string
     */
    protected function entryType()
    {
        return EntryType::LOG;
    }
}
