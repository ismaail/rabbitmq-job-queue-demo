<?php

namespace App;

class Job
{
    /**
     * Job constructor.
     *
     * @param int $id
     * @param string $name
     * @param int $sleepPeriod
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $sleepPeriod,
    ) {
    }

    /**
     * @return string|false
     */
    public function toJson(): string|false
    {
        return json_encode([
            'id' => $this->id,
            'name' => $this->name,
            'sleep_period' => $this->sleepPeriod,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $body
     *
     * @return \App\Job
     */
    public static function fromJson(string $body): self
    {
        $data = json_decode($body, flags: JSON_THROW_ON_ERROR);
        return new Job($data->id, $data->name, $data->sleep_period);
    }
}
