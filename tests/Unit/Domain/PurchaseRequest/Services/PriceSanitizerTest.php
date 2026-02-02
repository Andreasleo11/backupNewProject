<?php

namespace Tests\Unit\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\Services\PriceSanitizer;
use PHPUnit\Framework\TestCase;

class PriceSanitizerTest extends TestCase
{
    private PriceSanitizer $sanitizer;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new PriceSanitizer();
    }
    
    public function test_sanitizes_idr_rupiah_price()
    {
        $result = $this->sanitizer->sanitize('Rp 50,000');
        $this->assertEquals(50000.0, $result);
    }
    
    public function test_sanitizes_usd_price()
    {
        $result = $this->sanitizer->sanitize('$ 1,250.50');
        $this->assertEquals(1250.50, $result);
    }
    
    public function test_sanitizes_cny_yuan_price()
    {
        $result = $this->sanitizer->sanitize('¥ 8,500');
        $this->assertEquals(8500.0, $result);
    }
    
    public function test_sanitizes_price_with_dot_after_currency()
    {
        $result = $this->sanitizer->sanitize('Rp. 100,000');
        $this->assertEquals(100000.0, $result);
    }
    
    public function test_handles_plain_numeric_string()
    {
        $result = $this->sanitizer->sanitize('999.99');
        $this->assertEquals(999.99, $result);
    }
    
    public function test_handles_integer_input()
    {
        $result = $this->sanitizer->sanitize(1000);
        $this->assertEquals(1000.0, $result);
    }
    
    public function test_handles_float_input()
    {
        $result = $this->sanitizer->sanitize(1250.75);
        $this->assertEquals(1250.75, $result);
    }
    
    public function test_handles_null_input()
    {
        $result = $this->sanitizer->sanitize(null);
        $this->assertEquals(0.0, $result);
    }
    
    public function test_handles_empty_string()
    {
        $result = $this->sanitizer->sanitize('');
        $this->assertEquals(0.0, $result);
    }
    
    public function test_sanitizes_multiple_prices()
    {
        $prices = ['Rp 1,000', '$ 50.00', '¥ 200', null, ''];
        $result = $this->sanitizer->sanitizeMany($prices);
        
        $this->assertEquals([1000.0, 50.0, 200.0, 0.0, 0.0], $result);
    }
    
    public function test_handles_price_without_whitespace()
    {
        $result = $this->sanitizer->sanitize('Rp50000');
        $this->assertEquals(50000.0, $result);
    }
    
    public function test_handles_multiple_thousand_separators()
    {
        $result = $this->sanitizer->sanitize('Rp 1,250,500.50');
        $this->assertEquals(1250500.50, $result);
    }
}
