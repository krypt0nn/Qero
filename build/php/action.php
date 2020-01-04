<?php

namespace Qero\Actions;

interface Action
{
    public function __construct (array $args, array $params = []);
    public function execute (): void;
}
