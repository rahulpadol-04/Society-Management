<?php

declare(strict_types=1);

namespace App\Events\Notices;

use App\Models\Notice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoticePublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public Notice $notice) {}
}
