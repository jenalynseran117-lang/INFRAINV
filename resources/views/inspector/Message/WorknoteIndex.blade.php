@extends('layouts.Inspector.app')

@section('content')

@php
    // Same role -> color mapping is mirrored in JS (AVATAR_COLORS) below,
    // used when messages are appended live via polling instead of Blade.
    $avatarColor = fn ($role) => match ($role) {
        'Admin'      => 'bg-slate-800',
        'Supply'     => 'bg-red-500',
        'Inspector'  => 'bg-blue-500',
        default      => 'bg-slate-400',
    };
    $lastSelfNoteId = optional($notes->last(fn ($n) => $n->role === auth()->user()->role))->id;
@endphp

<div class="container mx-auto p-4 h-full">
  <div class="flex flex-col h-[calc(100vh-160px)] bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">

    <div class="p-5 border-b bg-white flex justify-between items-center">
      <div>
        <h2 class="text-xl font-bold text-slate-800">Work Notes for Inspector</h2>
        <p class="text-xs text-slate-500 italic">Internal communication for Admin, Supply, and Inspector</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
        </span>
        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Live System</span>
      </div>
    </div>

    <div id="chat-container" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50">
      @forelse($notes as $note)
      @php $isSelf = $note->role == auth()->user()->role; @endphp

      <div class="msg-row flex items-end gap-2 {{ $isSelf ? 'justify-end' : 'justify-start' }}" data-note-id="{{ $note->id }}">

        @unless($isSelf)
          <div class="avatar shrink-0 h-8 w-8 rounded-full flex items-center justify-center text-white text-xs font-bold {{ $avatarColor($note->role) }}">
            {{ strtoupper(substr($note->user->name ?? $note->role, 0, 1)) }}
          </div>
        @endunless

        <div class="max-w-[75%] lg:max-w-[50%]">
          <span class="block text-[10px] font-bold mb-1 px-2 {{ $isSelf ? 'text-right text-blue-600' : 'text-left text-slate-500' }}">
            {{ $note->user->name ?? $note->role }}
          </span>

          <div class="p-4 shadow-sm {{ $isSelf
                            ? 'bg-blue-600 text-white rounded-2xl rounded-tr-none' 
                            : 'bg-white text-slate-700 border border-slate-200 rounded-2xl rounded-tl-none' }}">
            <p class="text-sm leading-relaxed">{{ $note->message }}</p>
          </div>

          <span class="block text-[9px] mt-1 px-2 opacity-60 {{ $isSelf ? 'text-right' : 'text-left' }}">
            {{ $note->created_at->diffForHumans() }}
          </span>
        </div>

        @if($isSelf)
          <div class="avatar shrink-0 h-8 w-8 rounded-full flex items-center justify-center text-white text-xs font-bold {{ $avatarColor($note->role) }}">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
          </div>
        @endif
      </div>

      {{-- "Delivered" only under the current user's OWN latest message, iMessage/Messenger-style --}}
      @if($isSelf && $note->id === $lastSelfNoteId)
      <div id="delivered-label" class="text-right pr-12">
        <span class="text-[10px] text-slate-400 font-medium">Delivered</span>
      </div>
      @endif

      @empty
      <div class="flex flex-col items-center justify-center h-full opacity-40">
        <p class="text-sm">No notes yet. Start the conversation!</p>
      </div>
      @endforelse

      <div id="typing-indicator" class="hidden text-xs text-slate-400 italic px-2"></div>
    </div>

    <div class="p-4 bg-white border-t border-slate-100">
      <form id="chat-form" class="flex gap-3 items-center">
        @csrf
        <input type="text" id="message-input" autocomplete="off" placeholder="Type your note here..."
          class="flex-1 bg-slate-100 border-none rounded-full px-6 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">

        <button type="submit" class="bg-slate-900 hover:bg-blue-700 text-white p-3 rounded-full shadow-md transition-all active:scale-95">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" viewBox="0 0 24 24">
            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
          </svg>
        </button>
      </form>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
  const CURRENT_USER_ID = {{ auth()->id() }};

  const chatContainer   = document.getElementById('chat-container');
  const typingIndicator = document.getElementById('typing-indicator');
  let lastNoteId = {{ optional($notes->last())->id ?? 0 }};

  chatContainer.scrollTop = chatContainer.scrollHeight;

  axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Mirrors the $avatarColor map in the Blade @php block above — keep both in sync.
  const AVATAR_COLORS = {
    'Admin': 'bg-slate-800',
    'Supply': 'bg-red-500',
    'Inspector': 'bg-blue-500',
  };
  const avatarColor = (role) => AVATAR_COLORS[role] || 'bg-slate-400';
  const initials     = (name) => (name || '?').trim().charAt(0).toUpperCase();

  function removeDeliveredLabel() {
    const el = document.getElementById('delivered-label');
    if (el) el.remove();
  }

  // Builds one message row exactly like the Blade loop above, then inserts
  // it right before the typing indicator (keeps it pinned to the bottom).
  function appendNote(note) {
    const isSelf = note.user_id === CURRENT_USER_ID;

    const row = document.createElement('div');
    row.className = `msg-row flex items-end gap-2 ${isSelf ? 'justify-end' : 'justify-start'}`;
    row.dataset.noteId = note.id;

    const avatarHtml = `<div class="avatar shrink-0 h-8 w-8 rounded-full flex items-center justify-center text-white text-xs font-bold ${avatarColor(note.role)}">${initials(note.user_name)}</div>`;

    const bubbleWrap = document.createElement('div');
    bubbleWrap.className = 'max-w-[75%] lg:max-w-[50%]';
    bubbleWrap.innerHTML = `
      <span class="block text-[10px] font-bold mb-1 px-2 ${isSelf ? 'text-right text-blue-600' : 'text-left text-slate-500'}">${note.user_name}</span>
      <div class="p-4 shadow-sm ${isSelf ? 'bg-blue-600 text-white rounded-2xl rounded-tr-none' : 'bg-white text-slate-700 border border-slate-200 rounded-2xl rounded-tl-none'}">
        <p class="text-sm leading-relaxed"></p>
      </div>
      <span class="block text-[9px] mt-1 px-2 opacity-60 ${isSelf ? 'text-right' : 'text-left'}">just now</span>
    `;
    // set via textContent (not template string) so a message can never break out as HTML
    bubbleWrap.querySelector('p').textContent = note.message;

    if (isSelf) {
      row.appendChild(bubbleWrap);
      row.appendChild(document.createRange().createContextualFragment(avatarHtml));
      removeDeliveredLabel();
    } else {
      row.appendChild(document.createRange().createContextualFragment(avatarHtml));
      row.appendChild(bubbleWrap);
    }

    chatContainer.insertBefore(row, typingIndicator);

    if (isSelf) {
      const delivered = document.createElement('div');
      delivered.id = 'delivered-label';
      delivered.className = 'text-right pr-12';
      delivered.innerHTML = '<span class="text-[10px] text-slate-400 font-medium">Delivered</span>';
      chatContainer.insertBefore(delivered, typingIndicator);
    }

    lastNoteId = Math.max(lastNoteId, note.id);
    chatContainer.scrollTop = chatContainer.scrollHeight;
  }

  document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const input = document.getElementById('message-input');
    const message = input.value.trim();
    if (!message) return;
    input.value = '';

    axios.post("{{ route('inspector.Messagestore') }}", { message: message })
      .then(response => appendNote(response.data.note))
      .catch(error => {
        console.log(error.response);
        alert("Error: " + error.response.status);
      });
  });

  // --- Typing indicator: broadcast (debounced) + poll ---
  let typingDebounce = null;
  document.getElementById('message-input').addEventListener('input', function() {
    clearTimeout(typingDebounce);
    typingDebounce = setTimeout(() => {
      axios.post("{{ route('inspector.Messagetyping') }}").catch(() => {});
    }, 200); // small debounce so it's not firing on literally every keystroke
  });

  function renderTyping(typingUsers) {
    if (!typingUsers || typingUsers.length === 0) {
      typingIndicator.classList.add('hidden');
      typingIndicator.textContent = '';
      return;
    }
    const names = typingUsers.map(t => t.user_name).join(', ');
    typingIndicator.textContent = `${names} ${typingUsers.length > 1 ? 'are' : 'is'} typing...`;
    typingIndicator.classList.remove('hidden');
    chatContainer.scrollTop = chatContainer.scrollHeight;
  }

  function poll() {
    axios.get("{{ route('inspector.Messagepoll') }}", { params: { after: lastNoteId } })
      .then(response => {
        (response.data.notes || []).forEach(note => {
          if (note.id > lastNoteId) appendNote(note);
        });
        renderTyping(response.data.typing);
      })
      .catch(() => {});
  }

  setInterval(poll, 2500);
</script>

@endsection