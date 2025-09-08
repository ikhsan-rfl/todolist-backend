<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public static function baseQuery(int $id = 0)
    {
        return self::when(
            $id !== 0,
            function ($query) use ($id) {
                $query->where('id', $id);
            }
        );
    }

    public function tasks()
    {
        return $this->hasMany(Tasks::class);
    }
}
