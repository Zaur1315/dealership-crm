<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadPipelineStage;
use App\Enums\TaskStatus;
use App\Models\Dealership;
use App\Models\Lead;
use App\Models\User;
use App\Services\Leads\LeadPipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeadPipelineServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_first_communication_timestamp_when_lead_moves_to_in_communication(): void
    {
        $lead = $this->createLead();

        app(LeadPipelineService::class)->moveToStage(
            lead: $lead,
            stage: LeadPipelineStage::IN_COMMUNICATION,
        );

        $lead->refresh();

        $this->assertSame(LeadPipelineStage::IN_COMMUNICATION, $lead->pipeline_stage);
        $this->assertNotNull($lead->first_communication_at);
    }

    public function test_it_sets_won_timestamp_when_lead_is_marked_as_won(): void
    {
        $lead = $this->createLead();

        app(LeadPipelineService::class)->moveToStage(
            lead: $lead,
            stage: LeadPipelineStage::WON,
        );

        $lead->refresh();

        $this->assertSame(LeadPipelineStage::WON, $lead->pipeline_stage);
        $this->assertNotNull($lead->won_at);
    }

    public function test_it_creates_did_not_answer_follow_up_tasks_once(): void
    {
        $user = $this->createUser();
        $lead = $this->createLead();

        app(LeadPipelineService::class)->moveToStage(
            lead: $lead,
            stage: LeadPipelineStage::DID_NOT_ANSWER,
            changedBy: $user,
        );

        app(LeadPipelineService::class)->moveToStage(
            lead: $lead,
            stage: LeadPipelineStage::DID_NOT_ANSWER,
            changedBy: $user,
        );

        $this->assertSame(6, $lead->tasks()->count());

        $this->assertSame(
            6,
            $lead->tasks()
                ->where('status', TaskStatus::ACTIVE->value)
                ->count(),
        );
    }

    private function createLead(): Lead
    {
        $dealership = Dealership::query()->create([
            'name' => 'Test Dealership',
            'email' => 'dealer@example.com',
            'is_active' => true,
        ]);

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
            'username' => 'salesperson_test',
            'password' => Hash::make('password123!'),
            'role' => User::ROLE_SALESPERSON,
            'status' => User::STATUS_ACTIVE,
        ]);
    }
}
