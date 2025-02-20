<?php

namespace App;

enum TaskStatus: string
{
    case Open = 'open';
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Closed = 'closed';
}
