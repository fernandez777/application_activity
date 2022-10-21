<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_store_new_user()
    {  
        $response = $this->post('api/users', $this->data_for_creating_user());
        
        $response->assertCreated();
        $this->assertCount(1, User::all());
    }

    public function test_a_user_can_login()
    {  
        $this->post('api/users', $this->data_for_creating_user());
        
        $response = $this->post('/api/users/login', [
            'email' => 'test@gmail.com',
            'password' => 'pass1234'  
        ]);

        $response->assertOk();
        $this->assertCount(1, User::all());
    }

    public function test_can_verify_a_user()
    {
        $response = $this->post('api/users', $this->data_for_creating_user());
        $response->assertCreated();
        $this->assertCount(1, User::all());
        $this->assertCount(1, UserVerification::all());
        
        $user = User::first();
        $userVer = UserVerification::first();

        $this->assertNull($user->first()->account_verified_at);
        
        $response = $this->post('api/users/validate/otp', [
            "mobile_number" => $user->mobile_number,
            "type" => $userVer->type,
            "verification_code" => $userVer->verification_code
        ]);

        $response->assertStatus(202);
        $this->assertNotNull($user->first()->account_verified_at);
    }

    public function test_login_using_wrong_credentials()
    {  
        $this->post('api/users', $this->data_for_creating_user());
        
        $this->assertCount(1, User::all());
        
        $response = $this->post('/api/users/login', [
            'email' => 'test@gmail.com',
            'password' => 'pass12345' //pass12345 is wrong password  
            ]);

        $response->assertUnauthorized();
    }

    public function test_a_password_confirmation_are_not_the_same()
    {  
        $response = $this->post('api/users', array_merge($this->data_for_creating_user(), [
            'password_confirmation' => 'pass123' //pass123 is wrong password_confirmation
        ])); 
        
        $response->assertSessionHasErrors(['password']);
        $this->assertCount(0, User::all());
    }

    public function test_a_user_data_can_be_updated()
    {  
        $this->post('api/users', $this->data_for_creating_user()); 
        
        $this->assertCount(1, User::all());

        $user = User::first();

        $response = $this->post('/api/users/login', [
            'email' => 'test@gmail.com',
            'password' => 'pass1234'  
        ]);

        $response->assertOk();

        $this->patch('/api/users/' . $user->id, [
            'name' => 'New Name',
            'email' => 'newemail@gmail.com'
        ]);

        $this->assertEquals('New Name', User::first()->name);
        $this->assertEquals('newemail@gmail.com', User::first()->email);
    }

    public function test_a_user_data_can_be_deleted()
    {  
        $this->post('api/users', $this->data_for_creating_user()); 
        
        $this->assertCount(1, User::all());

        $user = User::first();
        $user->delete();

        $this->assertCount(0, User::all());
    }

    private function data_for_creating_user(){
        return array(
            'name' => 'francis fernandez',
            'email' => 'test@gmail.com',
            'mobile_number' => '09566754387',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234'
        );
    }
}
