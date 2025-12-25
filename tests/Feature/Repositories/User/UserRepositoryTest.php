<?php

namespace Tests\Repositories\User;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\User\UserRepositoryInterface;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
    }

    public function test_it_can_create_a_user(): void
    {
        $user = $this->userRepository->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_it_can_find_user_by_id(): void
    {
        $user = User::factory()->create();

        $foundUser = $this->userRepository->findById($user->id);

        $this->assertNotNull($foundUser);
        $this->assertSame($user->id, $foundUser->id);
    }

    public function test_it_returns_null_when_user_not_found_by_id(): void
    {
        $foundUser = $this->userRepository->findById(999);

        $this->assertNull($foundUser);
    }

    public function test_it_can_find_user_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'unique@example.com',
        ]);

        $foundUser = $this->userRepository->findByEmail('unique@example.com');

        $this->assertNotNull($foundUser);
        $this->assertSame($user->id, $foundUser->id);
    }

    public function test_it_can_update_a_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);

        $updated = $this->userRepository->update($user, [
            'name' => 'New Name',
        ]);

        $this->assertTrue($updated);
        $this->assertSame('New Name', $user->fresh()->name);
    }

    public function test_it_can_delete_a_user(): void
    {
        $user = User::factory()->create();

        $deleted = $this->userRepository->delete($user);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_it_can_get_all_users(): void
    {
        User::factory()->count(3)->create();

        $users = $this->userRepository->all();

        $this->assertCount(3, $users);
    }
}
