<?php

namespace Oro\Bundle\LoggerBundle\Tests\Unit\Monolog;

use Doctrine\Common\Cache\CacheProvider;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LoggerBundle\DependencyInjection\Configuration;
use Oro\Bundle\LoggerBundle\Monolog\DetailedLogsHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DetailedLogsHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $config;

    /**
     * @var HandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $nestedHandler;

    /**
     * @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $container;

    /**
     * @var DetailedLogsHandler
     */
    protected $handler;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->nestedHandler = $this->getMockBuilder(HandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $this->container->expects($this->any())
            ->method('has')
            ->with('oro_config.user')
            ->willReturn(true);

        $this->handler = new DetailedLogsHandler();
        $this->handler->setHandler($this->nestedHandler);
        $this->handler->setContainer($this->container);
    }

    public function testIsHandlingApplicationIsNotInstalled()
    {
        /** @var CacheProvider|\PHPUnit\Framework\MockObject\MockObject $cacheProvider */
        $cacheProvider = $this->getMockBuilder(CacheProvider::class)->getMock();
        $this->container
            ->expects($this->once())
            ->method('get')
            ->willReturnMap([
                ['oro_logger.cache', 1, $cacheProvider],
            ]);

        $this->config->expects($this->never())->method('get');

        $this->container
            ->expects($this->once())
            ->method('getParameter')
            ->with('oro_logger.detailed_logs_default_level')
            ->willReturn('warning');

        $this->assertTrue($this->handler->isHandling(['level' => Logger::WARNING]));
    }

    public function testIsHandlingWithCache()
    {
        /** @var CacheProvider|\PHPUnit\Framework\MockObject\MockObject $cacheProvider */
        $cacheProvider = $this->getMockBuilder(CacheProvider::class)->getMock();
        $cacheProvider->expects($this->once())
            ->method('contains')
            ->with(Configuration::LOGS_LEVEL_KEY)
            ->willReturn(true);

        $cacheProvider->expects($this->once())
            ->method('fetch')
            ->with(Configuration::LOGS_LEVEL_KEY)
            ->willReturn('warning');

        $this->container
            ->expects($this->exactly(1))
            ->method('get')
            ->willReturnMap([
                ['oro_logger.cache', 1, $cacheProvider],
            ]);

        $this->config->expects($this->never())->method('get');
        $this->container->expects($this->never())->method('hasParameter');
        $this->container->expects($this->never())->method('getParameter');

        $this->assertTrue($this->handler->isHandling(['level' => Logger::WARNING]));
    }
}
