<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\NotificationRepository;
use App\Models\Notification;

/**
 * Class NotificationRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class NotificationRepositoryEloquent extends BaseRepository implements NotificationRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Notification::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }



    public function readNotifications()
    {
        return Notification::query()->where([
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => auth()->user()->id ?? null,
            'read_at' => null
        ])->update([
            'read_at' => now()
        ]);
    }
    
}
