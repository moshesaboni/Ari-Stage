document.addEventListener('DOMContentLoaded', function () {
    // Live search for songs page
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const tableBody = document.getElementById('songsTableBody');
    const originalNoSongsRow = document.getElementById('noSongsRow')?.cloneNode(true);

    if (searchInput && tableBody) {
        // Function to format duration from seconds to MM:SS
        const formatDuration = (seconds) => {
            if (seconds === null || seconds < 0) return '00:00';
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        };

        // Function to render table rows
        const renderTable = (songs, searchTerm) => {
            tableBody.innerHTML = ''; // Clear existing rows

            if (songs.length === 0) {
                const noResultsRow = document.createElement('tr');
                noResultsRow.innerHTML = `<td colspan="9" class="text-center text-muted py-4">לא נמצאו שירים התואמים את החיפוש "${searchTerm}".</td>`;
                if (searchTerm === '') {
                    if(originalNoSongsRow){
                         tableBody.appendChild(originalNoSongsRow);
                    } else {
                        noResultsRow.innerHTML = `<td colspan="9" class="text-center text-muted py-4">עדיין אין שירים במאגר. <a href="#" onclick="document.getElementById('addSongBtn').click()">הוסף את השיר הראשון שלך!</a></td>`;
                        tableBody.appendChild(noResultsRow);
                    }
                } else {
                    tableBody.appendChild(noResultsRow);
                }
            } else {
                songs.forEach((song, index) => {
                    const row = document.createElement('tr');
                    const tagsHtml = song.tags.split(',').map(tag => {
                        const trimmedTag = tag.trim();
                        return trimmedTag ? `<span class="badge bg-secondary bg-opacity-25 text-dark-emphasis">${escapeHTML(trimmedTag)}</span>` : '';
                    }).join(' ');

                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${escapeHTML(song.artist)}</td>
                        <td class="fw-bold">${escapeHTML(song.name)}</td>
                        <td>${escapeHTML(song.bpm)}</td>
                        <td>${escapeHTML(song.song_key)}</td>
                        <td>${formatDuration(song.duration_seconds)}</td>
                        <td>${tagsHtml}</td>
                        <td>${escapeHTML(song.notes)}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-btn" data-song='${escapeHTML(JSON.stringify(song))}' data-bs-toggle="modal" data-bs-target="#songModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="songs.php" method="POST" class="d-inline" onsubmit="return confirm('האם אתה בטוח שברצונך למחוק את השיר?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="${song.id}">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }
        };

        // Debounce function to limit API calls
        let debounceTimeout;
        const handleSearch = () => {
            const searchTerm = searchInput.value.trim();
            clearTimeout(debounceTimeout);

            clearSearchBtn.style.display = searchTerm ? 'block' : 'none';

            debounceTimeout = setTimeout(() => {
                fetch(`api/search_songs.php?search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(songs => {
                        renderTable(songs, searchTerm);
                    })
                    .catch(error => console.error('Error fetching search results:', error));
            }, 300); // 300ms delay
        };

        searchInput.addEventListener('input', handleSearch);

        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            handleSearch();
        });
    }
    
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>'"/]/g, function (s) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;'
            }[s];
        });
    }

    const songModalEl = document.getElementById('songModal');
    if (songModalEl) {
        const songModal = new bootstrap.Modal(songModalEl);
        const modalTitle = songModalEl.querySelector('.modal-title');
        const songForm = document.getElementById('songForm');
        const actionInput = document.getElementById('action');
        const songIdInput = document.getElementById('song_id');

        // Function to reset the modal to its "Add Song" state
        const resetModal = () => {
            songForm.reset();
            actionInput.value = 'create';
            songIdInput.value = '';
            modalTitle.textContent = 'הוספת שיר חדש';
        };

        // Use event delegation for edit buttons since they are dynamically loaded
        document.body.addEventListener('click', function(event) {
            const editBtn = event.target.closest('.edit-btn');
            if (editBtn) {
                const song = JSON.parse(editBtn.dataset.song);

                resetModal(); // Start with a clean slate

                actionInput.value = 'update';
                modalTitle.textContent = 'עריכת שיר';
                songIdInput.value = song.id;

                document.getElementById('name').value = song.name;
                document.getElementById('artist').value = song.artist;
                document.getElementById('bpm').value = song.bpm;

                if (song.song_key && song.song_key.trim() !== '') {
                    const keyParts = song.song_key.split(' ');
                    document.getElementById('key_note').value = keyParts[0];
                    document.getElementById('key_scale').value = keyParts[1] || 'Major';
                } else {
                    document.getElementById('key_note').value = '';
                    document.getElementById('key_scale').value = 'Major';
                }

                document.getElementById('notes').value = song.notes;
                document.getElementById('tags').value = song.tags;

                if (song.duration_seconds) {
                    const minutes = Math.floor(song.duration_seconds / 60);
                    const seconds = song.duration_seconds % 60;
                    document.getElementById('duration_minutes').value = minutes;
                    document.getElementById('duration_seconds').value = seconds;
                }

                songModal.show();
            }
            
            const addSongLink = event.target.closest('#addSongBtnLink');
            if(addSongLink){
                 event.preventDefault();
                resetModal();
                songModal.show();
            }
        });

        // Reset the modal form when it's opened via the main "Add Song" button
        songModalEl.addEventListener('show.bs.modal', function (event) {
            if (event.relatedTarget && !event.relatedTarget.classList.contains('edit-btn')) {
                resetModal();
            }
        });
    }
});