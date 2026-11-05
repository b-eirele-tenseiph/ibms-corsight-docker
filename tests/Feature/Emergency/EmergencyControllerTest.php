<?php

namespace Tests\Feature\Emergency;

use App\Http\Controllers\Common\EmergencyController;
use App\Services\Emergency\EmergencyService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Traits\Data;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class EmergencyControllerTest extends TestCase
{
    use RefreshDatabase, Data;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // test_it_dispatches_with_the_authenticated_user_and_redirects_back   (1.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> test_it_dispatches_with_the_authenticated_user_and_redirects_back
     * <Function> Test that the emergency controller dispatches the emergency.
     *
     * @return void
     */
    public function test_it_dispatches_with_the_authenticated_user_and_redirects_back(): void
    {
        // Stub "authenticated" user (plain object to avoid model relations/scopes)
        $userStub = (object) ['id' => 123, 'name' => 'Test User'];
        Auth::shouldReceive('user')->once()->andReturn($userStub);

        // Mock the service injected into the controller
        $service = Mockery::mock(EmergencyService::class);
        $service->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::on(fn($u) => $u === $userStub), 'EmergencyMode')
            ->andReturnNull();

        // Full previous URL; redirect()->back() will use this
        $previous = url('/user/123/dashboard');

        // Create a Request and make it the current app request
        $server = ['HTTP_REFERER' => $previous];
        $request = Request::create('/user/emergency/trigger', 'POST', ['command' => 'EmergencyMode'], [], [], $server);

        // Start a real session and attach the session *store* (not the manager)
        $this->startSession();
        $request->setLaravelSession(app('session.store'));

        // Bind this request into the container so redirect()->back() sees it
        $this->app->instance('request', $request);

        // Invoke the controller method directly
        $controller = new EmergencyController();
        $response = $controller->roomEmergency($request, $service);

        // Assert redirect to the referer
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($previous, $response->getTargetUrl());
    }
}
