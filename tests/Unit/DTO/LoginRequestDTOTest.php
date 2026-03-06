<?php

namespace Tests\Unit\DTOs;

use Tests\TestCase;
use App\DTOs\LoginRequestDTO;

class LoginRequestDTOTest extends TestCase
{
    public function test_can_create_dto_from_array()
    {
        $data = ['email' => 'test@test.com', 'password' => '123456'];
        $dto = LoginRequestDTO::fromRequest($data);

        $this->assertEquals('test@test.com', $dto->email);
        $this->assertEquals('123456', $dto->password);
    }

    public function test_dto_is_readonly()
    {
        $dto = new LoginRequestDTO('test@test.com', '123456');
        
        $this->expectException(\Error::class);
        $dto->email = 'otro@test.com';
    }
}