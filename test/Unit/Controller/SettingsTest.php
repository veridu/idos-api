<?php

namespace Test\Unit\Controller;

use App\Command\ResponseDispatch;
use App\Command\Setting\CreateNew;
use App\Command\Setting\DeleteAll;
use App\Command\Setting\DeleteOne;
use App\Command\Setting\UpdateOne;
use App\Controller\Settings;
use App\Entity\Company;
use App\Entity\Setting as SettingEntity;
use App\Factory\Command;
use App\Repository\DBSetting;
use Illuminate\Support\Collection;
use Jenssegers\Optimus\Optimus;
use League\Tactician\CommandBus;
use Slim\Http\Request;
use Slim\Http\Response;
use Test\Unit\AbstractUnit;

class SettingsTest extends AbstractUnit {
    public function testListAll() {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $requestMock
            ->method('getAttribute')
            ->will(
                $this->returnValue(
                    new Company(
                        ['id' => 1]
                    )
                )
            );

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock = $this->getMockBuilder(DBSetting::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllByCompanyId'])
            ->getMock();
        $repositoryMock
            ->method('getAllByCompanyId')
            ->will(
                $this->returnValue(
                    new Collection(
                        [
                            'section'    => 'section',
                            'updated_at' => time()
                        ]
                    )
                )
            );

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $commandBus
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($responseMock));

        $commandFactory = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $commandFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new ResponseDispatch()));

        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsMock = $this->getMockBuilder(Settings::class)
            ->setConstructorArgs([$repositoryMock, $commandBus, $commandFactory, $optimus])
            ->setMethods(null)
            ->getMock();

        $this->assertSame($responseMock, $settingsMock->listAll($requestMock, $responseMock));
    }

    public function testListAllFromSection() {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $requestMock
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        new Company(
                            ['id' => 1]
                        )
                    ),
                    'section'
                )
            );

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock = $this->getMockBuilder(DBSetting::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllByCompanyIdAndSection'])
            ->getMock();
        $repositoryMock
            ->method('getAllByCompanyIdAndSection')
            ->will(
                $this->returnValue(
                    new Collection(
                        [
                            'section'    => 'section',
                            'updated_at' => time()
                        ]
                    )
                )
            );

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $commandBus
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($responseMock));

        $commandFactory = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $commandFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new ResponseDispatch()));

        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsMock = $this->getMockBuilder(Settings::class)
            ->setConstructorArgs([$repositoryMock, $commandBus, $commandFactory, $optimus])
            ->setMethods(null)
            ->getMock();

        $this->assertSame($responseMock, $settingsMock->listAllFromSection($requestMock, $responseMock));
    }

    public function testGetOne() {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $requestMock
            ->expects($this->exactly(3))
            ->method('getAttribute')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        new Company(
                            ['id' => 1]
                        )
                    ),
                    'section',
                    'propName'
                )
            );

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock = $this->getMockBuilder(DBSetting::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOne'])
            ->getMock();
        $repositoryMock
            ->method('findOne')
            ->will(
                $this->returnValue(
                    new SettingEntity(
                        [
                            'section'    => 'section',
                            'property'   => 'property',
                            'value'      => 'value',
                            'created_at' => time(),
                            'updated_at' => time()
                        ]
                    )
                )
            );

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $commandBus
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($responseMock));

        $commandFactory = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $commandFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new ResponseDispatch()));

        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsMock = $this->getMockBuilder(Settings::class)
            ->setConstructorArgs([$repositoryMock, $commandBus, $commandFactory, $optimus])
            ->setMethods(null)
            ->getMock();

        $this->assertSame($responseMock, $settingsMock->getOne($requestMock, $responseMock));
    }

    public function testCreateNew() {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute', 'getParsedBody'])
            ->getMock();
        $requestMock
            ->method('getAttribute')
            ->will(
                $this->returnValue(
                    new Company(
                        ['id' => 1]
                    )
                )
            );
        $requestMock
            ->method('getParsedBody')
            ->will($this->returnValue(['request' => 'request']));

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock = $this->getMockBuilder(DBSetting::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllByCompanyId'])
            ->getMock();
        $repositoryMock
            ->method('getAllByCompanyId')
            ->will(
                $this->returnValue(
                    new Collection(
                        [
                            'section'    => 'section',
                            'updated_at' => time()
                        ]
                    )
                )
            );

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->disableOriginalConstructor()
            ->setMethods(['handle'])
            ->getMock();
        $commandBus
            ->expects($this->exactly(2))
            ->method('handle')
            ->will(
                $this->onConsecutiveCalls(
                    new SettingEntity(
                        [
                            'section'    => 'section',
                            'property'   => 'property',
                            'value'      => 'value',
                            'created_at' => time(),
                            'updated_at' => time()
                        ]
                    ),
                    $responseMock
                )
            );

        $commandFactory = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $commandFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls(new CreateNew(), new ResponseDispatch()));

        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsMock = $this->getMockBuilder(Settings::class)
            ->setConstructorArgs([$repositoryMock, $commandBus, $commandFactory, $optimus])
            ->setMethods(null)
            ->getMock();

        $this->assertSame($responseMock, $settingsMock->createNew($requestMock, $responseMock));
    }

    public function testDeleteAll() {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $requestMock
            ->method('getAttribute')
            ->will(
                $this->returnValue(
                    new Company(
                        ['id' => 1]
                    )
                )
            );

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock = $this->getMockBuilder(DBSetting::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllByCompanyId'])
            ->getMock();
        $repositoryMock
            ->method('getAllByCompanyId')
            ->will(
                $this->returnValue(
                    new Collection(
                        [
                            'section'    => 'section',
                            'updated_at' => time()
                        ]
                    )
                )
            );

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->disableOriginalConstructor()
            ->setMethods(['handle'])
            ->getMock();
        $commandBus
            ->expects($this->exactly(2))
            ->method('handle')
            ->will(
                $this->onConsecutiveCalls(7, $responseMock));

        $commandFactory = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $commandFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls(new DeleteAll(), new ResponseDispatch()));

        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsMock = $this->getMockBuilder(Settings::class)
            ->setConstructorArgs([$repositoryMock, $commandBus, $commandFactory, $optimus])
            ->setMethods(null)
            ->getMock();

        $this->assertSame($responseMock, $settingsMock->deleteAll($requestMock, $responseMock));
    }

    public function testDeleteOne() {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $requestMock
            ->expects($this->exactly(3))
            ->method('getAttribute')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        new Company(
                            ['id' => 1]
                        )
                    ),
                    'section',
                    'propName'
                )
            );

        $repositoryMock = $this->getMockBuilder(DBSetting::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->disableOriginalConstructor()
            ->setMethods(['handle'])
            ->getMock();
        $commandBus
            ->expects($this->exactly(2))
            ->method('handle')
            ->will(
                $this->onConsecutiveCalls(1, $responseMock));

        $commandFactory = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $commandFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls(new DeleteOne(), new ResponseDispatch()));

        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsMock = $this->getMockBuilder(Settings::class)
            ->setConstructorArgs([$repositoryMock, $commandBus, $commandFactory, $optimus])
            ->setMethods(null)
            ->getMock();

        $this->assertSame($responseMock, $settingsMock->deleteOne($requestMock, $responseMock));
    }

    public function testUpdateOne() {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute', 'getParsedBody'])
            ->getMock();
        $requestMock
            ->expects($this->exactly(3))
            ->method('getAttribute')
            ->will(
                $this->onConsecutiveCalls(
                    new Company(
                        ['id' => 1]
                    ),
                    'section',
                    'property'
                )
            );

        $requestMock
            ->method('getParsedBody')
            ->will($this->returnValue(['request' => 'request']));

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock = $this->getMockBuilder(DBSetting::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllByCompanyId'])
            ->getMock();
        $repositoryMock
            ->method('getAllByCompanyId')
            ->will(
                $this->returnValue(
                    new Collection(
                        [
                            'section'    => 'section',
                            'updated_at' => time()
                        ]
                    )
                )
            );

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->disableOriginalConstructor()
            ->setMethods(['handle'])
            ->getMock();
        $commandBus
            ->expects($this->exactly(2))
            ->method('handle')
            ->will(
                $this->onConsecutiveCalls(
                    new SettingEntity(
                        [
                            'section'    => 'section',
                            'property'   => 'property',
                            'value'      => 'value',
                            'created_at' => time(),
                            'updated_at' => time()
                        ]
                    ),
                    $responseMock
                )
            );

        $commandFactory = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $commandFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls(new UpdateOne(), new ResponseDispatch()));

        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsMock = $this->getMockBuilder(Settings::class)
            ->setConstructorArgs([$repositoryMock, $commandBus, $commandFactory, $optimus])
            ->setMethods(null)
            ->getMock();

        $this->assertSame($responseMock, $settingsMock->updateOne($requestMock, $responseMock));
    }
}
