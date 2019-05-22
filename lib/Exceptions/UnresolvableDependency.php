<?php

    namespace Creator\Exceptions;

    class UnresolvableDependency extends Unresolvable {

        /**
         * @var string
         */
        private $parameterName;

        /**
         * @var string|null
         */
        private $dependencyClass;

        /**
         * @var string|null
         */
        private $dependantClass;

        /**
         * @param string $parameterName
         * @param string|null $dependencyClass
         * @param string|null $invokableName
         */
        function __construct (string $parameterName, ?string $dependencyClass, string $invokableName) {
            $this->parameterName = $parameterName;
            $this->dependencyClass = $dependencyClass;
            $this->dependantClass = $invokableName;

            parent::__construct("'{$invokableName}' demands {$this->formatDependency()} as '\${$parameterName}', but the resource is unknown");
        }

        /**
         * @return string
         */
        function getParameterName () : string {
            return $this->parameterName;
        }

        /**
         * @return string|null
         */
        function getDependencyClass () : ?string {
            return $this->dependencyClass;
        }

        /**
         * @return string
         */
        private function formatDependency () : string {
            if ($this->dependencyClass === null) {
                return 'a primitive resource';
            }

            return "class '{$this->dependencyClass}'";
        }

    }