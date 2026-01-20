<?php

class Formuel_Redirect_Exception extends Exception
{
    private string $location;

    public function __construct(string $location)
    {
        parent::__construct('Redirect occurred.');
        $this->location = $location;
    }

    public function location(): string
    {
        return $this->location;
    }
}

class Formuel_Shortcode_Test extends WP_UnitTestCase
{
    private array $post_backup = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->post_backup = $_POST;
        $_POST = [];

        Formuel_DB::activate();
        $this->truncate_entries();
    }

    public function tearDown(): void
    {
        $_POST = $this->post_backup;

        parent::tearDown();
    }

    public function test_handle_submission_inserts_row_and_redirects_success(): void
    {
        $this->prepare_valid_post();
        add_filter('wp_redirect', [$this, 'capture_redirect']);

        try {
            Formuel_Shortcode::handle_submission();
            $this->fail('Expected redirect exception.');
        } catch (Formuel_Redirect_Exception $exception) {
            $this->assertStringContainsString('formuel_status=success', $exception->location());
        } finally {
            remove_filter('wp_redirect', [$this, 'capture_redirect']);
        }

        $this->assertSame(1, $this->entry_count());
    }

    public function test_handle_submission_with_missing_fields_redirects_error(): void
    {
        $this->prepare_valid_post();
        $_POST['formuel_message'] = '';
        add_filter('wp_redirect', [$this, 'capture_redirect']);

        try {
            Formuel_Shortcode::handle_submission();
            $this->fail('Expected redirect exception.');
        } catch (Formuel_Redirect_Exception $exception) {
            $this->assertStringContainsString('formuel_status=error', $exception->location());
        } finally {
            remove_filter('wp_redirect', [$this, 'capture_redirect']);
        }

        $this->assertSame(0, $this->entry_count());
    }

    public function test_handle_submission_with_invalid_nonce_dies(): void
    {
        $this->prepare_valid_post();
        $_POST['formuel_nonce'] = 'invalid';

        $this->expectException(WPDieException::class);
        Formuel_Shortcode::handle_submission();
    }

    public function capture_redirect(string $location): string
    {
        throw new Formuel_Redirect_Exception($location);
    }

    private function prepare_valid_post(): void
    {
        $_POST = [
            'formuel_submit' => '1',
            'formuel_nonce' => wp_create_nonce(Formuel_Shortcode::NONCE_ACTION),
            'formuel_name' => 'Ada Lovelace',
            'formuel_email' => 'ada@example.com',
            'formuel_message' => 'Hello world.',
        ];
    }

    private function entry_count(): int
    {
        global $wpdb;

        $table = Formuel_DB::table_name();
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    private function truncate_entries(): void
    {
        global $wpdb;

        $table = Formuel_DB::table_name();
        $wpdb->query("DELETE FROM {$table}");
    }
}
