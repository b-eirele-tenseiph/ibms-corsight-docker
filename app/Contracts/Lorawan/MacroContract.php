<?php

namespace App\Contracts\Lorawan;

use App\Models\{LorawanGateway, ThirdPartyAccessToken};
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class MacroContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $accessTokenModel  (1.0)  The model of the token from the current
    //                           connection.
    // $token             (2.0)  The token string from access token model.
    // $config            (3.0)  The set configuration based on the current.
    //                           connection
    // $response          (4.0)  The response from the API when requesting for
    //                           oauth.
    // $data              (5.0)  Array of data retrieved from the response.
    // $gateway           (6.0)  The gateway the device belongs to.
    // __construct        (7.0)  The constructor of the macro contract.
    // macro              (8.0)  Get the generated macro based on the current
    //                           connection.
    // getToken           (9.0)  Get the token from database or generate a new
    //                           one if needed.
    // getCurrentToken    (10.0) Get the current token from storage depending
    //                           on the token name.
    // baseUrl            (11.0) Set base URL string depending on the current
    //                           connection.
    // generateToken      (12.0) Generate a token using the configuration of the
    //                           current connection.
    // handleError        (13.0) Throw an exception if the token cannot be
    //                           generated.
    // saveToken          (14.0) Save the token to database.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $accessTokenModel
     * <Function> The model of the token from the current connection.
     *
     * @var \App\Models\ThirdPartyAccessToken
     */
    private $accessTokenModel;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $token
     * <Function> The token string from access token model.
     *
     * @var string
     */
    protected $token;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $config
     * <Function> The set configuration based on the current connection.
     *
     * @var array
     */
    protected array $config;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $response
     * <Function> The response from the API when requesting for oauth.
     *
     * @var \Illuminate\Http\Client\Response
     */
    protected $response;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $data
     * <Function> Array of data retrieved from the response.
     *
     * @var array
     */
    protected $data;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> $gateway
     * <Function> The gateway the device belongs to.
     *
     * @var \App\Models\LorawanGateway
     */
    protected LorawanGateway $gateway;

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> __construct
     * <Function> The constructor of the macro contract.
     *
     * @param string $connection
     */
    public function __construct(
        protected string $connection
    ) {
        $this->config = config("lorawan.connections.{$connection}");
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> macro
     * <Function> Get the generated macro based on the current connection.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    public function macro(): PendingRequest
    {
        return Http::withToken($this->getToken())
            ->baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson();
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getToken
     * <Function> Get the token from database or generate a new one if needed.
     *
     * @return string
     */
    private function getToken(): string
    {
        // get current token stored in the database
        $this->getCurrentToken();

        if (blank($this->accessTokenModel) || $this->accessTokenModel->isSoonToExpire) {

            $this->generateToken(); // Generate new valid token

            $this->handleError(); // Throw exception if token cannot be generated

            $this->saveToken(); // Store new token to the database
        }

        return $this->token;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> getCurrentToken
     * <Function> Get the current token from storage depending on the token name.
     *
     * @return void
     */
    private function getCurrentToken(): void
    {
        // Get token model from storage
        $this->accessTokenModel = ThirdPartyAccessToken::ofName($this->config['token_name'])->first();

        // Assign token string base on resource
        $this->token = optional($this->accessTokenModel)->token;
    }


    /**
     * <Layer number> (11.0)
     *
     * <Processing name> baseUrl
     * <Function> Set base URL string depending on the current connection.
     *
     * @return string
     */
    abstract protected function baseUrl();

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> generateToken
     * <Function> Generate a token using the configuration of the current connection.
     *
     * @return void
     */
    abstract protected function generateToken();

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> handleError
     * <Function> Throw an exception if the token cannot be generated.
     *
     * @return void
     */
    abstract protected function handleError();

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> saveToken
     * <Function> Save the token to database.
     *
     * @return void
     */
    abstract protected function saveToken();
}
