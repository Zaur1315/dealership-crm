<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadActivityType;
use App\Enums\LeadPipelineStage;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Dealership;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Services\Leads\LeadActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeadActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_lead_created_activity(): void
    {
        $lead = $this->createLead();
        $user = $this->createUser();

        app(LeadActivityService::class)->leadCreated($lead, $user);

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'dealership_id' => $lead->dealership_id,
            'user_id' => $user->id,
            'type' => LeadActivityType::LEAD_CREATED->value,
            'title' => 'Lead created',
        ]);
    }

    public function test_it_records_stage_changed_activity(): void
    {
        $lead = $this->createLead();
        $user = $this->createUser();

        app(LeadActivityService::class)->stageChanged(
            lead: $lead,
            oldStage: LeadPipelineStage::NEW->value,
            newStage: LeadPipelineStage::WON->value,
            user: $user,
        );

        $activity = $lead->activities()->first();

        $this->assertNotNull($activity);
        $this->assertSame(LeadActivityType::STAGE_CHANGED, $activity->type);
        $this->assertSame(['pipeline_stage' => LeadPipelineStage::NEW->value], $activity->old_values);
        $this->assertSame(['pipeline_stage' => LeadPipelineStage::WON->value], $activity->new_values);
    }

    public function test_it_records_task_created_activity(): void
    {
        $lead = $this->createLead();
        $user = $this->createUser();

        $task = Task::query()->create([
            'dealership_id' => $lead->dealership_id,
            'lead_id' => $lead->id,
            'created_by_user_id' => $user->id,
            'created_by_name' => $user->full_name,
            'title' => 'Call customer',
            'description' => 'Call customer about the deal.',
            'type' => TaskType::PHONE_CALL->value,
            'status' => TaskStatus::ACTIVE->value,
            'due_at' => now()->addHour(),
        ]);

        app(LeadActivityService::class)->taskCreated($task, $user);

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'dealership_id' => $lead->dealership_id,
            'user_id' => $user->id,
            'type' => LeadActivityType::TASK_CREATED->value,
            'title' => 'Task created',
            'description' => 'Call customer',
        ]);
    }

    public function test_it_does_not_record_task_activity_without_lead(): void
    {
        $dealership = $this->createDealership();

        $task = Task::query()->create([
            'dealership_id' => $dealership->id,
            'lead_id' => null,
            'title' => 'Manual task',
            'description' => 'No lead attached.',
            'type' => TaskType::GENERAL->value,
            'status' => TaskStatus::ACTIVE->value,
            'due_at' => now()->addHour(),
        ]);

        $activity = app(LeadActivityService::class)->taskCreated($task);

        $this->assertNull($activity);
        $this->assertDatabaseCount('lead_activities', 0);
    }

    private function createDealership(): Dealership
    {
        return Dealership::query()->create([
            'name' => 'Test Dealership',
            'email' => 'dealer@example.com',
            'is_active' => true,
        ]);
    }

    private function createLead(): Lead
    {
        $dealership = $this->createDealership();

        return Lead::query()->create([
            'dealership_id' => $dealership->id,
            'full_name' => 'John Customer',
            'phone_number' => '+15555550123',
            'email' => 'customer@example.com',
            'pipeline_stage' => LeadPipelineStage::NEW->value,
        ]);
    }

    private function createUser(): User
    {
        return User::query()->create([
            'full_name' => 'Sales Person',
            'username' => 'salesperson_activity_test',
            'password' => Hash::make('password123!'),
            'role' => User::ROLE_SALESPERSON,
            'status' => User::STATUS_ACTIVE,
        ]);
    }
}
