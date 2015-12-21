<?php

    namespace Creator\Interfaces;

    interface Singleton {

        /**
         * @return $this
         */
        function getInstance();

    }