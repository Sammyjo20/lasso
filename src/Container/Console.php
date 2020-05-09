<?php

namespace Sammyjo20\Lasso\Container;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Exceptions\ConsoleMethodException;

class Console
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @return Command
     */
    public function getCommand(): Command
    {
        return $this->command;
    }

    /**
     * @param Command $command
     * @return $this
     */
    public function setCommand(Command $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|void
     * @throws ConsoleMethodException
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->getCommand(), $name)) {
            return call_user_func_array([$this->getCommand(), $name], $arguments);
        }

        throw new ConsoleMethodException(sprintf(
            'Method %s::%s does not exist.', get_class($this->getCommand()), $name
        ));
    }
}
