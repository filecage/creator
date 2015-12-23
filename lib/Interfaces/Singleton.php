<?php

    namespace Creator\Interfaces;

    interface Singleton {

        /**
         * @return $this
         */
        static function getInstance();

    }