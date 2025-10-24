document.addEventListener('DOMContentLoaded', function () {
    const lineupId = new URLSearchParams(window.location.search).get('id');
    const songSearchInput = document.getElementById('song-search-input');
    const searchResultsContainer = document.getElementById('search-results');
    const lineupSongsContainer = document.getElementById('lineup-song-list');

    // Function to fetch and display songs already in the lineup
    function fetchLineupSongs() {
        if (!lineupId) return;
        fetch(`/api/lineups_api.php?lineup_id=${lineupId}`)
            .then(response => response.json())
            .then(data => {
                renderLineupSongs(data);
            })
            .catch(error => console.error('Error fetching lineup songs:', error));
    }

    // Function to render the list of songs in the lineup
    function renderLineupSongs(songs) {
        lineupSongsContainer.innerHTML = '';
        if (songs.length === 0) {
            lineupSongsContainer.innerHTML = '<p>אין עדיין שירים בליינאפ זה.</p>';
            return;
        }
        const list = document.createElement('ul');
        list.className = 'list-group';
        songs.forEach(song => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            listItem.textContent = `${song.artist || 'Unknown Artist'} - ${song.name}`;
            
            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-danger btn-sm';
            removeBtn.textContent = 'הסר';
            removeBtn.onclick = () => removeSongFromLineup(song.id); // Use song.id from the songs table
            
            listItem.appendChild(removeBtn);
            list.appendChild(listItem);
        });
        lineupSongsContainer.appendChild(list);
    }

    // Function to search for songs
    function searchSongs(query) {
        fetch(`/api/search_songs.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                renderSearchResults(data);
            })
            .catch(error => console.error('Error searching songs:', error));
    }

    // Function to render search results
    function renderSearchResults(songs) {
        searchResultsContainer.innerHTML = '';
        if (songs.length === 0) {
            searchResultsContainer.innerHTML = '<p>לא נמצאו שירים.</p>';
            return;
        }
        const list = document.createElement('ul');
        list.className = 'list-group';
        songs.forEach(song => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            listItem.textContent = `${song.artist || 'Unknown Artist'} - ${song.name}`;
            
            const addBtn = document.createElement('button');
            addBtn.className = 'btn btn-primary btn-sm';
            addBtn.textContent = 'הוסף';
            addBtn.onclick = () => addSongToLineup(song.id);

            listItem.appendChild(addBtn);
            list.appendChild(listItem);
        });
        searchResultsContainer.appendChild(list);
    }

    // Function to add a song to the lineup
    function addSongToLineup(songId) {
        fetch('/api/add_song_to_lineup.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lineup_id: lineupId, song_id: songId })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                fetchLineupSongs(); // Refresh the lineup list
                const currentQuery = searchInput.value.trim();
                searchSongs(currentQuery); // Refresh search results to remove the added song
            } else {
                // Use the specific message from the server, or a default one
                alert(body.message || 'לא ניתן היה להוסיף את השיר.');
            }
        })
        .catch(error => console.error('Error adding song:', error));
    }

    // Function to remove a song from the lineup
    function removeSongFromLineup(songId) {
        fetch('/api/remove_song_from_lineup.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lineup_id: lineupId, song_id: songId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchLineupSongs(); // Refresh the lineup list
                const currentQuery = searchInput.value.trim();
                searchSongs(currentQuery); // Refresh search results to show the removed song
            } else {
                alert('Failed to remove song: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => console.error('Error removing song:', error));
    }

    // Initial fetch of lineup songs and all songs for searching
    if (lineupId) {
        fetchLineupSongs();
        searchSongs(''); // Load all songs initially
    }

    // Event Listener for the search input
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();
        searchSongs(query);
    });
});
