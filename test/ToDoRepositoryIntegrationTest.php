<?php

namespace Acme;

use PHPUnit\Framework\TestCase;

class ToDoRepositoryIntegrationTest extends TestCase
{
    /**
     * @var ToDoRepository
     */
    private $SUT;

    protected function setUp(): void
    {
        $app = ToDoApp::bootstrap();
        $this->SUT = $app->toDoRepo;
        $this->SUT->dropSchema();
        $this->SUT->initSchema();
    }

    public function test_add_and_find()
    {
        $todo = new ToDo('foo');

        assertNull($this->SUT->find($todo->getId()));

        $this->SUT->add($todo);

        assertEquals($todo, $this->SUT->find($todo->getId()));
    }

    public function test_add_and_getall()
    {
        $todo1 = new ToDo('foo');
        $todo2 = new ToDo('bar');

        $this->SUT->add($todo1);
        $this->SUT->add($todo2);

        assertEquals([ $todo1, $todo2 ] , $this->SUT->getAll());
    }

    public function test_add_update_and_find()
    {
        $todo = new ToDo('foo');

        $this->SUT->add($todo);

        $todo->setContent('bar');

        $this->SUT->update($todo);

        assertEquals($todo, $this->SUT->find($todo->getId()));
    }
}
