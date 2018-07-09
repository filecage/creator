<?php

    namespace Creator;

    use Creator\Exceptions\InvalidFactory;
    use Creator\Interfaces\Factory;

    class InvokableFactory extends Invokable {

        /**
         * @var Creatable
         */
        private $factoryCreatable;

        /**
         * @param $factory
         *
         * @return Invokable|InvokableFactory
         * @throws InvalidFactory
         */
        static function createFromAnyFactory ($factory) {
            if (is_callable($factory)) {
                $invokable = InvokableClosure::createFromCallable($factory);
            } elseif ($factory instanceof Factory) {
                $invokable = InvokableClosure::createFromCallable([$factory, 'createInstance']);
            } elseif (is_string($factory) && class_exists($factory)) {
                $factoryCreatable = new Creatable($factory);
                if (!$factoryCreatable->getReflectionClass()->implementsInterface(Factory::class)) {
                    throw new InvalidFactory(get_class($factory));
                }

                $invokable = new InvokableFactory($factoryCreatable);

            } else {
                throw InvalidFactory::createWithUnknownActualType($factory);
            }

            return $invokable;
        }

        /**
         * @param Creatable $factoryCreatable
         */
        function __construct (Creatable $factoryCreatable) {
            parent::__construct($factoryCreatable->getInvokableReflection());
            $this->factoryCreatable = $factoryCreatable;
        }

        /**
         * @return DependencyContainer
         */
        function getDependencies () {
            $dependencyContainer = (new DependencyContainer())
                ->addDependency(Dependency::createFromCreatable('factory', $this->factoryCreatable));

            return $dependencyContainer->mergeWith(parent::getDependencies()); // merge with actual dependencies for signature collection
        }

        /**
         * @param array $args
         *
         * @return mixed
         * @throws InvalidFactory
         */
        function invoke (array $args = []) {
            $factory = $args[0];
            if (!$factory instanceof Factory) {
                throw new InvalidFactory(get_class($factory), 'lazy bound Factory');
            }

            return $factory->createInstance();
        }

    }