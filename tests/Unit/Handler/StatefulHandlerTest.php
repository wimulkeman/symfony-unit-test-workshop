<?php


use App\Handler\StateHandler;
use PHPUnit\Framework\TestCase;

class StatefulHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        StateHandler::$state = 'idle';
    }

    public function test_it_changes_it_state_when_a_process_is_running()
    {
        $stateHandler = new StateHandler();
        $this->assertSame('idle',$stateHandler->getState());

        $stateHandler->startProcessing();
        $this->assertSame('processing',$stateHandler->getState());

        $stateHandler->keepRunning();
        $this->assertSame('pending',$stateHandler->getState());
    }

    public function test_it_resets_its_internal_state()
    {
        $stateHandler = new StateHandler();
        $this->assertSame('idle',$stateHandler->getState());
    }
}
