<?php

namespace SuitTests\Http\Controllers\Auth;

use Mockery;
use SuitTests\TestCase;
use Suitcoda\Http\Controllers\Auth\AuthController;
use Suitcoda\Model\User as Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Test Suitcoda\Http\Controllers\Auth\AuthController
 */
class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testVisitLoginPage()
    {
        $this->visit('login')
             ->see('Login');
        $this->assertInstanceOf('Illuminate\Http\Response', $this->response);
    }

    public function testPostLoginSuccess()
    {
        $input = ['username' => 'foo.bar', 'password' => 'asdfg', 'captcha' => 'asdf'];
        $userFaker = factory(Model::class)->create();
        $request = Mockery::mock('Suitcoda\Http\Requests\AuthRequest');
        $user = Mockery::mock(Model::class);
        
        $request->shouldReceive('only');
        $request->shouldReceive('has');
        $user->shouldReceive('login');
        \Auth::shouldReceive('attempt')->andReturn(true);
        \Auth::shouldReceive('user')->andReturn($user);

        $auth = new AuthController;
        $result = $auth->postLogin($request);

        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $result);
        $this->assertEquals($this->app['url']->to('/'), $result->headers->get('Location'));
    }

    public function testPostLoginFailed()
    {
        $input = ['username' => 'foo.bar', 'password' => 'asdfg', 'captcha' => 'asdf'];
        $userFaker = factory(Model::class)->create();
        $request = Mockery::mock('Suitcoda\Http\Requests\AuthRequest');
        $request->shouldReceive('only');
        $request->shouldReceive('has');
        \Auth::shouldReceive('attempt')->andReturn(false);

        $auth = new AuthController;
        $result = $auth->postLogin($request);
        $error = $this->app['session.store']->get('errors')->get('username');
        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $result);
        $this->assertEquals($this->app['url']->to('login'), $result->headers->get('Location'));
        $this->assertContains('These credentials do not match our records.', $error);
    }

    public function testGetSuccessLogout()
    {
        $userFaker = factory(Model::class)->create();
        $this->be($userFaker);
        $this->visit('logout')
             ->seePageIs('/login');
    }
}
