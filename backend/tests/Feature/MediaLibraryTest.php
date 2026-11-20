<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Media;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_images_are_validated_stored_privately_and_converted_to_responsive_variants(): void
    {
        $editor = $this->admin(Role::Editor);

        $this->actingAs($editor)->postJson('/admin/media', ['file' => UploadedFile::fake()->image('lab.jpg', 1000, 600), 'alt' => 'Robotics lab'])
            ->assertCreated()->assertJsonPath('alt', 'Robotics lab');

        $media = Media::query()->firstOrFail();
        $this->assertSame('ready', $media->status);
        $this->assertSame([400, 800], $media->availableWidths());
        Storage::disk('local')->assertExists($media->filename);
        Storage::disk('public')->assertExists($media->variantPath(800, 'webp'));
        Storage::disk('public')->assertExists($media->variantPath(400, 'webp'));
        Storage::disk('public')->assertMissing($media->filename);
    }

    public function test_non_images_and_disguised_files_are_rejected_and_alt_text_is_required(): void
    {
        $editor = $this->admin(Role::Editor);

        $this->actingAs($editor)->postJson('/admin/media', ['file' => UploadedFile::fake()->create('shell.php', 10, 'image/jpeg'), 'alt' => 'x'])->assertUnprocessable();
        $this->actingAs($editor)->postJson('/admin/media', ['file' => UploadedFile::fake()->createWithContent('logo.svg', '<svg onload="alert(1)"></svg>'), 'alt' => 'x'])->assertUnprocessable();
        $this->actingAs($editor)->postJson('/admin/media', ['file' => UploadedFile::fake()->image('a.jpg')])->assertJsonValidationErrors('alt');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_images_in_use_cannot_be_deleted(): void
    {
        $editor = $this->admin(Role::Editor);
        $this->actingAs($editor)->postJson('/admin/media', ['file' => UploadedFile::fake()->image('a.jpg', 500, 400), 'alt' => 'A'])->assertCreated();
        $media = Media::query()->firstOrFail();

        Program::create(['title' => 'P', 'slug' => 'p', 'level' => 'undergraduate', 'image_id' => $media->id]);

        $this->actingAs($editor)->getJson("/admin/media/{$media->id}")->assertJsonPath('usages.0.title', 'P');
        $this->actingAs($editor)->delete("/admin/media/{$media->id}")->assertSessionHas('error');
        $this->assertModelExists($media);

        Program::query()->first()->update(['image_id' => null]);
        $this->actingAs($editor)->delete("/admin/media/{$media->id}")->assertSessionHas('success');
        $this->assertModelMissing($media);
    }
}
