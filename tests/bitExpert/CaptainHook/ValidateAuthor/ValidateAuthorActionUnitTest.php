<?php

/*
 * This file is part of the Captain Hook Validate Author plugin package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bitExpert\CaptainHook\ValidateAuthor;

use CaptainHook\App\Config;
use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Options;
use CaptainHook\App\Console\IO;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use RuntimeException;
use SebastianFeldmann\Git\Log\Commit;
use SebastianFeldmann\Git\Repository;

class ValidateAuthorActionUnitTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;
    /**
     * @var IO|MockObject
     */
    private $io;
    /**
     * @var Repository|MockObject
     */
    private $repository;
    /**
     * @var Action|MockObject
     */
    private $action;
    /**
     * @var ValidateAuthorAction
     */
    private $hook;

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(Config::class);
        $this->io = $this->createMock(IO::class);
        $this->repository = $this->createMock(Repository::class);
        $this->action = $this->createMock(Action::class);
        $this->hook = $this->createPartialMock(ValidateAuthorAction::class, ['getConfig']);
    }

    /**
     * @test
     */
    public function missingConfigurationStopsExecution()
    {
        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options([]));

        $this->hook->expects(self::never())
            ->method('getConfig');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function configuredNameRegexWillTriggerValidation()
    {
        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['name' => '/Some author name/']));

        $this->hook->expects(self::once())
            ->method('getConfig')
            ->with($this->repository, 'user.name')
            ->willReturn('Some author name');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function nameValidationWillNotThrowExceptionIfMatches()
    {
        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['name' => '/[A-F]+/']));

        $this->hook->expects(self::once())
            ->method('getConfig')
            ->with($this->repository, 'user.name')
            ->willReturn('ABCDEF');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function nameValidationWillThrowExceptionIfNotMatches()
    {
        self::expectException(RuntimeException::class);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['name' => '/^[1-9]+$/']));

        $this->hook->expects(self::once())
            ->method('getConfig')
            ->with($this->repository, 'user.name')
            ->willReturn('ABCDEF');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function configuredEmailRegexWillTriggerValidation()
    {
        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['email' => '/test@test.loc/']));

        $this->hook->expects(self::once())
            ->method('getConfig')
            ->with($this->repository, 'user.email')
            ->willReturn('test@test.loc');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function emailValidationWillNotThrowExceptionIfMatches()
    {
        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['email' => '/@/']));

        $this->hook->expects(self::once())
            ->method('getConfig')
            ->with($this->repository, 'user.email')
            ->willReturn('test@test.loc');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function emailValidationWillThrowExceptionIfNotMatches()
    {
        self::expectException(RuntimeException::class);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['email' => '/^[1-9]+$/']));

        $this->hook->expects(self::once())
            ->method('getConfig')
            ->with($this->repository, 'user.email')
            ->willReturn('test@test.loc');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }
}
