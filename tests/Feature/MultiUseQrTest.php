<?php

namespace Tests\Feature;

use App\Models\QrTransfer;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiUseQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_code_can_be_used_multiple_times_until_balance_is_exhausted()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $sender->wallet()->create(['balance' => 200]);
        $receiver->wallet()->create(['balance' => 0]);

        // 1. Create a QR for 100
        $this->actingAs($sender);
        $response = $this->postJson('/api/wallet/qr/create', ['amount' => 100]);
        $response->assertStatus(200);
        $token = $response->json('data.token');

        $transfer = QrTransfer::where('token', $token)->first();
        $this->assertEquals(100, $transfer->remaining_amount);
        $this->assertEquals('pending', $transfer->status);

        // 2. Receiver (Driver) deducts 5
        $this->actingAs($receiver);
        $response = $this->postJson('/api/wallet/qr/process', [
            'token' => $token,
            'amount' => 5
        ]);

        $response->assertStatus(200);
        $this->assertEquals(95, $response->json('data.remaining'));
        
        $transfer->refresh();
        $this->assertEquals(95, $transfer->remaining_amount);
        $this->assertEquals('pending', $transfer->status);
        $this->assertEquals(5, $receiver->wallet->fresh()->balance);
        $this->assertEquals(195, $sender->wallet->fresh()->balance);

        // 3. Receiver deducts another 10
        $response = $this->postJson('/api/wallet/qr/process', [
            'token' => $token,
            'amount' => 10
        ]);

        $response->assertStatus(200);
        $this->assertEquals(85, $response->json('data.remaining'));
        
        $transfer->refresh();
        $this->assertEquals(85, $transfer->remaining_amount);
        $this->assertEquals('pending', $transfer->status);

        // 4. Receiver deducts the rest (85)
        $response = $this->postJson('/api/wallet/qr/process', [
            'token' => $token,
            'amount' => 85
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('data.remaining'));
        $this->assertEquals('completed', $response->json('data.status'));
        
        $transfer->refresh();
        $this->assertEquals(0, $transfer->remaining_amount);
        $this->assertEquals('completed', $transfer->status);
        $this->assertEquals(100, $receiver->wallet->fresh()->balance);
        $this->assertEquals(100, $sender->wallet->fresh()->balance);

        // 5. Try to use it again
        $response = $this->postJson('/api/wallet/qr/process', [
            'token' => $token,
            'amount' => 1
        ]);

        $response->assertJson(['success' => false, 'message' => 'Invalid or already used QR code.']);
    }

    public function test_cannot_deduct_more_than_remaining_amount()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $sender->wallet()->create(['balance' => 200]);

        $this->actingAs($sender);
        $response = $this->postJson('/api/wallet/qr/create', ['amount' => 100]);
        $token = $response->json('data.token');

        $this->actingAs($receiver);
        $response = $this->postJson('/api/wallet/qr/process', [
            'token' => $token,
            'amount' => 100.01
        ]);

        $response->assertJson(['success' => false, 'message' => 'Requested amount exceeds QR balance.']);
    }
}
