<?php

    namespace Creator;

    use Creator\Exceptions\Container\ContainerException;
    use Creator\Exceptions\Container\NotFoundException;
    use Creator\Exceptions\Unreflectable;
    use Creator\Exceptions\Unresolvable;
    use Psr\Container\ContainerInterface;

    class Container extends AbstractResourceRegistryAware implements ContainerInterface {

        /**
         * @var Creator
         */
        private $creator;

        /**
         * @param Creator $creator
         */
        function __construct (Creator $creator) {
            parent::__construct($creator->resourceRegistry);
            $this->creator = $creator;
        }

        /**
         * @param string $identifier
         * @return mixed|object
         */
        function get ($identifier) {
            if ($this->resourceRegistry->hasPrimitiveResource($identifier)) {
                return $this->resourceRegistry->getPrimitiveResource($identifier);
            }

            $classResource = $this->resourceRegistry->getClassResource($identifier);
            if ($classResource !== null) {
                return $classResource;
            }

            try {
                return $this->creator->create($identifier);
            } catch (Unreflectable $unreflectable) {
                throw NotFoundException::createFromUnreflectable($unreflectable);
            } catch (Unresolvable $unresolvable) {
                throw ContainerException::createFromUnresolvable($unresolvable);
            }
        }

        /**
         * @param string $identifier
         * @return bool
         * @throws \ReflectionException
         */
        function has ($identifier) : bool {
            if ($this->resourceRegistry->hasPrimitiveResource($identifier)) {
                return true;
            }

            if ($this->resourceRegistry->hasFactoryForClassResource($identifier)) {
                return true;
            }

            if ($this->resourceRegistry->getClassResource($identifier) !== null) {
                return true;
            }

            // If we have no factory and the class does not exist (yet) we can not have a fulfilling instance
            if (class_exists($identifier, false) === false) {
                return false;
            }

            if ($this->resourceRegistry->findFulfillingInstance(Creatable::createFromClassName($identifier)) !== null) {
                return true;
            }

            return false;
        }

    }