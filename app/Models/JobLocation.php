<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'state',
        'country',
        'address',
        'postal_code',
        'is_remote',
    ];

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class, 'location_id');
    }

    /**
     * Split "City, Country" or "City, State, Country" into columns; null when
     * the text doesn't fit either shape.
     */
    public static function parseLabel(string $label): ?array
    {
        $parts = array_map('trim', explode(',', $label));

        if (! in_array(count($parts), [2, 3], true) || in_array('', $parts, true)) {
            return null;
        }

        foreach ($parts as $part) {
            if (mb_strlen($part) > 100) {
                return null;
            }
        }

        return count($parts) === 2
            ? ['city' => $parts[0], 'state' => null, 'country' => $parts[1]]
            : ['city' => $parts[0], 'state' => $parts[1], 'country' => $parts[2]];
    }
}
