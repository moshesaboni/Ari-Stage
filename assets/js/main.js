document.addEventListener('DOMContentLoaded', function () {
    // Initialize toast notifications
    const toastEl = document.getElementById('notificationToast');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    }

    const songModalEl = document.getElementById('songModal');
    if (!songModalEl) return;

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

    // Handle clicks on edit buttons
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const song = JSON.parse(this.dataset.song);

            resetModal(); // Start with a clean slate

            actionInput.value = 'update';
            modalTitle.textContent = 'עריכת שיר';
            songIdInput.value = song.id;

            document.getElementById('name').value = song.name;
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
        });
    });
    
    // Handle click on the link to add the first song
    const addSongBtnLink = document.getElementById('addSongBtnLink');
    if(addSongBtnLink) {
        addSongBtnLink.addEventListener('click', (e) => {
            e.preventDefault();
            resetModal();
            songModal.show();
        });
    }

    // Reset the modal form when it's opened via the main "Add Song" button
    // The main button works via data-attributes, so we just need to hook into the event
    songModalEl.addEventListener('show.bs.modal', function (event) {
        // If the trigger was NOT an edit button, reset the form for adding.
        if (event.relatedTarget && !event.relatedTarget.classList.contains('edit-btn')) {
            resetModal();
        }
    });
});