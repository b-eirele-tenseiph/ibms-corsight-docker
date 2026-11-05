<?php

namespace App\Managers;

use App\Contracts\Lorawan\ControlsContract;
use App\Exceptions\Lorawan\InvalidConnection;
use App\Models\LorawanGateway;

class LorawanManager
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $commandableInstance       (1.0) The instance of the commandable class
    //                                  that implements ControlsContract.
    // $macroClass                (2.0) The macro class associated with the
    //                                  connection, if any.
    // __construct                (3.0) The constructor for the LorawanManager.
    // initializeMacroClass       (4.0) Initialize the macro class based on the
    //                                  connection configuration.
    // initializeCommandableClass (5.0) Initialize the commandable class based
    //                                  on the connection configuration.
    // commander                  (6.0) Initialize the commandable class based
    //                                  on the connection configuration.
    // macro                      (7.0) Returns the macro class instance if it
    //                                  exists, otherwise returns null.
    // fromGateway                (8.0) Creates a LorawanManager instance from a
    //                                  LorawanGateway.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $commandableInstance
     * <Function> The instance of the commandable class that implements ControlsContract.
     *
     * @var \App\Contracts\Lorawan\ControlsContract
     */
    private ControlsContract $commandableInstance;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $macroClass
     * <Function> The macro class associated with the connection, if any.
     *
     * @var string
     */
    private ?string $macroClass;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> __construct
     * <Function> The constructor for the LorawanManager.
     *
     * @var string
     */
    public function __construct(
        protected string $connection
    ) {
        $this->connection = $connection;

        $this->initializeCommandableClass();
        $this->initializeMacroClass();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> initializeMacroClass
     * <Function> Initialize the macro class based on the connection configuration.
     *
     * @return void
     */
    private function initializeMacroClass(): void
    {
        $this->macroClass = config("lorawan.connections.{$this->connection}.macro");
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> initializeCommandableClass
     * <Function> Initialize the commandable class based on the connection configuration.
     *
     * @return void
     */
    private function initializeCommandableClass(): void
    {
        $lorawanControlClass = config("lorawan.connections.{$this->connection}.class");

        throw_unless(
            is_subclass_of($lorawanControlClass, ControlsContract::class),
            InvalidConnection::classIsNotValid($lorawanControlClass)
        );

        $this->commandableInstance = app($lorawanControlClass);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> initializeCommandableClass
     * <Function> Initialize the commandable class based on the connection configuration.
     *
     * @return \App\Contracts\Lorawan\ControlsContract
     */
    public function commander(): ControlsContract
    {
        return $this->commandableInstance;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> macro
     * <Function> Returns the macro class instance if it exists, otherwise returns null.
     *
     * @return mixed
     */
    public function macro(): mixed
    {
        return $this->macroClass ? (new $this->macroClass($this->connection))->macro() : null;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> fromGateway
     * <Function> Creates a LorawanManager instance from a LorawanGateway.
     *
     * @param LorawanGateway|null $gateway
     *
     * @return self
     */
    public static function fromGateway(?LorawanGateway $gateway): self
    {
        $connection = optional($gateway)->connection;

        return new self($connection);
    }
}
