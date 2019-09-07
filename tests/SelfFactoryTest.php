<?php

    namespace Creator\Tests;

    use Creator\Exceptions\InvalidFactoryResult;
    use Creator\Tests\Mocks\SelfFactory\ConfigProvider;
    use Creator\Tests\Mocks\SelfFactory\InvalidSelfFactorizingClass;
    use Creator\Tests\Mocks\SelfFactory\SelfFactorizingClass;

    class SelfFactoryTest extends AbstractCreatorTest {

        function testExpectsInstanceToBeCreatedViaSelfFactory () {
            /** @var SelfFactorizingClass $selfFactorizingClass */
            $selfFactorizingClass = $this->creator->create(SelfFactorizingClass::class);

            $this->assertInstanceOf(SelfFactorizingClass::class, $selfFactorizingClass);
            $this->assertSame('http://localhost/creator?parameter=42', $selfFactorizingClass->getUrlFromClient());
        }

        function testExpectsInstanceToBeCreatedViaSelfFactoryWithInjectedDependency () {
            /** @var SelfFactorizingClass $selfFactorizingClass */
            $selfFactorizingClass = $this->creator->createInjected(SelfFactorizingClass::class)
                ->with(new class extends ConfigProvider {
                    function getBaseUrlConfigurationValue () : string {return 'https://www.example.org';}
                    function getAnotherParamterConfigurationValue () : int {return 9001;}
                }, ConfigProvider::class)
                ->create();

            $this->assertInstanceOf(SelfFactorizingClass::class, $selfFactorizingClass);
            $this->assertSame('https://www.example.org?parameter=9001', $selfFactorizingClass->getUrlFromClient());
        }

        function testExpectsInvalidFactoryResultException () {
            $this->expectException(InvalidFactoryResult::class);
            $this->expectExceptionMessage('SelfFactory `Creator\Tests\Mocks\SelfFactory\InvalidSelfFactorizingClass` returned instance of `Creator\Tests\Mocks\SimpleClass`, expected instance of self instead');

            $this->creator->create(InvalidSelfFactorizingClass::class);
        }

    }