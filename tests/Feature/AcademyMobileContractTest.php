<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\AcademyReview;
use App\Models\Coach;
use App\Models\Field;
use App\Models\Group;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademyMobileContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_search_and_view_complete_academy_contract(): void
    {
        $player = Player::create([
            'first_name' => 'Parent',
            'last_name' => 'Account',
            'type' => 'parent',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);
        $academy = Academy::create([
            'name' => 'Al Awael Football Academy',
            'email' => 'academy@example.com',
            'city' => 'Riyadh',
            'country' => 'Saudi Arabia',
            'address' => 'King Fahd Road',
            'description' => 'Football training',
            'min_age' => 5,
            'max_age' => 18,
            'accepted_genders' => ['male', 'female'],
            'latitude' => 24.72,
            'longitude' => 46.68,
            'currency' => 'SAR',
        ]);
        $field = Field::create([
            'academy_id' => $academy->id,
            'name' => 'Main field',
            'type' => 'outdoor',
            'status' => 'available',
            'available_from' => '09:00',
            'available_to' => '22:00',
        ]);
        Group::create([
            'academy_id' => $academy->id,
            'field_id' => $field->id,
            'name' => 'Group 1',
            'training_category' => 'football',
            'start_time' => '17:00',
            'end_time' => '18:30',
            'days' => ['sunday', 'tuesday'],
            'monthly_price' => 500,
            'capacity' => 20,
        ]);
        AcademyReview::create([
            'academy_id' => $academy->id,
            'player_id' => $player->id,
            'rating' => 5,
            'comment' => 'Great',
        ]);
        $academy->services()->create(['title' => 'Medical services']);
        $academy->operatingHours()->create([
            'day' => 'sunday',
            'opens_at' => '17:00',
            'closes_at' => '21:00',
        ]);

        $headers = $this->playerHeaders($player);

        $this->getJson('/api/player/academies/search?min_price=400&max_price=600&training_days[]=sunday', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Al Awael Football Academy')
            ->assertJsonPath('data.0.price', 500)
            ->assertJsonPath('data.0.reviews_count', 1)
            ->assertJsonStructure(['data' => [['image_url', 'travel_time', 'is_favorite', 'reviews_avg_rating']], 'addtionalData']);

        $this->getJson('/api/player/academies/'.$academy->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.services_count', 1)
            ->assertJsonPath('data.groups_count', 1)
            ->assertJsonPath('data.currency', 'SAR')
            ->assertJsonStructure(['data' => ['gallery', 'distance', 'operating_hours', 'accepted_genders']]);
    }

    public function test_parent_children_are_scoped_and_booking_rejects_foreign_players(): void
    {
        $parent = Player::create([
            'first_name' => 'Parent',
            'last_name' => 'Account',
            'type' => 'parent',
        ]);
        $foreignPlayer = Player::create([
            'first_name' => 'Foreign',
            'last_name' => 'Player',
            'type' => 'player',
        ]);

        $headers = $this->playerHeaders($parent);
        $response = $this->postJson('/api/parent/children', [
            'first_name' => 'Zeyad',
            'last_name' => 'Parent',
            'birth_date' => now()->subYears(8)->toDateString(),
            'gender' => 'male',
        ], $headers)->assertOk()->assertJsonPath('data.age', 8);

        $childId = $response->json('data.id');
        $this->getJson('/api/parent/children', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.id', $childId);

        $academy = Academy::create(['name' => 'Academy']);
        $field = Field::create([
            'academy_id' => $academy->id,
            'name' => 'Field',
            'type' => 'outdoor',
            'status' => 'available',
            'available_from' => '09:00',
            'available_to' => '22:00',
        ]);
        $group = Group::create([
            'academy_id' => $academy->id,
            'field_id' => $field->id,
            'name' => 'Group',
            'training_category' => 'football',
            'start_time' => '17:00',
            'end_time' => '18:00',
            'days' => ['sunday'],
            'monthly_price' => 500,
            'capacity' => 10,
        ]);

        $this->postJson('/api/player/bookings', [
            'academy_id' => $academy->id,
            'group_id' => $group->id,
            'booking_type' => 'monthly',
            'duration_months' => 1,
            'player_ids' => [$foreignPlayer->id],
        ], $headers)->assertUnprocessable();
    }

    public function test_academy_tabs_reviews_and_favorites_return_mobile_fields(): void
    {
        $player = Player::create([
            'first_name' => 'Current',
            'last_name' => 'Player',
            'type' => 'player',
        ]);
        $academy = Academy::create(['name' => 'Academy', 'currency' => 'SAR']);
        $field = Field::create([
            'academy_id' => $academy->id,
            'name' => 'Field',
            'type' => 'outdoor',
            'status' => 'available',
            'available_from' => '09:00',
            'available_to' => '22:00',
        ]);
        $group = Group::create([
            'academy_id' => $academy->id,
            'field_id' => $field->id,
            'name' => 'Group',
            'training_category' => 'football',
            'start_time' => '17:00',
            'end_time' => '18:30',
            'days' => ['sunday'],
            'monthly_price' => 500,
            'capacity' => 10,
        ]);
        $coach = Coach::create([
            'academy_id' => $academy->id,
            'full_name' => 'Ahmed Mohamed',
        ]);
        $coach->groups()->attach($group);
        $academy->services()->create([
            'title' => 'Medical services',
            'images' => [],
        ]);

        $headers = $this->playerHeaders($player);

        $this->postJson('/api/player/academies/'.$academy->id.'/favorite', [], $headers)
            ->assertOk()
            ->assertJsonPath('data.academy_id', $academy->id)
            ->assertJsonPath('data.is_favorite', true);
        $this->getJson('/api/player/academies/favorites', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.is_favorite', true);
        $this->getJson('/api/player/academies/'.$academy->id.'/coaches', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Ahmed Mohamed');
        $this->getJson('/api/player/academies/'.$academy->id.'/services', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Medical services');
        $this->getJson('/api/player/academies/'.$academy->id.'/groups', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.available_seats', 10);
        $this->postJson('/api/player/academies/'.$academy->id.'/review', [
            'rating' => 5,
            'comment' => 'Great academy',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('data.user.name', 'Current Player')
            ->assertJsonPath('data.academy_rating_summary.reviews_count', 1);
        $this->getJson('/api/player/academies/'.$academy->id.'/reviews', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.user_name', 'Current Player')
            ->assertJsonPath('addtionalData.reviews_count', 1);
    }

    public function test_unauthenticated_api_request_never_redirects_to_login_route(): void
    {
        // Deliberately use get() rather than getJson() to reproduce clients
        // that omit Accept: application/json.
        $this->get('/api/academy/fields')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
                'error_code' => 'unauthenticated',
            ]);
    }

    private function playerHeaders(Player $player): array
    {
        return [
            'Authorization' => 'Bearer '.auth('player')->login($player),
            'Accept' => 'application/json',
        ];
    }
}
