<?php

namespace App\Services\Utils;

class Downsampler
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // sample                   (1.0) Downsample numeric time series using
    //                                Largest-Triangle-Three-Buckets.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> sample
     * <Function> Downsample numeric time series using Largest-Triangle-Three-Buckets.
     *
     * Expects $data as array of [timestamp, value] where timestamp is numeric (seconds or ms).
     * Returns sampled points in the same timestamp units.
     *
     * @param array $data
     * @param int $threshold
     * @return array
     */
    public static function sample(array $data, int $threshold): array
    {
        $dataLength = count($data);
        if ($threshold >= $dataLength || $threshold < 3) return $data;

        $bucketSize = ($dataLength - 2) / ($threshold - 2);
        $sampled = [];
        $sampled[] = $data[0];
        $a = 0;

        for ($i = 0; $i < $threshold - 2; $i++) {
            $rangeStart = (int)floor(($i + 1) * $bucketSize) + 1;
            $rangeEnd = min((int)floor(($i + 2) * $bucketSize) + 1, $dataLength);

            $pointRange = array_slice($data, $rangeStart, $rangeEnd - $rangeStart);
            if (empty($pointRange)) {
                continue;
            }

            $xs = array_column($pointRange, 0);
            $ys = array_column($pointRange, 1);

            $avgX = array_sum($xs) / count($xs);
            $avgY = array_sum($ys) / count($ys);

            $maxArea = -1;
            $nextPoint = 0;

            foreach ($pointRange as $j => $point) {
                $area = abs((($data[$a][0] - $avgX) * ($point[1] - $data[$a][1]) -
                    ($data[$a][0] - $point[0]) * ($avgY - $data[$a][1])) / 2);
                if ($area > $maxArea) {
                    $maxArea = $area;
                    $nextPoint = $j;
                }
            }

            $sampled[] = $pointRange[$nextPoint];
            $a = $rangeStart + $nextPoint;
        }

        $sampled[] = $data[$dataLength - 1];
        return $sampled;
    }
}
