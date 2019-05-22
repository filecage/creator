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
        private $invokableName;

        /**
         * @var string|null
         */
        private $parentInvokableName;

        /**
         * @param string $parameterName
         * @param string|null $dependencyClass
         * @param string|null $invokableName
         * @param string|null $parentInvokableName
         */
        function __construct (string $parameterName, ?string $dependencyClass, string $invokableName, ?string $parentInvokableName) {
            $this->parameterName = $parameterName;
            $this->dependencyClass = $dependencyClass;
            $this->invokableName = $invokableName;
            $this->parentInvokableName = $parentInvokableName;

            parent::__construct($this->formatMessage());
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
         * @param string $parentInvokableName
         * @return UnresolvableDependency
         */
        function setParentInvokableName (string $parentInvokableName) : self {
            $this->parentInvokableName = $parentInvokableName;
            $this->message = $this->formatMessage();

            return $this;
        }

        /**
         * @return string
         */
        private function formatMessage () : string {
            return "'{$this->invokableName}' demands {$this->formatDependency()} for parameter '\${$this->parameterName}' but the resource is unresolvable{$this->formatParent()}";
        }

        /**
         * @return string
         */
        private function formatParent () : string {
            if ($this->parentInvokableName === null || $this->parentInvokableName === $this->invokableName) {
                return '';
            }

            return " (inner dependency of '{$this->parentInvokableName}')";
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