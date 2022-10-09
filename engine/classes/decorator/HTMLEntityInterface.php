<?php

namespace Decorator;

interface HTMLEntityInterface {
    public function getId();
    public function getType();
    public function getContent();
    public function getName();
    public function getVisibility();
    public function changeParam($param, $newValue);
}