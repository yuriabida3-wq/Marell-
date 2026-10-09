<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-18: /canteen/sell deprecated; enforcement lives only on
 * CanteenItemController@checkout.
 */
class Cp18CanteenCanonicalPathTest extends TestCase
{
    private function walletSrc(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/WalletController.php'));
    }

    private function canteenSrc(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/CanteenItemController.php'));
    }

    private function canteenViewSrc(): string
    {
        return (string) file_get_contents(base_path('resources/views/canteen/index.blade.php'));
    }

    public function test_canteen_sell_returns_410(): void
    {
        $src = $this->walletSrc();
        $this->assertStringContainsString("'This endpoint is no longer supported. Please use the item menu.'", $src);
        $this->assertStringContainsString('], 410);', $src);
    }

    public function test_canteen_sell_no_longer_deducts_wallet(): void
    {
        $src = $this->walletSrc();
        $start = strpos($src, 'public function canteenSell');
        $this->assertNotFalse($start);
        $end = strpos($src, 'public function canteenLogs', $start);
        $this->assertNotFalse($end);
        $body = substr($src, $start, $end - $start);

        $this->assertStringNotContainsString("update(['wallet_balance'", $body);
        $this->assertStringNotContainsString('DB::transaction', $body);
        $this->assertStringNotContainsString('WalletTransaction::create', $body);
    }

    public function test_canteen_sell_logs_without_pii(): void
    {
        $src = $this->walletSrc();
        $start = strpos($src, 'public function canteenSell');
        $end = strpos($src, 'public function canteenLogs', $start);
        $body = substr($src, $start, $end - $start);

        $this->assertStringContainsString("'Deprecated /canteen/sell called'", $body);
        $this->assertStringNotContainsString("'phone'", $body);
        $this->assertStringNotContainsString("'student_id'", $body);
        $this->assertStringNotContainsString("'items' =>", $body);
    }

    public function test_checkout_still_enforces_all_rules(): void
    {
        $src = $this->canteenSrc();
        $start = strpos($src, 'public function checkout');
        $this->assertNotFalse($start);
        $body = substr($src, $start, 6000);

        $this->assertStringContainsString('lockForUpdate', $body);
        $this->assertStringContainsString('blocked_items', $body);
        $this->assertStringContainsString('blocked_categories', $body);
        $this->assertStringContainsString('$item->stock > 0 && $item->stock < $qty', $body);
        $this->assertStringContainsString('dailyLimit', $body);
        $this->assertStringContainsString('wallet_balance', $body);
    }

    public function test_view_no_longer_calls_sell_route(): void
    {
        $src = $this->canteenViewSrc();
        $this->assertStringNotContainsString("route(\"canteen.sell\")", $src);
        $this->assertStringNotContainsString("route('canteen.sell')", $src);
    }

    public function test_view_posts_to_checkout_with_item_ids(): void
    {
        $src = $this->canteenViewSrc();
        $this->assertStringContainsString('canteen.item-checkout', $src);
        $this->assertStringContainsString('item_ids', $src);
        $this->assertStringContainsString('quantities', $src);
    }
}