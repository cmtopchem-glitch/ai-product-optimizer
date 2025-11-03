<?php

class AIProductOptimizer extends AbstractModuleCenterModule
{
    protected function _init()
    {
        $this->title = 'AI Product Optimizer';
        $this->description = 'KI-gestützte Produktbeschreibungen mit OpenAI';
        $this->sortOrder = 10000;
    }
}
