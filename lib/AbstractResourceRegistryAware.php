<?php

    namespace Creator;

    /**
     * This class is used to allow a shared access to the Registry between Creator itself and the
     * Container implementation without providing access to public.
     *
     * Do not use this class anywhere else.
     */
    abstract class AbstractResourceRegistryAware {

        /**
         * @var ResourceRegistry
         */
        protected $resourceRegistry;

        /**
         * @param ResourceRegistry $resourceRegistry
         */
        function __construct (ResourceRegistry $resourceRegistry) {
            $this->resourceRegistry = $resourceRegistry;
        }

    }