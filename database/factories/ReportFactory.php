<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\ReportType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'report_type_id' => ReportType::factory(),
            'user_id' => User::factory(),
            'data' => [],
            'status' => 'belum disetujui',
            'last_edited_by_user_id' => User::factory(),
        ];
    }
}
