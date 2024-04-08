<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Blog;
use App\Models\Order;
use App\Models\PushNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Str;

class PushNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var PushNotification|JsonResource $this */

        $model = $this->relationLoaded('model') ? $this->model : optional();

        if (!empty($model)) {

            if (get_class($model) === Order::class) {
                $model = OrderResource::make($model
                    ->loadMissing('user:id,firstname,lastname,active,img')
                    ->select(['id', 'user_id', 'parent_id'])
                    ->first()
                );
            } else if (get_class($model) === User::class) {
                $model = $model->only([
                    'id',
                    'firstname',
                    'lastname',
                    'img',
                ]);
            } else if (get_class($model) === Blog::class) {
                $model = BlogResource::make($model);
            }

        }

        $modelType = Str::lower(str_replace('App\\Models\\', '', $this->model_type));

        if ($this->type === 'news_publish') {
            $modelType = 'notifications';
        }

        return [
            'id'            => $this->when($this->id,          $this->id),
            'type'          => $this->when($this->type,        $this->type),
            'title'         => $this->when($this->title,       $this->title),
            'body'          => $this->when($this->body,        $this->body),
            'data'          => $this->when($this->data,        $this->data),
            'user_id'       => $this->when($this->user_id,     $this->user_id),
            'model_id'      => $this->when($this->model_id,    $this->model_id),
            'model_type'    => $this->when($this->model_type,  $modelType),
            'created_at'    => $this->when($this->created_at,  $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'    => $this->when($this->updated_at,  $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),
            'read_at'       => $this->when($this->read_at,     $this->read_at . 'Z'),

            'user'          => UserResource::make($this->whenLoaded('user')),
            'model'         => $this->when($model, $model),
        ];
    }
}
