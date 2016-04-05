<?php
namespace BrauneDigital\ImagineBundle\Model;

interface RuntimeConfigInterface {

    /**
     * @return mixed Adds additional RuntimeFilters before manual runtime filters
     */
    public function getRuntimeConfiguration();
}