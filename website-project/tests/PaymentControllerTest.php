<?php
use PHPUnit\Framework\TestCase;
use App\Controllers\PaymentController;
use App\Models\Payment as PaymentModel;
use App\Models\Reservation;
use App\Models\Listing;
use App\Models\User;
use App\Config\DatabaseConnection;

require_once 'bootstrap.php';

// Create a child class of PaymentController to allow mocking private methods
class TestablePaymentController extends PaymentController {
    public $mockedResponse;

    // Override the API call to return a mocked response
    protected function _queryStkStatus($accessToken, $checkoutRequestID) {
        return $this->mockedResponse;
    }
    
    // Override to prevent actual token generation during tests
    protected function getMpesaAccessToken($consumerKey, $consumerSecret) {
        return 'mock_access_token';
    }
}

final class PaymentControllerTest extends TestCase {
    private static $pdo;
    private $paymentId;
    private $reservationId;
    private $listingId;
    private $userId;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = DatabaseConnection::getInstance()->getConnection();
    }

    protected function setUp(): void
    {
        // Create dummy records for a realistic test case
        $this->userId = User::create(['name' => 'testuser', 'password' => 'password', 'email' => 'test@user.com'])->id;
        $this->listingId = Listing::create([
            'title' => 'Test Query Listing',
            'city' => 'TestCity',
            'neighborhood_id' => 1,
            'htype_id' => 2, // Studio
            'style_id' => 1, // Modern
            'rent_amount' => 500,
            'deposit_amount' => 500,
            'owner_id' => $this->userId,
            'status' => 'available',
        ])->id;
        $this->reservationId = Reservation::create([
            'listing_id' => $this->listingId,
            'user_id' => $this->userId,
            'status' => 'pending'
        ])->id;
        $this->paymentId = PaymentModel::create([
            'reservation_id' => $this->reservationId,
            'amount' => 500,
            'payment_method' => 'mpesa',
            'status' => 'pending',
            'transaction_id' => 'ws_CO_0101202412345678' // A mock CheckoutRequestID
        ])->id;
    }

    protected function tearDown(): void
    {
        // Clean up the database after each test, checking if IDs exist
        if ($this->paymentId) {
            self::$pdo->exec("DELETE FROM payments WHERE id = {$this->paymentId}");
        }
        if ($this->reservationId) {
            self::$pdo->exec("DELETE FROM reservations WHERE id = {$this->reservationId}");
        }
        if ($this->listingId) {
            self::$pdo->exec("DELETE FROM listings WHERE id = {$this->listingId}");
        }
        if ($this->userId) {
            self::$pdo->exec("DELETE FROM users WHERE id = {$this->userId}");
        }
    }

    public function testQueryMpesaTransactionStatusSuccess() {
        // Arrange
        $controller = new TestablePaymentController();
        // Mock a successful response from the M-Pesa API
        $controller->mockedResponse = json_decode(json_encode([
            'ResponseCode' => '0',
            'ResponseDescription' => 'The service request is processed successfully.',
            'MerchantRequestID' => 'some_merchant_id',
            'CheckoutRequestID' => 'ws_CO_0101202412345678',
            'ResultCode' => '0',
            'ResultDesc' => 'The service request is processed successfully.'
        ]));
        
        // Act
        // Capture output to check JSON response
        ob_start();
        $controller->queryMpesaTransactionStatus(['payment_id' => $this->paymentId]);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        // Assert
        $updatedPayment = PaymentModel::find($this->paymentId);
        $updatedReservation = Reservation::find($this->reservationId);

        $this->assertEquals('completed', $updatedPayment['status']);
        $this->assertEquals('CONFIRMED', $updatedReservation['status']);
        $this->assertArrayHasKey('status', $response); // This line is correct
        $this->assertEquals('completed', $response['status']);
    }

    public function testQueryMpesaTransactionStatusFailed() {
        // Arrange
        $controller = new TestablePaymentController(); // This line is correct
        // Mock a failed response
        $controller->mockedResponse = json_decode(json_encode([
            'ResponseCode' => '0',
            'ResponseDescription' => 'The service request is processed successfully.',
            'MerchantRequestID' => 'some_merchant_id',
            'CheckoutRequestID' => 'ws_CO_0101202412345678',
            'ResultCode' => '1032', // Customer cancelled the request
            'ResultDesc' => 'Request cancelled by user.'
        ]));
        
        // Act
        ob_start();
        $controller->queryMpesaTransactionStatus(['payment_id' => $this->paymentId]);
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        // Assert
        $updatedPayment = PaymentModel::find($this->paymentId);
        $updatedReservation = Reservation::find($this->reservationId);

        $this->assertEquals('failed', $updatedPayment['status']);
        $this->assertEquals('FAILED', $updatedReservation['status']);
        $this->assertArrayHasKey('status', $response); // This line is correct
        $this->assertEquals('failed', $response['status']);
    }
}
