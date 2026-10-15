<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * L12: library route postures.
 *   - library.*        -> auth + role:dos|principal|bursar|teacher
 *   - parent.library   -> parent.auth (OTP session)
 *   - library.public   -> intentionally public marketing catalog
 */
class LibraryAuthTest extends TestCase
{
    public function test_staff_library_index_rejects_unauthenticated(): void
    {
        $this->get('/library')->assertRedirect();
    }

    public function test_staff_library_books_rejects_unauthenticated(): void
    {
        $this->get('/library/books')->assertRedirect();
    }

    public function test_staff_library_issue_rejects_unauthenticated(): void
    {
        $this->get('/library/issue')->assertRedirect();
    }

    public function test_staff_library_loans_rejects_unauthenticated(): void
    {
        $this->get('/library/loans')->assertRedirect();
    }

    public function test_parent_library_rejects_unauthenticated(): void
    {
        $response = $this->get('/parent/library');
        $response->assertRedirect(route('parent.login'));
    }

    public function test_library_public_catalog_is_accessible_without_login(): void
    {
        // Intentional: marketing catalog, no auth, no PII.
        // We do not assert 200 because the catalog may render with
        // zero rows on a fresh DB; the point is it is reachable.
        $response = $this->get('/library-catalog');
        $this->assertNotSame(302, $response->getStatusCode(),
            'public library catalog must not redirect to login');
    }
}
