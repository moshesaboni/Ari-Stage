
document.addEventListener('DOMContentLoaded', function () {
    const lineupId = document.getElementById('lineup-id').value;
    const searchInput = document.getElementById('song-search-input');
    const searchResultsContainer = document.getElementById('search-results');
    const lineupSongList = document.getElementById('lineup-song-list');

    // 1. Search for songs
    searchInput.addEventListener('keyup', function () {
        const query = this.value;

        if (query.length < 2) {
            searchResultsContainer.innerHTML = '';
            return;
        }

        fetch('api/search_songs.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `query=${encodeURIComponent(query)}&lineup_id=${lineupId}`
        })
        .then(response => response.json())
        .then(songs => {
            searchResultsContainer.innerHTML = '';
            if (songs.length > 0) {
                const list = document.createElement('ul');
                list.className = 'list-group';
                songs.forEach(song => {
                    const listItem = document.createElement('li');
                    listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                    listItem.innerHTML = `
                        <span><strong>${song.artist}</strong> - ${song.name}</span>
                        <button class="btn btn-sm btn-primary add-song-btn" data-song-id="${song.id}">הוסף</button>
                    `;
                    list.appendChild(listItem);
                });
                searchResultsContainer.appendChild(list);
            } else {
                searchResultsContainer.innerHTML = '<p class="text-muted">לא נמצאו שירים תואמים.</p>';
            }
        })
        .catch(error => {
            console.error('Error searching for songs:', error);
            searchResultsContainer.innerHTML = '<p class="text-danger">אירעה שגיאה בחיפוש.</p>';
        });
    });

    // 2. Add a song to the lineup
    searchResultsContainer.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('add-song-btn')) {
            const songId = e.target.dataset.songId;
            
            fetch('api/add_song_to_lineup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `song_id=${songId}&lineup_id=${lineupId}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Reload the page to see the updated list. It's simple and reliable.
                    location.reload();
                } else {
                    alert('ההוספה נכשלה: ' + (result.message || 'שגיאה לא ידועה'));
                }
            })
            .catch(error => {
                console.error('Error adding song:', error);
                alert('אירעה שגיאה קריטית בעת ההוספה.');
            });
        }
    });

    // 3. Remove a song from the lineup
    lineupSongList.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-song-btn')) {
            const songId = e.target.dataset.songId;

            if (!confirm('האם אתה בטוח שברצונך להסיר את השיר הזה?')) {
                return;
            }

            fetch('api/remove_song_from_lineup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `song_id=${songId}&lineup_id=${lineupId}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Remove the song from the list in the UI
                    e.target.closest('li.list-group-item').remove();
                    
                    // If the list is now empty, show the "empty" message
                    if (lineupSongList.children.length === 0) {
                        const emptyMessage = document.createElement('li');
                        emptyMessage.id = 'empty-lineup-message';
                        emptyMessage.className = 'list-group-item text-center text-muted';
                        emptyMessage.textContent = 'אין עדיין שירים בליינאפ זה.';
                        lineupSongList.appendChild(emptyMessage);
                    }
                } else {
                    alert('ההסרה נכשלה: ' + (result.message || 'שגיאה לא ידועה'));
                }
            })
            .catch(error => {
                console.error('Error removing song:', error);
                alert('אירעה שגיאה קריטית בעת ההסרה.');
            });
        }
    });
});
