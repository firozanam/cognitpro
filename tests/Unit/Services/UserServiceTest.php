<?php

namespace Tests\Unit\Services;

use App\Modules\User\Contracts\UserRepositoryInterface;
use App\Modules\User\Models\User;
use App\Modules\User\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;
    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app(UserService::class);
        $this->userRepository = app(UserRepositoryInterface::class);
    }

    public function test_update_profile_resets_email_verification_when_email_changes(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);

        // Act
        $updatedUser = $this->userService->updateProfile($user, [
            'email' => 'new@example.com',
        ]);

        // Assert
        $this->assertEquals('new@example.com', $updatedUser->email);
        $this->assertNull($updatedUser->email_verified_at);
    }

    public function test_update_profile_keeps_email_verification_when_email_unchanged(): void
    {
        // Arrange
        $verifiedAt = now();
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'email_verified_at' => $verifiedAt,
        ]);

        // Act
        $updatedUser = $this->userService->updateProfile($user, [
            'name' => 'New Name',
        ]);

        // Assert
        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertNotNull($updatedUser->email_verified_at);
    }

    public function test_delete_account_removes_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $userId = $user->id;

        // Act
        $result = $this->userService->deleteAccount($user);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_is_email_available_returns_true_for_new_email(): void
    {
        // Act
        $isAvailable = $this->userService->isEmailAvailable('unique@example.com');

        // Assert
        $this->assertTrue($isAvailable);
    }

    public function test_is_email_available_returns_false_for_existing_email(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);

        // Act
        $isAvailable = $this->userService->isEmailAvailable('existing@example.com');

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_is_email_available_excludes_current_user(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'user@example.com']);

        // Act
        $isAvailable = $this->userService->isEmailAvailable(
            'user@example.com',
            $user->id
        );

        // Assert
        $this->assertTrue($isAvailable);
    }
}
