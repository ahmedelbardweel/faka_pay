<?php

namespace Tests\Feature;

use App\Models\QrTransfer;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncPartialQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_transfers_supports_partial_deductions()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $sender->wallet()->create(['balance' => 200]);
        $receiver->wallet()->create(['balance' => 0]);

        // 1. Create a QR for 100
        $this->actingAs($sender);
        $response = $this->postJson('/api/wallet/qr/create', ['amount' => 100]);
        $token = $response->json('data.token');

        // 2. Sync with partial amount
        $this->actingAs($receiver);
        $response = $this->postJson('/api/wallet/qr/sync', [
            'tokens' => [
                ['token' => $token, 'amount' => 30]
            ]
        ]);

        $response->assertStatus(200);
        $this->assertEquals(30, $receiver->wallet->fresh()->balance);
        
        $transfer = QrTransfer::where('token', $token)->first();
        $this->assertEquals(70, $transfer->remaining_amount);
        $this->assertEquals('pending', $transfer->status);

        // 3. Sync another partial
        $response = $this->postJson('/api/wallet/qr/sync', [
            'tokens' => [
                ['token' => $token, 'amount' => 20]
            ]
        ]);

        $this->assertEquals(50, $receiver->wallet->fresh()->balance);
        $this->assertEquals(50, $transfer->fresh()->remaining_amount);

        // 4. Sync the rest
        $response = $this->postJson('/api/wallet/qr/sync', [
            'tokens' => [
                ['token' => $token, 'amount' => 50]
            ]
        ]);

        $this->assertEquals(100, $receiver->wallet->fresh()->balance);
        $this->assertEquals(0, $transfer->fresh()->remaining_amount);
        $this->assertEquals('completed', $transfer->fresh()->status);
    }

    public function test_check_status_includes_remaining_amount()
    {
        $sender = User::factory()->create();
        $sender->wallet()->create(['balance' => 100]);

        $this->actingAs($sender);
        $response = $this->postJson('/api/wallet/qr/create', ['amount' => 100]);
        $token = $response->json('data.token');

        $response = $this->getJson("/api/wallet/qr/{$token}/status");
        $response->assertStatus(200);
        $response->assertJsonFragment(['remaining_amount' => "100.00"]);

        // Partially use it
        $receiver = User::factory()->create();
        $receiver->wallet()->create(['balance' => 0]);
        $this->actingAs($receiver);
        $this->postJson('/api/wallet/qr/process', ['token' => $token, 'amount' => 40]);

        $response = $this->getJson("/api/wallet/qr/{$token}/status");
        $response->assertJsonFragment(['remaining_amount' => "60.00"]);
    }
}
