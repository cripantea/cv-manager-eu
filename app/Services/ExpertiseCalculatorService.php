<?php

namespace App\Services;

use App\Models\Cv;
use Carbon\Carbon;

class ExpertiseCalculatorService
{
    public function calculate(Cv $cv): array
    {
        // Collect intervals per technology: ['tech' => [[start_ts, end_ts], ...]]
        $intervals = [];

        foreach ($cv->projects as $project) {
            if (!$project->start_date) continue;

            $start = Carbon::parse($project->start_date)->startOfMonth()->timestamp;
            $end   = $project->end_date
                ? Carbon::parse($project->end_date)->startOfMonth()->timestamp
                : Carbon::now()->startOfMonth()->timestamp;

            if ($start >= $end) continue;

            foreach ($project->technologies as $tech) {
                $key = strtolower(trim($tech->technology_name));
                $intervals[$key][] = [$start, $end];
            }
        }

        $expertise = [];

        foreach ($intervals as $tech => $periods) {
            // Sort by start date
            usort($periods, fn($a, $b) => $a[0] <=> $b[0]);

            // Merge overlapping/contiguous intervals
            $merged = [array_shift($periods)];
            foreach ($periods as [$s, $e]) {
                $last = &$merged[count($merged) - 1];
                if ($s <= $last[1]) {
                    $last[1] = max($last[1], $e);
                } else {
                    $merged[] = [$s, $e];
                }
            }

            // Sum months across merged intervals
            $totalMonths = 0;
            foreach ($merged as [$s, $e]) {
                $totalMonths += (int) Carbon::createFromTimestamp($s)
                    ->diffInMonths(Carbon::createFromTimestamp($e));
            }

            $expertise[$tech] = $totalMonths;
        }

        return $expertise;
    }
}
