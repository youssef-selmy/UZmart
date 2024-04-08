<?php
declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PageTranslation
 *
 * @property int $id
 * @property int $page_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereAddress($value)
 * @method static Builder|self whereDescription($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereLocale($value)
 * @method static Builder|self wherePageId($value)
 * @method static Builder|self whereTitle($value)
 * @mixin Eloquent
 */
class PageTranslation extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];
}
