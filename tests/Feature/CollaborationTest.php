<?php

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createCollaborationTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $user->roles()->attach(
        Role::query()->where('slug', $roleSlug)->firstOrFail()
    );

    return $user;
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('operator can add and delete their own comment', function () {
    $operator = createCollaborationTestUser('operator');
    $company = Company::factory()->create([
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->post(route('comments.store', ['company', $company->id]), [
            'body' => 'Customer requested a revised delivery date.',
        ])
        ->assertRedirect();

    $comment = Comment::query()->firstOrFail();

    $this->get(route('companies.show', $company))
        ->assertOk()
        ->assertSee('Customer requested a revised delivery date.');

    $this->delete(route('comments.destroy', $comment))
        ->assertRedirect();

    $this->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
});

test('viewer cannot add comments', function () {
    $viewer = createCollaborationTestUser('viewer');
    $company = Company::factory()->create([
        'created_by' => $viewer->id,
    ]);

    $this->actingAs($viewer)
        ->post(route('comments.store', ['company', $company->id]), [
            'body' => 'This must not be saved.',
        ])
        ->assertForbidden();

    expect(Comment::query()->count())->toBe(0);
});

test('operator can upload and securely download an attachment', function () {
    Storage::fake('local');

    $operator = createCollaborationTestUser('operator');
    $company = Company::factory()->create([
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->post(route('attachments.store', ['company', $company->id]), [
            'file' => UploadedFile::fake()->create(
                'brief.pdf',
                120,
                'application/pdf'
            ),
        ])
        ->assertRedirect();

    $attachment = Attachment::query()->firstOrFail();

    Storage::disk('local')->assertExists($attachment->path);

    $this->get(route('attachments.download', $attachment))
        ->assertOk()
        ->assertDownload('brief.pdf');
});

test('attachment author can delete their uploaded file', function () {
    Storage::fake('local');

    $operator = createCollaborationTestUser('operator');
    $company = Company::factory()->create([
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->post(route('attachments.store', ['company', $company->id]), [
            'file' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
        ]);

    $attachment = Attachment::query()->firstOrFail();
    $path = $attachment->path;

    $this->delete(route('attachments.destroy', $attachment))
        ->assertRedirect();

    Storage::disk('local')->assertMissing($path);
    $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
});
