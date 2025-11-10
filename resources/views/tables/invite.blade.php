@extends('template')

@section('title', 'Inviter un utilisateur')

@section('content')
  <div class="container mt-4">
    <h1>➕ Inviter un contributeur — {{ $table->name }}</h1>

    <form action="{{ route('tables.invite.store', $table) }}" method="POST">
      @csrf

      <div class="mb-3">
        <label class="form-label">Méthode d’invitation</label>
        <div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="mode" id="mode-email" value="email" checked>
            <label class="form-check-label" for="mode-email">Nouvel utilisateur (e-mail)</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="mode" id="mode-existing" value="existing">
            <label class="form-check-label" for="mode-existing">Utilisateur existant</label>
          </div>
        </div>
      </div>

      <div id="panel-email" class="mode-panel">
        <div class="mb-3">
          <label for="invite_email" class="form-label">Adresse e-mail</label>
          <input type="email" name="email" id="invite_email" class="form-control" placeholder="nouveau@exemple.com"
            value="{{ old('email') }}">
          @error('email') <div class="text-danger">{{ $message }}</div>@enderror
        </div>
      </div>
      <div id="panel-name" class="mode-panel">
        <div class="mb-3">
          <label for="invite_name" class="form-label">Nom complet</label>
          <input type="text" name="name" id="invite_name" class="form-control" placeholder="Nom Complet"
            value="{{ old('name') }}">
          @error('name') <div class="text-danger">{{ $message }}</div>@enderror
        </div>
      </div>

      <div id="panel-existing" class="mode-panel d-none">
        <div class="mb-3 position-relative">
          <label for="user-search" class="form-label">Choisir un·e utilisateur·rice</label>
          <input type="text" id="user-search" class="form-control" placeholder="Rechercher par nom ou trigramme">
          <div id="user-suggestions" class="list-group position-absolute w-100" style="z-index:1000"></div>
          <input type="hidden" name="user_id" id="user_id">
          @error('user_id') <div class="text-danger">{{ $message }}</div>@enderror
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Envoyer</button>
      <a href="{{ route('table.show', $table) }}" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
  @push('scripts')
    <script>
      document.querySelectorAll('input[name="mode"]').forEach(radio => {
        radio.addEventListener('change', () => {
          document.querySelectorAll('.mode-panel').forEach(p => p.classList.add('d-none'));
          document.getElementById('panel-' + radio.value).classList.remove('d-none');
        });
      });

      // type-ahead existants
      const users = @json($existing);
      const inp = document.getElementById('user-search');
      const sugg = document.getElementById('user-suggestions');
      const hid = document.getElementById('user_id');

      inp.addEventListener('input', () => {
        const q = inp.value.toLowerCase();
        sugg.innerHTML = '';
        if (q.length < 2) return;
        users.filter(u =>
          u.name.toLowerCase().includes(q)
          || u.trigramme.toLowerCase().includes(q)
        ).forEach(u => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'list-group-item list-group-item-action';
          btn.textContent = `${u.name} (${u.trigramme})`;
          btn.addEventListener('click', () => {
            inp.value = `${u.name} (${u.trigramme})`;
            hid.value = u.id;
            sugg.innerHTML = '';
          });
          sugg.appendChild(btn);
        });
      });

      document.addEventListener('click', e => {
        if (!inp.contains(e.target)) sugg.innerHTML = '';
      });
    </script>
  @endpush
@endsection