<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExternalStudentLoginPageTest extends TestCase
{
    public function test_student_login_page_is_not_available(): void
    {
        $response = $this->get('/student-login');

        $response->assertNotFound();
    }

    public function test_dashboard_page_is_not_available(): void
    {
        $response = $this->get('/dashboard');

        $response->assertNotFound();
    }
}
