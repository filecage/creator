<?php

    namespace Creator;

    use Creator\Exceptions\InvalidFactoryResult;
    use Creator\Interfaces\SelfFactory;

    class Creatable extends InvokableMethod {

        /**
         * @var string
         */
        private $className;

        /**
         * @var \ReflectionClass
         */
        private $reflectionClass;

        /**
         * @var string|null
         */
        private $creationMethodName;

        /**
         * @var bool
         */
        private $useConstructor = true;

        /**
         * @param string $className
         * @param string|null $creationMethodName
         * @return Creatable
         * @throws \ReflectionException
         */
        static function createFromClassName (string $className, string $creationMethodName = null) : self {
            return new static(new \ReflectionClass($className), $creationMethodName);
        }

        /**
         * @param \ReflectionClass $reflectionClass
         * @param null $creationMethodName
         * @throws \ReflectionException
         */
        function __construct (\ReflectionClass $reflectionClass, $creationMethodName = null) {
            $this->className = $reflectionClass->getName();
            $this->reflectionClass = $reflectionClass;
            $this->creationMethodName = $creationMethodName;

            if ($creationMethodName === null && $reflectionClass->implementsInterface(SelfFactory::class)) {
                $selfCreationClosure = new \ReflectionFunction($this->reflectionClass->getMethod('createSelf')->invoke(null));
                parent::__construct($selfCreationClosure);
                $this->useConstructor = false; // TODO: There must be a nicer way to achieve the self-factories 🤔
            } else {
                parent::__construct($creationMethodName !== null ? $this->reflectionClass->getMethod($creationMethodName) : $this->reflectionClass->getConstructor());
            }
        }

        /**
         * @return string
         */
        function getName () : string {
            return $this->className . '::' . ($this->creationMethodName ?? $this->reflectionClass->getConstructor()->getName()) . '()';
        }

        /**
         * @param array|null $args
         *
         * @return object
         */
        function invoke (array $args = null) {
            if ($this->useConstructor) {
                return $this->invokeConstructor($args);
            }

            $instance = $this->invokeReflection($args);
            if (!$instance instanceof $this->className) {
                throw new InvalidFactoryResult($this->className, null, $instance);
            }

            return $instance;
        }

        /**
         * @return \ReflectionClass
         */
        function getReflectionClass () {
            return $this->reflectionClass;
        }

        /**
         * @param array|null $args
         * @return object
         */
        private function invokeConstructor (?array $args) : object {
            if ($args !== null) {
                return $this->reflectionClass->newInstanceArgs($args);
            }

            return $this->reflectionClass->newInstance();
        }

        /**
         * @param array|null $args
         * @return object
         */
        private function invokeReflection (?array $args) : object {
            if ($args !== null) {
                return $this->invokableReflection->invokeArgs($args);
            }

            return $this->invokableReflection->invoke();
        }

    }