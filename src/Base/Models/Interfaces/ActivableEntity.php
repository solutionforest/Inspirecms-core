<?php

namespace SolutionForest\InspireCms\Base\Models\Interfaces;

interface ActivableEntity
{
    /**
     * Sets the disable state.
     *
     * @param  bool  $save  Whether to save the state immediately. Default is true.
     * @return void
     */
    public function setDisable(bool $save = true);

    /**
     * Set the enable status.
     *
     * @param  bool  $save  Whether to save the status immediately. Default is true.
     * @return void
     */
    public function setEnable(bool $save = true);
}
