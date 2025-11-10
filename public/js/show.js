document.addEventListener('DOMContentLoaded', function () {
    // ==== Constantes & helpers ====
    const normKey = s => String(s ?? '').trim().toLowerCase();

    const contributorAllowed = [
        'commentaire suivi',
        'status',
        'avancement',
        'échéance réelle',
        'échéance relle' // selon orthographe stockée
    ];

    // colonnes toujours visibles dans le panneau
    const baseCols = [
        'Description',
        'Responsable',
        'Status',
        'Priorité',
        'Avancement',
        'Échéance planifié',
        'Échéance relle',
        'Échéance réelle',
        'Source',
        'Commentaire suivi',
        'Atteinte objectif'
    ];

    const canModifyAll = !!window.tableConfig?.canModifyAll;
    const imp = window.tableConfig?.important || [];
    const multi = window.tableConfig?.multiLine || [];
    const statusOpt = window.tableConfig?.statusOptions || [];
    const priorOpt = window.tableConfig?.priorityOptions || [];
    const allCols = window.tableConfig?.allCols || [];
    const sources = window.tableConfig?.sources || [];
    const base = window.tableConfig?.baseUrl || '';
    const addUrl = window.tableConfig?.addUrl || '';
    const token = window.tableConfig?.csrfToken || '';

    const allowedSet = new Set(contributorAllowed.map(normKey));
    const baseSet = new Set(baseCols.map(normKey));
    const impSet = new Set(imp.map(normKey));

    let currentRowId = null;
    let currentRowData = null;

    // Offcanvas
    const offEl = document.getElementById('detailCanvas');
    const off = bootstrap.Offcanvas.getOrCreateInstance(offEl);

    function recalc(v) {
        const r = Math.round(255 - 2.55 * v);
        const g = Math.round(2.55 * v);
        return `rgb(${r},${g},0)`;
    }

    function updateProgressVisuals(row, value) {
        const bar = row.querySelector('.progress-bar');
        if (bar) {
            bar.style.width = value + '%';
            bar.style.backgroundColor = recalc(value);
            bar.setAttribute('aria-valuenow', value);
        }
        if (String(currentRowId) === String(row?.dataset?.id)) {
            const panelBar = document.querySelector('#detail-fields .progress-bar');
            const panelInput = document.querySelector('#detail-fields .avancement-input');
            if (panelBar && panelInput) {
                panelBar.style.width = value + '%';
                panelBar.style.backgroundColor = recalc(value);
                panelBar.setAttribute('aria-valuenow', value);
                panelInput.value = value;
            }
        }
    }

    function attachProgressButtons(container) {
        container.querySelectorAll('.inc-detail').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = container.querySelector('.avancement-input');
                let v = parseInt(input.value, 10) || 0;
                v = Math.min(100, v + 10);
                input.value = v;
                const bar = container.querySelector('.progress-bar');
                bar.style.width = v + '%';
                bar.style.backgroundColor = recalc(v);
                bar.setAttribute('aria-valuenow', v);

                const row = document.querySelector(`#table-body tr[data-id="${currentRowId}"]`);
                if (row) updateProgressVisuals(row, v);
            });
        });
        container.querySelectorAll('.dec-detail').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = container.querySelector('.avancement-input');
                let v = parseInt(input.value, 10) || 0;
                v = Math.max(0, v - 10);
                input.value = v;
                const bar = container.querySelector('.progress-bar');
                bar.style.width = v + '%';
                bar.style.backgroundColor = recalc(v);
                bar.setAttribute('aria-valuenow', v);

                const row = document.querySelector(`#table-body tr[data-id="${currentRowId}"]`);
                if (row) updateProgressVisuals(row, v);
            });
        });
    }

    function renderResponsables(trigrammes) {
        let arr = [];
        if (Array.isArray(trigrammes)) arr = trigrammes;
        else if (typeof trigrammes === 'string') {
            try { arr = JSON.parse(trigrammes); }
            catch { arr = trigrammes.split(',').map(s => s.trim()).filter(Boolean); }
        }
        const container = document.createElement('div');
        container.className = 'd-flex flex-wrap gap-2 mb-2';

        arr.forEach(tri => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary d-inline-flex align-items-center';
            badge.dataset.trigramme = tri;
            badge.innerHTML = `
                ${tri}
                <button type="button" class="btn-close btn-close-white btn-sm ms-1" data-tri="${tri}"></button>
            `;
            container.appendChild(badge);
        });
        return container;
    }

    function setupResponsableSearch(existing = []) {
        const users = window.tableConfig?.users || [];
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3';

        const lbl = document.createElement('label');
        lbl.className = 'form-label';
        lbl.textContent = 'Responsable';
        wrapper.appendChild(lbl);

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Rechercher un utilisateur...';
        input.className = 'form-control mb-1';
        wrapper.appendChild(input);

        const suggestions = document.createElement('div');
        suggestions.className = 'list-group mb-1';
        wrapper.appendChild(suggestions);

        const selectedContainer = renderResponsables(existing);
        wrapper.appendChild(selectedContainer);

        function updateHidden() {
            wrapper.querySelectorAll('input[name="data[Responsable][]"]').forEach(el => el.remove());
            Array.from(selectedContainer.querySelectorAll('span')).forEach(el => {
                const tri = el.dataset.trigramme;
                const h = document.createElement('input');
                h.type = 'hidden';
                h.name = 'data[Responsable][]';
                h.value = tri;
                wrapper.appendChild(h);
            });
        }
        updateHidden();

        input.addEventListener('input', () => {
            const q = input.value.toLowerCase().trim();
            suggestions.innerHTML = '';
            const already = Array.from(selectedContainer.children).map(el => el.dataset.trigramme);

            users
                .filter(u =>
                    (u.name?.toLowerCase().includes(q) || u.trigramme?.toLowerCase().includes(q)) &&
                    !already.includes(u.trigramme)
                )
                .forEach(u => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';
                    btn.textContent = `${u.name} (${u.trigramme})`;
                    btn.addEventListener('click', () => {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-primary d-inline-flex align-items-center me-2';
                        badge.dataset.trigramme = u.trigramme;
                        badge.innerHTML = `
                            ${u.trigramme}
                            <button type="button" class="btn-close btn-close-white btn-sm ms-1" data-tri="${u.trigramme}"></button>
                        `;
                        selectedContainer.appendChild(badge);
                        input.value = '';
                        suggestions.innerHTML = '';
                        updateHidden();
                    });
                    suggestions.appendChild(btn);
                });
        });

        wrapper.addEventListener('click', e => {
            if (e.target.tagName === 'BUTTON' && e.target.dataset.tri) {
                const tri = e.target.dataset.tri;
                const badge = selectedContainer.querySelector(`span[data-trigramme="${tri}"]`);
                if (badge) badge.remove();
                updateHidden();
            }
        });

        return wrapper;
    }
    document.getElementById('detail-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!currentRowId) {
            alert("Aucune ligne sélectionnée.");
            return;
        }

        // S'assurer que les responsables sont poussés en inputs cachés
        // (setupResponsableSearch le fait déjà, mais on rejoue au cas où)
        const respWrappers = this.querySelectorAll('[data-resp-wrapper]');
        respWrappers.forEach(w => {
            // si tu as gardé l’attribut data-resp-wrapper sur le wrapper, sinon omets ce bloc
        });

        const fd = new FormData(this); // inclut déjà @csrf et @_method('PUT') du Blade
        // Par sécurité, on ajoute quand même _method si supprimé :
        if (!fd.has('_method')) fd.append('_method', 'PUT');

        fetch(base + currentRowId, {
            method: 'POST',                    // PUT via _method
            headers: { 'X-CSRF-TOKEN': token },
            body: fd
        })
            .then(async (r) => {
                if (r.ok) return true;
                // Essaie de remonter le message d’erreur du backend
                const txt = await r.text().catch(() => '');
                throw new Error(txt || ('HTTP ' + r.status));
            })
            .then(() => {
                // ferme le panneau et rafraîchit l’affichage
                try { off.hide(); } catch { }
                location.reload();
            })
            .catch(err => {
                console.error('Save failed:', err);
                alert('Échec de l’enregistrement.\n' + (err.message || ''));
            });
    });
    // Init DataTables
    const dt = $('#actions-table').DataTable({
        paging: true,
        autoWidth: false,
        scrollX: false,
        order: [],
        searching: true,
        lengthChange: true,
        pageLength: 10,
        ordering: true,
        info: true,
        language: {
            lengthMenu: "Afficher _MENU_ actions par page",
            zeroRecords: "Aucun résultat trouvé",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ actions",
            infoEmpty: "Aucune action disponible",
            infoFiltered: "(filtré sur _MAX_ actions au total)",
            search: "🔍 Rechercher :",
            paginate: {
                first: "Premier",
                previous: "Précédent",
                next: "Suivant",
                last: "Dernier"
            }
        },
        columnDefs: [{
            targets: -1,
            orderable: false,
            searchable: false,
            className: 'dt-body-nowrap'
        }]
    });

    // ---- Rendu du panneau détail ----
    function renderDetailFields(data) {
        const container = document.getElementById('detail-fields');
        container.innerHTML = '';

        const norm = {};
        Object.keys(data || {}).forEach(k => { norm[normKey(k)] = data[k]; });

        const extraCols = (allCols || []).filter(c => {
            const nk = normKey(c);
            return !baseSet.has(nk) && !impSet.has(nk);
        });

        const detailCols = Array.from(new Set([
            ...baseCols,
            ...imp,
            ...extraCols
        ]));

        const getVal = (label) => {
            const key = normKey(label);
            if (key === 'atteinte objectif') return data['__formatted_objectif'] ?? '';
            return norm[key] ?? '';
        };

        detailCols.forEach((col) => {
            const rawValue = getVal(col);
            const colNorm = normKey(col);

            if (!canModifyAll && !allowedSet.has(colNorm)) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="mb-3">
                        <label class="form-label">${col}</label>
                        <div class="form-control-plaintext readonly-field">${rawValue ?? ''}</div>
                    </div>
                `);
                return;
            }

            let field = `<div class="mb-3"><label class="form-label">${col}</label>`;

            switch (colNorm) {
                case 'commentaire suivi':
                    field += `<textarea name="data[Commentaire suivi]" class="form-control resizable-textarea">${rawValue ?? ''}</textarea>`;
                    break;

                case 'status':
                    field += `<select name="data[Status]" class="form-select">${statusOpt.map(o => `<option${o === rawValue ? ' selected' : ''}>${o}</option>`).join('')
                        }</select>`;
                    break;

                case 'avancement': {
                    const v = parseInt(rawValue, 10) || 0;
                    field += `
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger dec-detail">-</button>
                            <div class="progress flex-fill" style="height:1rem">
                                <div class="progress-bar" role="progressbar"
                                     style="width:${v}%;background-color:${recalc(v)}"
                                     aria-valuenow="${v}"></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success inc-detail">+</button>
                            <input type="hidden" name="data[Avancement]" class="avancement-input" value="${v}">
                        </div>`;
                    break;
                }

                case 'échéance planifié':
                    field += `<input type="date" name="data[Échéance planifié]" class="form-control" value="${rawValue ?? ''}">`;
                    break;

                case 'échéance relle':
                case 'échéance réelle':
                    // adapte le nom selon ce que tu stockes réellement
                    field += `<input type="date" name="data[Échéance relle]" class="form-control" value="${rawValue ?? ''}">`;
                    break;

                case 'responsable':
                    if (canModifyAll) {
                        let arr = Array.isArray(rawValue) ? rawValue : [];
                        if (!arr.length && typeof rawValue === 'string') {
                            try { arr = JSON.parse(rawValue); }
                            catch { arr = rawValue.split(',').map(s => s.trim()).filter(Boolean); }
                        }
                        container.insertAdjacentElement('beforeend', setupResponsableSearch(arr));
                        return;
                    } else {
                        field += `<div class="form-control-plaintext readonly-field">${rawValue ?? ''}</div>`;
                    }
                    break;

                case 'priorité': {
                    const opts = (priorOpt && priorOpt.length) ? priorOpt : ['P1', 'P2', 'P3'];
                    const selected = (rawValue ?? '').toString().trim().toLowerCase();
                    const optionsHtml = opts.map(o => {
                        const ov = o.toString().trim().toLowerCase();
                        return `<option value="${o}"${ov === selected ? ' selected' : ''}>${o}</option>`;
                    }).join('');
                    field += `<select name="data[Priorité]" class="form-select">${optionsHtml}</select>`;
                    break;
                }

                case 'source':
                    field += `<select name="data[Source]" class="form-select">${(sources || []).map(o => `<option${o === rawValue ? ' selected' : ''}>${o}</option>`).join('')
                        }</select>`;
                    break;

                case 'atteinte objectif':
                    field += `<div class="form-control-plaintext readonly-field">${rawValue ?? ''}</div>`;
                    break;

                default: {
                    const type = (colNorm === 'échéance planifié') ? 'date' : 'text';
                    field += `<input type="${type}" name="data[${col}]" class="form-control" value="${rawValue ?? ''}">`;
                }
            }

            field += `</div>`;
            container.insertAdjacentHTML('beforeend', field);
        });

        attachProgressButtons(container);
    }

    // --- Ouvrir le panneau (loupe) : délégué + show explicite ---
    $('#actions-table tbody').on('click', '.detail-btn', function () {
        const tr = this.closest('tr');
        currentRowId = tr?.dataset?.id || null;

        try {
            currentRowData = JSON.parse(tr.getAttribute('data-row') || '{}');
        } catch (e) {
            currentRowData = {};
        }

        renderDetailFields(currentRowData);
        off.show();
    });

    // --- Supprimer ---
    $('#actions-table tbody').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        const rowEl = $(this).closest('tr')[0];
        const id = rowEl.dataset.id;
        if (!confirm('Supprimer cette action ?')) return;

        fetch(base + id, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ '_method': 'DELETE' })
        }).then(r => r.ok ? location.reload() : r.json().then(err => alert(err.message || 'Erreur serveur')));
    });

    // --- Ajouter une ligne (handler délégué sur la table) ---
    $('#actions-table').on('click', '#add-btn', function (e) {
        e.preventDefault();
        if (document.getElementById('new-row')) return;

        const tr = document.createElement('tr');
        tr.id = 'new-row';
        let html = '';

        imp.forEach(function (col) {
            if (col === 'Responsable') {
                html += '<td data-col="responsable"></td>';
            } else if (col === 'Atteinte objectif') {
                html += '<td><span class="form-control-plaintext text-muted">—</span></td>';
            } else if (col === 'Avancement') {
                html += '<td></td>';
            } else if (col === 'Status') {
                html += '<td><select name="data[Status]" class="form-select form-select-sm">'
                    + statusOpt.map(o => `<option>${o}</option>`).join('')
                    + '</select></td>';
            } else if (col === 'Priorité') {
                html += '<td><select name="data[Priorité]" class="form-select form-select-sm">'
                    + priorOpt.map(o => `<option>${o}</option>`).join('')
                    + '</select></td>';
            } else if (multi.indexOf(col) !== -1) {
                html += `<td><textarea name="data[${col}]" class="form-control resizable-textarea"></textarea></td>`;
            } else if (col === 'Source') {
                html += `<td><select name="data[Source]" class="form-select form-select-sm">${(sources || []).map(o => `<option>${o}</option>`).join('')
                    }</select></td>`;
            } else {
                const tp = (col === 'Échéance planifié') ? 'date' : 'text';
                html += `<td><input type="${tp}" name="data[${col}]" class="form-control resizable-input"/></td>`;
            }
        });

        html += `
            <td>
                <button type="button" id="save-new" class="btn btn-success btn-sm">💾</button>
                <button type="button" id="cancel-new" class="btn btn-secondary btn-sm">❌</button>
            </td>
        `;
        tr.innerHTML = html;
        const tdResp = tr.querySelector('td[data-col="responsable"]');
        if (tdResp) {
            const widget = setupResponsableSearch([]);
            tdResp.appendChild(widget);
        }

        // Ajout via l’API DataTables (redraw-safe)
        dt.row.add(tr).draw(false);
    });

    // --- Enregistrer la nouvelle ligne ---
    $('#actions-table tbody').on('click', '#save-new', function (e) {
        e.preventDefault();
        const row = $(this).closest('tr')[0];
        const fd = new FormData();
        row.querySelectorAll('input,select,textarea').forEach(i => fd.append(i.name, i.value));
        fetch(addUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, body: fd })
            .then(r => r.ok ? location.reload() : r.text().then(t => alert(t)));
    });

    // --- Annuler la nouvelle ligne ---
    $('#actions-table tbody').on('click', '#cancel-new', function () {
        const tr = $(this).closest('tr');
        dt.row(tr).remove().draw(false);
    });

    // colResizable
    const lastIdx = $('#actions-table th').length - 1;
    $('#actions-table').colResizable({
        liveDrag: true,
        gripInnerHtml: "<div class='grip'></div>",
        draggingClass: "dragging",
        resizeMode: 'fit',
        disabledColumns: [lastIdx],
        postbackSafe: true,
        useLocalStorage: true
    });
});
