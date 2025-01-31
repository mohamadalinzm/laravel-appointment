<?php

namespace Nzm\Appointment\Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Nzm\Appointment\Facades\AppointmentFacade;
use Nzm\Appointment\Models\Appointment;
use Nzm\Appointment\Tests\Traits\SetUpDatabase;
use Orchestra\Testbench\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase,SetUpDatabase;

    public function test_create_appointment()
    {
        //Arrange
        $data = $this->generateAppointment();
        //Act
        $appointment = Appointment::query()->create($data);
        //Assert
        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertDatabaseHas('appointments', $data);
    }

    public function test_create_appointment_with_note()
    {
        //Arrange
        $data = $this->generateAppointment();
        $data['note'] = 'This is a note';
        //Act
        $appointment = Appointment::query()->create($data);
        //Assert
        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertDatabaseHas('appointments', $data);
    }

    public function test_create_appointment_via_facade()
    {
        $appointment = AppointmentFacade::setAgent($this->agent)
            ->setClient($this->client)
            ->startTime(now()->format('Y-m-d H:i'))
            ->endTime(now()->addMinutes(30)->format('Y-m-d H:i'))
            ->save();

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertDatabaseHas('appointments', [
            'agentable_type' => get_class($this->agent),
            'agentable_id' => $this->agent->id,
            'clientable_type' => get_class($this->client),
            'clientable_id' => $this->client->id,
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time,
        ]);
    }

    public function test_create_appointment_with_note_via_facade()
    {
        //Arrange
        $note = 'This is a note';
        //Act
        $appointment = AppointmentFacade::setAgent($this->agent)
            ->setClient($this->client)
            ->startTime(now()->format('Y-m-d H:i'))
            ->endTime(now()->addMinutes(30)->format('Y-m-d H:i'))
            ->note($note)
            ->save();
        //Assert
        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertDatabaseHas('appointments', [
            'agentable_type' => get_class($this->agent),
            'agentable_id' => $this->agent->id,
            'clientable_type' => get_class($this->client),
            'clientable_id' => $this->client->id,
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time,
            'note' => $note,
        ]);
    }

    public function test_create_appointment_via_count_and_duration_via_facade()
    {
        $duration = 30;
        $count = 3;
        $appointments = AppointmentFacade::setAgent($this->agent)
            ->startTime(now()->format('Y-m-d H:i'))
            ->duration($duration)
            ->count($count)
            ->save();

        $this->assertIsArray($appointments);
        $this->assertCount($count, $appointments);

        foreach ($appointments as $appointment) {
            $this->assertInstanceOf(Appointment::class, $appointment);
            $this->assertDatabaseHas('appointments', [
                'agentable_type' => get_class($this->agent),
                'agentable_id' => $this->agent->id,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
            ]);
        }
    }

    public function test_validation_on_add_appointment_via_count_and_without_duration_via_facade()
    {
        $this->expectException(ValidationException::class);

        try {

            AppointmentFacade::setAgent($this->agent)
                ->setClient($this->client)
                ->startTime(now()->format('Y-m-d H:i'))
                ->count(3)
                ->save();

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            $this->assertTrue($errors->has('end_time'), 'The end time field is required when duration / count is not present.');
            $this->assertTrue($errors->has('duration'), 'The duration field is required.');

            throw $e;
        }
    }

    public function test_validation_on_add_appointment_via_duration_and_without_count_via_facade()
    {
        $this->expectException(ValidationException::class);

        try {

            AppointmentFacade::setAgent($this->agent)
                ->setClient($this->client)
                ->startTime(now()->format('Y-m-d H:i'))
                ->duration(30)
                ->save();

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            $this->assertTrue($errors->has('end_time'), 'The end time field is required when duration / count is not present.');
            $this->assertTrue($errors->has('count'), 'The count field is required.');

            throw $e;
        }
    }

    public function test_validation_on_add_appointment_without_count_and_duration_via_facade()
    {
        $this->expectException(ValidationException::class);

        try {

            AppointmentFacade::setAgent($this->agent)
                ->setClient($this->client)
                ->startTime(now()->format('Y-m-d H:i'))
                ->save();

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            $this->assertTrue($errors->has('end_time'), 'The end time field is required when duration / count is not present.');

            throw $e;
        }
    }

    public function test_validation_on_add_appointment_without_start_time_via_facade()
    {
        $this->expectException(ValidationException::class);

        try {

            AppointmentFacade::setAgent($this->agent)
                ->setClient($this->client)
                ->save();

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            $this->assertTrue($errors->has('start_time'), 'The start time field is required.');

            throw $e;
        }
    }

    //add test for overlap validation
    public function test_validation_on_add_appointment_with_overlap_via_facade()
    {
        $this->expectException(ValidationException::class);

        try {

            $data = $this->generateAppointment();
            $data['start_time'] = now()->format('Y-m-d H:i');
            $data['end_time'] = now()->addMinutes(30)->format('Y-m-d H:i');

            $this->agent->agentAppointments()->create($data);

            AppointmentFacade::setAgent($this->agent)
                ->setClient($this->client)
                ->startTime(now()->addMinutes(10)->format('Y-m-d H:i'))
                ->endTime(now()->addMinutes(40)->format('Y-m-d H:i'))
                ->save();

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            $this->assertTrue($errors->has('start_time'), 'This appointment conflicts with an existing appointment time.');

            throw $e;
        }
    }
}
