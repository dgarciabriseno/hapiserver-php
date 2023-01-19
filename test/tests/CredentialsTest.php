<?php declare(strict_types=1);

use App\Database\Credentials;
use App\Database\PDODatabase;
use PHPUnit\Framework\TestCase;

final class CredentialsTest extends TestCase {
    public function testGetHost() {
        $host = Credentials::GetHost();
        $this->assertIsString($host);
        $this->assertNotEmpty($host);
    }

    public function testGetDatabaseName() {
        $name = Credentials::GetDatabaseName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function testGetDatabaseUser() {
        $user = Credentials::GetDatabaseUser();
        $this->assertIsString($user);
        $this->assertNotEmpty($user);
    }

    public function testGetDatabasePassword() {
        $pass = Credentials::GetDatabasePassword();
        $this->assertIsString($pass);
        $this->assertNotEmpty($pass);
    }
}