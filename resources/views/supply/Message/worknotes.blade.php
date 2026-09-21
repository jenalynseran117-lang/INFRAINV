@extends('layouts.Supply.app')

@section('content')

@php
    // Same role -> color mapping is mirrored in JS (AVATAR_COLORS) below,
    // used when messages are appended live via polling instead of Blade.
    // Matched in lowercase since that's how roles are actually stored
    // (auth()->user()->role returns 'admin' / 'supply' / 'inspector').
    $avatarColor = fn ($role) => match (strtolower($role ?? '')) {
        'admin'      => 'bg-slate-800',
        'supply'     => 'bg-red-500',
        'inspector'  => 'bg-blue-500',
        default      => 'bg-slate-400',
    };
    $lastSelfNoteId = optional($notes->last(fn ($n) => $n->role === auth()->user()?->role))->id;
@endphp

<div class="relative min-h-full p-4">

    {{-- Ambient glass background: soft brand-colored light pooling behind the panel --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden -z-10">
        <div class="absolute -top-24 -left-16 w-[26rem] h-[26rem] rounded-full bg-amber-400/30 blur-3xl"></div>
        <div class="absolute top-1/3 -right-24 w-[30rem] h-[30rem] rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute -bottom-32 left-1/4 w-[24rem] h-[24rem] rounded-full bg-orange-300/25 blur-3xl"></div>
    </div>

    <div class="container mx-auto h-full">
        <div class="flex flex-col h-[calc(100vh-160px)]
                    bg-white/40 backdrop-blur-2xl
                    rounded-[2rem] shadow-2xl shadow-slate-900/10
                    border border-white/60 ring-1 ring-white/40
                    overflow-hidden">

            {{-- Header --}}
            <div class="p-5 border-b border-white/50 bg-white/30 backdrop-blur-xl flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Work Notes for supply</h2>
                    <p class="text-xs text-slate-600/80 italic">Internal communication for Admin, Supply, and Inspector</p>
                </div>
                <div class="flex items-center gap-2 bg-white/50 border border-white/60 backdrop-blur-md rounded-full px-3 py-1.5 shadow-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] font-semibold text-slate-700 uppercase tracking-wider">Live System</span>
                </div>
            </div>

            {{-- Messages --}}
            <div id="chat-container" class="flex-1 overflow-y-auto p-6 space-y-4">
                @forelse($notes as $note)
                @php $isSelf = $note->role == auth()->user()?->role; @endphp

                <div class="msg-row flex items-end gap-2 {{ $isSelf ? 'justify-end' : 'justify-start' }}" data-note-id="{{ $note->id }}">

                    @unless($isSelf)
                        <div class="avatar shrink-0 h-8 w-8 rounded-full flex items-center justify-center text-white text-xs font-bold {{ $avatarColor($note->role) }}">
                            {{ strtoupper(substr($note->user->name ?? $note->role, 0, 1)) }}
                        </div>
                    @endunless

                    <div class="max-w-[75%] lg:max-w-[50%]">
                        <span class="block text-[10px] font-bold mb-1 px-2 {{ $isSelf ? 'text-right text-amber-700' : 'text-left text-slate-500' }}">
                            {{ $note->user->name ?? $note->role }}
                        </span>

                        <div class="p-4 backdrop-blur-xl shadow-sm border
                                {{ $isSelf
                                        ? 'bg-gradient-to-br from-amber-500/85 to-orange-600/85 text-white border-white/30 rounded-2xl rounded-tr-md shadow-orange-900/10'
                                        : 'bg-white/55 text-slate-700 border-white/70 rounded-2xl rounded-tl-md shadow-slate-900/5' }}">
                            <p class="text-sm leading-relaxed">{{ $note->message }}</p>
                        </div>

                        <span class="block text-[9px] mt-1 px-2 text-slate-500 {{ $isSelf ? 'text-right' : 'text-left' }}">
                            {{ $note->created_at->diffForHumans() }}
                        </span>
                    </div>

                    @if($isSelf)
                        <div class="avatar shrink-0 h-8 w-8 rounded-full flex items-center justify-center text-white text-xs font-bold {{ $avatarColor($note->role) }}">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                {{-- "Delivered" only under the current user's OWN latest message --}}
                @if($isSelf && $note->id === $lastSelfNoteId)
                <div id="delivered-label" class="text-right pr-12">
                    <span class="text-[10px] text-slate-400 font-medium">Delivered</span>
                </div>
                @endif

                @empty
                <div class="flex flex-col items-center justify-center h-full text-slate-500/70">
                    <div class="w-16 h-16 mb-3 rounded-2xl bg-white/40 backdrop-blur-md border border-white/60 flex items-center justify-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8-1.06 0-2.076-.163-3.02-.465L3 21l1.5-4.5C3.55 15.24 3 13.66 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <p class="text-sm italic">No notes yet. Start the conversation!</p>
                </div>
                @endforelse

                <div id="typing-indicator" class="hidden text-xs text-slate-500 italic px-2"></div>
            </div>

            {{-- Composer --}}
            <div class="p-4 bg-white/30 backdrop-blur-xl border-t border-white/50">
                <form id="chat-form" class="flex gap-3 items-center">
                    @csrf
                    <input type="text" id="message-input" autocomplete="off" placeholder="Type your note here..."
                        class="flex-1 bg-white/50 backdrop-blur-md border border-white/60 rounded-full px-6 py-3 text-sm text-slate-800 placeholder:text-slate-500
                               focus:ring-2 focus:ring-amber-400/60 focus:border-white/80 outline-none transition-all shadow-inner">

                    <button type="submit"
                        class="bg-gradient-to-br from-slate-800 to-slate-900 hover:from-amber-600 hover:to-orange-600
                               text-white p-3 rounded-full shadow-lg shadow-slate-900/20 border border-white/10
                               transition-all active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                        </svg>
                    </button>
                </form>
            </div>
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
    'admin': 'bg-slate-800',
    'supply': 'bg-red-500',
    'inspector': 'bg-blue-500',
  };
  const avatarColor = (role) => AVATAR_COLORS[(role || '').toLowerCase()] || 'bg-slate-400';
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
      <span class="block text-[10px] font-bold mb-1 px-2 ${isSelf ? 'text-right text-amber-700' : 'text-left text-slate-500'}">${note.user_name}</span>
      <div class="p-4 backdrop-blur-xl shadow-sm border ${isSelf ? 'bg-gradient-to-br from-amber-500/85 to-orange-600/85 text-white border-white/30 rounded-2xl rounded-tr-md shadow-orange-900/10' : 'bg-white/55 text-slate-700 border-white/70 rounded-2xl rounded-tl-md shadow-slate-900/5'}">
        <p class="text-sm leading-relaxed"></p>
      </div>
      <span class="block text-[9px] mt-1 px-2 text-slate-500 ${isSelf ? 'text-right' : 'text-left'}">just now</span>
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

    axios.post("{{ route('supply.Messagestore') }}", { message: message })
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
      axios.post("{{ route('supply.Messagetyping') }}").catch(() => {});
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
    axios.get("{{ route('supply.Messagepoll') }}", { params: { after: lastNoteId } })
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