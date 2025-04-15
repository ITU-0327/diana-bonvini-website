<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ArtworksController Test Case
 *
 * @uses \App\Controller\ArtworksController
 */
class ArtworksControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Artworks',
        'app.ContentBlocks',
    ];

    /**
     * Test Case: Index action returns only non-deleted artworks.
     *
     * @return void
     */
    public function testIndexReturnsOnlyNonDeletedArtworks(): void
    {
        $this->get('/artworks');
        $this->assertResponseOk();

        $artworks = $this->viewVariable('artworks');
        $this->assertNotEmpty($artworks, 'The artworks view variable should not be empty.');

        foreach ($artworks as $artwork) {
            $this->assertSame(0, $artwork->is_deleted, "Artwork {$artwork->artwork_id} should not be marked as deleted.");
        }
    }

    public function testIndexDisplayingValidArtworks(): void
    {
        $this->get('/artworks');
        $this->assertResponseOk();

        $this->assertResponseContains('Valid Art');
        $this->assertResponseContains('$100');
        $this->assertResponseContains('Sunset Over the Ocean');
        $this->assertResponseContains('$250');
    }

    public function testIndexDisplayingSoldArtworks(): void
    {
        $this->get('/artworks');
        $this->assertResponseOk();

        $this->assertResponseContains('Sold Art');
        $this->assertResponseContains('$200');
    }

    /**
     * Test Case: Index action does not display deleted artworks.
     *
     * @return void
     */
    public function testIndexNotDisplayingDeletedArtworks(): void
    {
        $this->get('/artworks');
        $this->assertResponseOk();

        $this->assertResponseNotContains('Deleted Art');
        $this->assertResponseContains('Valid Art');
    }

    /**
     * Test Case: View action displays the correct artwork details for a valid ID.
     *
     * @return void
     */
    public function testViewDisplaysCorrectArtworkForValidId(): void
    {
        $artworkId = '5492e85e-f1b2-41f5-85cb-bfbe115b69ea';

        // Test the view action using the valid artwork id.
        $this->get("/artworks/view/{$artworkId}");
        $this->assertResponseOk();

        $this->assertResponseContains('Sunset Over the Ocean');
        $this->assertResponseContains('A vibrant painting capturing the dynamic colors of a sunset over the vast ocean, evoking a sense of peace and wonder.');
        $this->assertResponseContains('$250');
    }

    /**
     * Test Case: View action throws an exception for an invalid artwork ID.
     *
     * @return void
     */
    public function testViewThrowsRecordNotFoundForInvalidArtworkId(): void
    {
        $this->get('/artworks/view/invalid-artwork-id');
        $this->assertResponseCode(404);
    }

    /**
     * Test Case: View action throws an exception when no artwork ID is provided.
     *
     * @return void
     */
    public function testViewThrowsRecordNotFoundForMissingArtworkId(): void
    {
        $this->get('/artworks/view');
        $this->assertResponseCode(500);
    }
}
