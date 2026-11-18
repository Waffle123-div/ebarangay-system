<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CommunityConcern;
use App\Models\CommunityConcernRemark;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminCommunityReportsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $resident;
    private CommunityConcern $concern;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);

        $this->admin = User::create([
            'user_id'           => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Admin Officer Concerns',
            'first_name'        => 'Admin',
            'last_name'         => 'Officer',
            'email'             => 'admin_concerns_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password123'),
            'role'              => 'Admin',
            'status'            => 'active',
        ]);

        $this->resident = User::create([
            'user_id'           => 'RES-' . rand(10000, 99999),
            'name'              => 'Troy Catarata Suson',
            'first_name'        => 'Troy',
            'last_name'         => 'Suson',
            'email'             => 'troy_concern_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password123'),
            'security_pin'      => bcrypt('123456'),
            'role'              => 'Resident',
            'status'            => 'active',
        ]);

        $this->concern = CommunityConcern::create([
            'concern_id'     => 'CON-' . date('Y') . '-' . rand(10000, 99999),
            'user_id'        => $this->resident->id,
            'resident_name'  => $this->resident->name,
            'resident_email' => $this->resident->email,
            'contact_number' => '09123456789',
            'title'          => 'Damaged Drainage Cover in Sitio Sambag',
            'category'       => 'Infrastructure & Roads',
            'location'       => 'Sitio Sambag',
            'description'    => 'The concrete drainage cover along the main road is cracked and poses safety hazards.',
            'status'         => 'Under Review',
        ]);
    }

    public function test_admin_can_view_community_reports_with_clickable_selections(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.concerns'));

        $response->assertStatus(200);

        // Header & Page Titles
        $response->assertSee('Community Reports');
        $response->assertSee('Reports Management');
        $response->assertSee('Review, update, and respond to resident-submitted community reports and concerns.');

        // Concern details are rendered
        $response->assertSee($this->concern->concern_id);
        $response->assertSee($this->concern->resident_name);
        $response->assertSee('Damaged Drainage Cover in Sitio Sambag');
        $response->assertSee('Infrastructure &amp; Roads', false);
        $response->assertSee('Sitio Sambag');
        $response->assertSee('Under Review');

        // Selection is clickable without the view button
        $response->assertSee('concern-row-clickable');
        $response->assertSee('data-bs-target="#manageConcernModal' . $this->concern->id . '"', false);
        $response->assertDontSee('View / Update');
        $response->assertDontSee('View & Update Concern');

        // Details modal is present
        $response->assertSee('id="manageConcernModal' . $this->concern->id . '"', false);
        $response->assertSee('Detailed Description');
        $response->assertSee($this->concern->description);
    }

    public function test_admin_can_update_community_concern_status_and_remarks(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('admin.concerns.update', $this->concern->id), [
            'status'        => 'In Progress',
            'admin_remarks' => 'Barangay maintenance crew dispatched to replace the concrete cover.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('community_concerns', [
            'id'            => $this->concern->id,
            'status'        => 'In Progress',
            'admin_remarks' => 'Barangay maintenance crew dispatched to replace the concrete cover.',
        ]);

        $this->assertDatabaseHas('community_concern_remarks', [
            'community_concern_id' => $this->concern->id,
            'admin_id' => $this->admin->id,
            'remark' => 'Barangay maintenance crew dispatched to replace the concrete cover.',
            'attachment_path' => null,
        ]);
    }

    public function test_admin_feedback_image_flows_to_the_correct_resident_and_remains_accessible(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $image = UploadedFile::fake()->image('repair-progress.jpg');

        $this->actingAs($this->admin)
            ->patch(route('admin.concerns.update', $this->concern->id), [
                'status' => 'In Progress',
                'admin_remarks' => 'The drainage repair is underway.',
                'feedback_image' => $image,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $remark = CommunityConcernRemark::where('community_concern_id', $this->concern->id)->firstOrFail();
        $this->assertSame('The drainage repair is underway.', $remark->remark);
        $this->assertNotEmpty($remark->attachment_path);
        $this->assertStringStartsWith('community-concern-feedback/', $remark->attachment_path);
        Storage::disk('local')->assertExists($remark->attachment_path);
        Storage::disk('public')->assertMissing($remark->attachment_path);

        $adminAttachmentUrl = route('admin.concerns.remarks.attachment', [
            'concern' => $this->concern->id,
            'remark' => $remark->id,
        ]);
        $this->actingAs($this->admin)->get($adminAttachmentUrl)->assertOk();

        $residentAttachmentUrl = route('resident.concerns.remarks.attachment', [
            'concern' => $this->concern->id,
            'remark' => $remark->id,
        ]);
        $residentPage = $this->actingAs($this->resident)->get(route('resident.concerns'));
        $residentPage->assertOk()
            ->assertSee('The drainage repair is underway.')
            ->assertSee('In Progress')
            ->assertSee($this->admin->name)
            ->assertSee($remark->created_at->format('M d, Y h:i A'))
            ->assertSee($residentAttachmentUrl, false)
            ->assertDontSee('No community concerns have been submitted yet.');

        $this->actingAs($this->resident)->get($residentAttachmentUrl)->assertOk();
        $this->actingAs($this->resident)->get(route('resident.concerns'))->assertOk()->assertSee($residentAttachmentUrl, false);

        $otherResident = User::create([
            'user_id' => 'RES-' . rand(10000, 99999),
            'name' => 'Other Test Resident',
            'email' => 'other_concern_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'security_pin' => bcrypt('123456'),
            'role' => 'Resident',
            'status' => 'active',
        ]);
        $otherConcern = CommunityConcern::create([
            'concern_id' => 'CON-' . date('Y') . '-' . rand(10000, 99999),
            'user_id' => $otherResident->id,
            'resident_name' => $otherResident->name,
            'resident_email' => $otherResident->email,
            'title' => 'Separate concern',
            'category' => 'Infrastructure',
            'description' => 'A separate resident concern.',
            'location' => 'Other location',
            'status' => 'Pending',
        ]);
        $this->actingAs($this->admin)->patch(route('admin.concerns.update', $otherConcern->id), [
            'status' => 'Under Review',
            'admin_remarks' => 'A separate update.',
            'feedback_image' => UploadedFile::fake()->image('other.png'),
        ])->assertRedirect();

        $otherRemark = CommunityConcernRemark::where('community_concern_id', $otherConcern->id)->firstOrFail();
        $otherAttachmentUrl = route('resident.concerns.remarks.attachment', [
            'concern' => $otherConcern->id,
            'remark' => $otherRemark->id,
        ]);
        $this->actingAs($otherResident)->get($otherAttachmentUrl)->assertOk();
        $this->actingAs($this->resident)->get($otherAttachmentUrl)->assertForbidden();
        $this->actingAs($this->resident)->get(route('resident.concerns.remarks.attachment', [
            'concern' => $this->concern->id,
            'remark' => $otherRemark->id,
        ]))->assertNotFound();
        $this->actingAs($this->resident)->get($adminAttachmentUrl)->assertRedirect(route('resident.dashboard'));
    }

    public function test_resident_without_concerns_sees_the_new_empty_state_without_the_removed_button(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . rand(10000, 99999),
            'name' => 'New Resident',
            'email' => 'new_concern_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'security_pin' => bcrypt('123456'),
            'role' => 'Resident',
            'status' => 'active',
        ]);

        $response = $this->actingAs($resident)->get(route('resident.concerns'));
        $response->assertOk()
            ->assertSee('No community concerns have been submitted yet. Once you submit a concern, it will appear here along with its status and any updates from the barangay.')
            ->assertDontSee('Submit a Concern');
    }

    public function test_feedback_image_accepts_supported_extensions_and_rejects_invalid_files_and_oversize_uploads(): void
    {
        Storage::fake('local');

        foreach ([
            ['feedback.jpg', 'image/jpeg'],
            ['feedback.jpeg', 'image/jpeg'],
            ['feedback.png', 'image/png'],
            ['feedback.webp', 'image/webp'],
        ] as [$filename, $mimeType]) {
            $this->actingAs($this->admin)
                ->patch(route('admin.concerns.update', $this->concern->id), [
                    'status' => 'Under Review',
                    'admin_remarks' => 'Testing ' . $filename,
                    'feedback_image' => UploadedFile::fake()->create($filename, 20, $mimeType),
                ])
                ->assertRedirect()
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(4, CommunityConcernRemark::where('community_concern_id', $this->concern->id)->count());

        $this->actingAs($this->admin)
            ->patch(route('admin.concerns.update', $this->concern->id), [
                'status' => 'Under Review',
                'feedback_image' => UploadedFile::fake()->create('not-an-image.jpg', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('feedback_image');

        $this->actingAs($this->admin)
            ->patch(route('admin.concerns.update', $this->concern->id), [
                'status' => 'Under Review',
                'feedback_image' => UploadedFile::fake()->create('unsupported.gif', 10, 'image/gif'),
            ])
            ->assertSessionHasErrors('feedback_image');

        $this->actingAs($this->admin)
            ->patch(route('admin.concerns.update', $this->concern->id), [
                'status' => 'Under Review',
                'feedback_image' => UploadedFile::fake()->image('too-large.png')->size(5121),
            ])
            ->assertSessionHasErrors('feedback_image');

        $this->assertSame(4, CommunityConcernRemark::where('community_concern_id', $this->concern->id)->count());
        $this->assertCount(4, Storage::disk('local')->allFiles('community-concern-feedback'));
    }

    public function test_failed_feedback_record_save_removes_the_uploaded_image_and_rolls_back_status(): void
    {
        Storage::fake('local');
        CommunityConcernRemark::creating(function () {
            throw new \RuntimeException('Simulated feedback insert failure.');
        });

        $response = $this->actingAs($this->admin)->patch(route('admin.concerns.update', $this->concern->id), [
            'status' => 'Resolved',
            'admin_remarks' => 'This update should roll back.',
            'feedback_image' => UploadedFile::fake()->image('failed-save.png'),
        ]);

        $response->assertStatus(500);
        $this->assertDatabaseHas('community_concerns', [
            'id' => $this->concern->id,
            'status' => 'Under Review',
            'admin_remarks' => null,
        ]);
        $this->assertDatabaseMissing('community_concern_remarks', [
            'community_concern_id' => $this->concern->id,
        ]);
        $this->assertSame([], Storage::disk('local')->allFiles('community-concern-feedback'));
    }
}

