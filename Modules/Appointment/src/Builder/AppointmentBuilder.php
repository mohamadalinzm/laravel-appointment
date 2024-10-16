<?php

namespace Appointment\Builder;

use App\Models\User;
use Appointment\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AppointmentBuilder
{
    protected $agentable;
    protected $clientable;
    protected $startTime;
    protected $endTime = null;

    protected ?int $duration = null;
    protected ?int $count = null;

    public function with($agentable): static
    {
        $this->agentable = $agentable;
        return $this;
    }

    public function for($clientable): static
    {
        $this->clientable = $clientable;
        return $this;
    }

    public function from($startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function to($endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function duration(?int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function count(?int $count): static
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @throws ValidationException
     */
    protected function validate(): void
    {
        $validator = Validator::make([
            'agentable_id' => $this->agentable->id,
            'agentable_type' => get_class($this->agentable),
            'clientable_id' => $this->clientable->id,
            'clientable_type' => get_class($this->clientable),
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'count' => $this->count,
            'duration' => $this->duration,
        ], [
            'agentable_id' => 'required|integer|exists:' . $this->agentable->getTable() . ',id',
            'agentable_type' => 'required|string',
            'clientable_id' => 'sometimes|integer|exists:' . $this->clientable->getTable() . ',id',
            'clientable_type' => 'sometimes|string',
            'start_time' => [
                'required', 'date_format:Y-m-d H:i', 'before:end_time',
                Rule::unique('appointments')->where(function ($query) {
                    return $query->where('agentable_id', $this->agentable->id)
                        ->where('agentable_type', get_class($this->agentable))
                        ->where('clientable_id', $this->clientable->id)
                        ->where('clientable_type', get_class($this->clientable));
                }),
            ],
            'end_time' => ['nullable','required_without:duration,count','date_format:Y-m-d H:i','after:start_time'],
            'count' => ['nullable', 'integer', 'min:1', 'required_with:duration'],
            'duration' => ['nullable', 'integer', 'min:1', 'required_with:count']
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @throws ValidationException
     */
    public function build(): array|Appointment
    {
        $this->validate();

        $appointments = [];

        if ($this->duration && $this->count) {

            for ($i = 0; $i < $this->count; $i++) {

                //convert start time string to Carbon instance
                $this->endTime = now()->parse($this->startTime)->addMinutes($this->duration)->format('Y-m-d H:i');
                $appointments[] = Appointment::query()->create([
                    'agentable_id' => $this->agentable->id,
                    'agentable_type' => get_class($this->agentable),
                    'clientable_id' => $this->clientable->id,
                    'clientable_type' => get_class($this->clientable),
                    'start_time' => $this->startTime,
                    'end_time' => $this->endTime,
                ]);

                $this->startTime = $this->endTime;
            }

            return $appointments;
        }

        return Appointment::query()->create([
            'agentable_id' => $this->agentable->id,
            'agentable_type' => get_class($this->agentable),
            'clientable_id' => $this->clientable->id,
            'clientable_type' => get_class($this->clientable),
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
        ]);

    }
}
