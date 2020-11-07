<?php

namespace Tests\Unit;

use DTApi\Repository\UserRepository;
use DTApi\Models\User;
use Mockery;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    protected $userMock;
    protected $userRepository;

    public function setUp():void
    {
        parent::setUp();

        $this->userMock = Mockery::mock(User::class);
        $this->userMock->id = 1;
        $this->userMock->user_type = 2;
        $this->loggerMock = new Logger('admin_logger');
        $this->loggerMock->pushHandler(new StreamHandler(storage_path('logs/admin/laravel-' . date('Y-m-d') . '.log'), Logger::DEBUG));
        $this->loggerMock->pushHandler(new FirePHPHandler());

    }
    public function testEnable()
    {
        $this->userMock->shouldReceive('findOrFail')->with($this->userMock->id)->andReturn(array());
        $this->userMock->status = 1;
        $this->userMock->shouldReceive('save')->andReturn(array());
        $this->userRepository = new UserRepository($this->userMock);
        $this->assertEquals(true, $this->userRepository->enable($this->userMock->id));
    }
    public function testDisable()
    {
        $this->userMock->shouldReceive('findOrFail')->with($this->userMock->id)->andReturn(array());
        $this->userMock->status = 0;
        $this->userMock->shouldReceive('save')->andReturn(array());
        $this->userRepository = new UserRepository($this->userMock);
        $this->assertEquals(true, $this->userRepository->disable($this->userMock->id));
    }
    public function testGetTranslators()
    {
        $this->userMock->shouldReceive('get')->andReturn(array());
        $this->userRepository = new UserRepository($this->userMock);
        $this->assertEquals(array(), $this->userRepository->getTranslators());
    }
    public function createOrUpdateTest()
    {
        $request = new \Illuminate\Http\Request();
        $this->userMock->shouldReceive('findOrFail')->with($this->userMock->id)->andReturn(array());
        $this->assertEquals(array(), $this->userRepository->createOrUpdate($this->userMock->id,$request));
    }

}
