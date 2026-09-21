<?php

namespace App\View\Composers;

use App\Models\WorkNote;
use App\Models\WorkNoteRead;
use Illuminate\View\View;

class WorkNoteComposer
{
  /**
   * Ilalagay nito yung $unreadWorkNotes variable sa kahit anong
   * view/layout na naka-attach dito (hal. yung sidebar layout).
   */
  public function compose(View $view): void
  {
    $userId = auth()->id();

    // kung hindi naka-login, walang unread count
    if (!$userId) {
      $view->with('unreadWorkNotes', 0);
      return;
    }

    // kunin kung kailan huling binuksan ng user yung Work Notes page
    $lastReadAt = WorkNoteRead::where('user_id', $userId)->value('last_read_at');

    // huwag isama sa count yung sarili niyang mensahe
    $query = WorkNote::where('user_id', '!=', $userId);

    // kung meron nang last_read_at, kunin lang yung mga bago pa
    if ($lastReadAt) {
      $query->where('created_at', '>', $lastReadAt);
    }

    $view->with('unreadWorkNotes', $query->count());
  }
}
