<?php


use App\Models\User;
use Appointment\Facades\AppointmentFacade;
use Appointment\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected $agent;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = User::factory()->create();
        $this->client = User::factory()->create();
    }
    public function testCreateAppointment()
    {
        $appointment = Appointment::factory()->create();

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertDatabaseHas('appointments', [
            'agentable_type' => $appointment->agentable_type,
            'agentable_id' => $appointment->agentable_id,
            'clientable_type' => $appointment->clientable_type,
            'clientable_id' => $appointment->clientable_id,
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time
        ]);
    }

    public function testBaseAddAppointmentWithBuilder()
    {
        $appointment = AppointmentFacade::with($this->agent)
            ->for($this->client)
            ->from(now()->format('Y-m-d H:i'))
            ->to(now()->addMinutes(30)->format('Y-m-d H:i'))
            ->build();

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertDatabaseHas('appointments', [
            'agentable_type' => get_class($this->agent),
            'agentable_id' => $this->agent->id,
            'clientable_type' => get_class($this->client),
            'clientable_id' => $this->client->id,
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time
        ]);
    }

    public function testAddAppointmentViaCountAndDurationWithBuilder()
    {
        $duration = 30;
        $count = 3;
        $appointments = AppointmentFacade::with($this->agent)
            ->for($this->client)
            ->from(now()->format('Y-m-d H:i'))
            ->duration($duration)
            ->count($count)
            ->build();

        $this->assertIsArray($appointments);
        $this->assertCount($count, $appointments);

        foreach ($appointments as $appointment)
        {
            $this->assertInstanceOf(Appointment::class, $appointment);
            $this->assertDatabaseHas('appointments', [
                'agentable_type' => get_class($this->agent),
                'agentable_id' => $this->agent->id,
                'clientable_type' => get_class($this->client),
                'clientable_id' => $this->client->id,
                'start_time' => $appointment->start_time,
                'end_time' =>$appointment->end_time
            ]);
        }
    }

    public function testValidationOnAddAppointmentViaCountAndWithoutDurationWithBuilder()
    {
        $this->expectException(ValidationException::class);

        try {

            AppointmentFacade::with($this->agent)
                ->for($this->client)
                ->from(now()->format('Y-m-d H:i'))
                ->count(3)
                ->build();

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            $this->assertTrue($errors->has('end_time'), 'Validation error for end_time is missing');
            $this->assertTrue($errors->has('duration'), 'Validation error for duration is missing');

            throw $e;
        }
    }

    public function testValidationOnAddAppointmentViaDurationAndWithoutCountWithBuilder()
    {
        $this->expectException(ValidationException::class);

        try {

            AppointmentFacade::with($this->agent)
                ->for($this->client)
                ->from(now()->format('Y-m-d H:i'))
                ->duration(30)
                ->build();

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            $this->assertTrue($errors->has('end_time'), 'Validation error for end_time is missing');
            $this->assertTrue($errors->has('count'), 'Validation error for count is missing');

            throw $e;
        }
    }

    public function testValidationOnAddAppointmentWithoutCountAndDurationWithBuilder()
    {
        $this->expectException(ValidationException::class);

        try {

            AppointmentFacade::with($this->agent)
                ->for($this->client)
                ->from(now()->format('Y-m-d H:i'))
                ->build();

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            $this->assertTrue($errors->has('end_time'), 'Validation error for end_time is missing');

            throw $e;
        }
    }



}
