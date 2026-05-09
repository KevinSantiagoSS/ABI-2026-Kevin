<?php

namespace Tests\Feature;

use App\Http\Requests\AcademicPeriodRequest;
use App\Models\AcademicPeriod;
use App\Models\ResearchStaff\ResearchStaffAcademicPeriod;
use App\Models\ResearchStaff\ResearchStaffUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AcademicPeriodValidationTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.mysql_research_staff', config('database.connections.sqlite'));

        ResearchStaffAcademicPeriod::query()->withTrashed()->forceDelete();
        ResearchStaffUser::query()->delete();
    }

    public function test_create_rejects_periods_before_the_existing_sequence(): void
    {
        $user = $this->createAuthUser();
        $existingCode = $this->uniqueCode('2026-1');
        $newCode = $this->uniqueCode('2025-2');

        ResearchStaffAcademicPeriod::query()->create([
            'code' => $existingCode,
            'name' => 'Periodo 2026-1',
            'start_date' => '2026-01-15',
            'end_date' => '2026-06-15',
            'status' => 'draft',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->post(route('academic-periods.store'), [
            'code' => $newCode,
            'name' => 'Periodo 2025-2',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-01',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('academic_periods', 1);
    }

    public function test_create_rejects_overlapping_periods(): void
    {
        $user = $this->createAuthUser();
        $existingCode = $this->uniqueCode('2026-1');
        $newCode = $this->uniqueCode('2026-X');

        ResearchStaffAcademicPeriod::query()->create([
            'code' => $existingCode,
            'name' => 'Periodo 2026-1',
            'start_date' => '2026-01-15',
            'end_date' => '2026-06-15',
            'status' => 'draft',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->post(route('academic-periods.store'), [
            'code' => $newCode,
            'name' => 'Periodo traslapado',
            'start_date' => '2026-05-01',
            'end_date' => '2026-07-01',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('academic_periods', 1);
    }

    public function test_update_rejects_invading_the_next_period(): void
    {
        $user = $this->createAuthUser();

        $first = ResearchStaffAcademicPeriod::query()->create([
            'code' => $this->uniqueCode('2026-1'),
            'name' => 'Periodo 2026-1',
            'start_date' => '2026-01-15',
            'end_date' => '2026-06-15',
            'status' => 'draft',
            'is_active' => false,
        ]);

        $second = ResearchStaffAcademicPeriod::query()->create([
            'code' => $this->uniqueCode('2026-2'),
            'name' => 'Periodo 2026-2',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-01',
            'status' => 'draft',
            'is_active' => false,
        ]);

        $third = ResearchStaffAcademicPeriod::query()->create([
            'code' => $this->uniqueCode('2027-1'),
            'name' => 'Periodo 2027-1',
            'start_date' => '2027-01-15',
            'end_date' => '2027-06-15',
            'status' => 'draft',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->put(route('academic-periods.update', $second), [
            'code' => $second->code,
            'name' => $second->name,
            'start_date' => $second->start_date->format('Y-m-d'),
            'end_date' => $third->start_date->format('Y-m-d'),
            'status' => $second->status,
        ]);

        $response->assertSessionHasErrors('end_date');
        $this->assertSame('2026-12-01', $second->fresh()->end_date->format('Y-m-d'));
        $this->assertNotNull($first);
    }

    public function test_future_period_data_passes_backend_validation_after_the_last_registered_one(): void
    {
        $existingCode = $this->uniqueCode('2026-1');
        $newCode = $this->uniqueCode('2026-2');

        ResearchStaffAcademicPeriod::query()->create([
            'code' => $existingCode,
            'name' => 'Periodo 2026-1',
            'start_date' => '2026-01-15',
            'end_date' => '2026-06-15',
            'status' => 'draft',
            'is_active' => false,
        ]);

        $request = AcademicPeriodRequest::create('/academic-periods', 'POST', [
            'code' => $newCode,
            'name' => 'Periodo 2026-2',
            'start_date' => '2026-06-16',
            'end_date' => '2026-12-15',
            'status' => 'draft',
        ]);

        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());
        $request->withValidator($validator);

        $this->assertTrue($validator->passes());
    }

    public function test_create_rejects_active_status_when_period_has_not_started_yet(): void
    {
        Carbon::setTestNow('2026-04-22 10:00:00');

        $user = $this->createAuthUser();

        $response = $this->actingAs($user)->post(route('academic-periods.store'), [
            'code' => $this->uniqueCode('2026-2'),
            'name' => 'Periodo 2026-2',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-01',
            'status' => AcademicPeriod::STATUS_ACTIVE,
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseCount('academic_periods', 0);
    }

    public function test_create_allows_active_status_when_current_date_is_within_period_range(): void
    {
        Carbon::setTestNow('2026-04-22 10:00:00');

        $user = $this->createAuthUser();

        $response = $this->actingAs($user)->post(route('academic-periods.store'), [
            'code' => $this->uniqueCode('2026-1'),
            'name' => 'Periodo 2026-1',
            'start_date' => '2026-01-15',
            'end_date' => '2026-06-30',
            'status' => AcademicPeriod::STATUS_ACTIVE,
        ]);

        $response->assertRedirect(route('academic-periods.index'));

        $this->assertDatabaseHas('academic_periods', [
            'code' => ResearchStaffAcademicPeriod::query()->firstOrFail()->code,
            'status' => AcademicPeriod::STATUS_ACTIVE,
            'is_active' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function createAuthUser(): ResearchStaffUser
    {
        $suffix = ++self::$sequence;

        return ResearchStaffUser::query()->create([
            'email' => "academic-periods-{$suffix}@example.com",
            'password' => Hash::make('password'),
            'role' => 'research_staff',
            'state' => 1,
        ]);
    }

    private function uniqueCode(string $prefix): string
    {
        return $prefix . '-' . (++self::$sequence);
    }
}
